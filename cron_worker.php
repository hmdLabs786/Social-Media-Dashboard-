<?php
// Set your local timezone
date_default_timezone_set('Asia/Karachi'); 

require 'db.php';

$now = date('Y-m-d H:i:s');
echo "Server current time: " . $now . "<br>";

// 1. Find posts that are due
$stmt = $pdo->prepare("SELECT * FROM scheduled_posts WHERE status = 'pending' AND scheduled_at <= ?");
$stmt->execute([$now]);
$posts_to_publish = $stmt->fetchAll();

if (empty($posts_to_publish)) {
    echo "No posts due yet. Checking next available post in DB...<br>";
    $check = $pdo->query("SELECT scheduled_at FROM scheduled_posts WHERE status = 'pending' ORDER BY scheduled_at ASC LIMIT 1")->fetch();
    if ($check) {
        echo "The next post is scheduled for: " . $check['scheduled_at'] . "<br>";
    } else {
        echo "No pending posts found.";
    }
    exit();
}

// Function to handle API calls
function post_to_api($url, $data) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

foreach ($posts_to_publish as $post) {
    $platforms = explode(',', $post['platforms']);
    $success_on_all = true;
    $saved_post_id = null; // To store the ID returned by Meta

    foreach ($platforms as $platform) {
        $acc_stmt = $pdo->prepare("SELECT platform_id, access_token FROM linked_socials WHERE user_id = ? AND platform = ?");
        $acc_stmt->execute([$post['user_id'], $platform]);
        $acc = $acc_stmt->fetch();

        if ($acc) {
            if ($platform == 'facebook') {
                $fb_url = "https://graph.facebook.com/v24.0/{$acc['platform_id']}/photos";
                $res = post_to_api($fb_url, [
                    'url' => $post['image_url'],
                    'caption' => $post['caption'],
                    'access_token' => $acc['access_token']
                ]);
                
                if (isset($res['id'])) {
                    $saved_post_id = $res['id']; // Store FB Post ID
                } else {
                    $success_on_all = false;
                }

            } elseif ($platform == 'instagram') {
                $ig_url = "https://graph.facebook.com/v24.0/{$acc['platform_id']}/media";
                $container = post_to_api($ig_url, [
                    'image_url' => $post['image_url'],
                    'caption' => $post['caption'],
                    'access_token' => $acc['access_token']
                ]);

                if (isset($container['id'])) {
                    sleep(10); // Wait for Instagram processing
                    $pub_res = post_to_api("https://graph.facebook.com/v24.0/{$acc['platform_id']}/media_publish", [
                        'creation_id' => $container['id'],
                        'access_token' => $acc['access_token']
                    ]);
                    
                    if (isset($pub_res['id'])) {
                        // For Instagram, we store the media ID
                        $saved_post_id = $pub_res['id']; 
                    } else {
                        $success_on_all = false;
                    }
                } else {
                    $success_on_all = false;
                }
            }
        }
    }

    // Update status and store the Platform Post ID for Analytics
    $new_status = $success_on_all ? 'published' : 'failed';
    $update_stmt = $pdo->prepare("UPDATE scheduled_posts SET status = ?, platform_post_id = ? WHERE id = ?");
    $update_stmt->execute([$new_status, $saved_post_id, $post['id']]);
}

echo "Cron run completed. Check your dashboard for updates.";