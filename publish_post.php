<?php
session_start();
// Hide errors from the end user
error_reporting(0); 
ini_set('display_errors', 0);

require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['image'])) {
    $user_id = $_SESSION['user_id'];
    $caption = $_POST['caption'];
    $platforms = $_POST['platforms'] ?? [];
    
    $upload_dir = 'uploads/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
    
    $file_name = time() . '_' . basename($_FILES['image']['name']);
    $target_file = $upload_dir . $file_name;
    
    // Using your ngrok link for public accessibility
    $image_url = "https://amee-epididymal-roslyn.ngrok-free.dev/SMD/" . $target_file; 

    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
        $success_count = 0;

        foreach ($platforms as $platform) {
            $stmt = $pdo->prepare("SELECT platform_id, access_token FROM linked_socials WHERE user_id = ? AND platform = ?");
            $stmt->execute([$user_id, $platform]);
            $acc = $stmt->fetch();

            if ($acc) {
                if ($platform == 'facebook') {
                    $fb_url = "https://graph.facebook.com/v24.0/{$acc['platform_id']}/photos";
                    $data = ['url' => $image_url, 'caption' => $caption, 'access_token' => $acc['access_token']];
                    
                    sleep(2); // Buffer for Meta crawler
                    $res = post_to_api($fb_url, $data);
                    if (isset($res['id'])) $success_count++;

                } elseif ($platform == 'instagram') {
                    $ig_container_url = "https://graph.facebook.com/v24.0/{$acc['platform_id']}/media";
                    $container_res = post_to_api($ig_container_url, [
                        'image_url' => $image_url, 
                        'caption' => $caption, 
                        'access_token' => $acc['access_token']
                    ]);
                    
                    if (isset($container_res['id'])) {
                        $creation_id = $container_res['id'];
                        $ig_publish_url = "https://graph.facebook.com/v24.0/{$acc['platform_id']}/media_publish";

                        // Retry logic for IG processing
                        for ($i = 1; $i <= 3; $i++) {
                            sleep(7); 
                            $pub_res = post_to_api($ig_publish_url, [
                                'creation_id' => $creation_id,
                                'access_token' => $acc['access_token']
                            ]);
                            if (isset($pub_res['id'])) {
                                $success_count++;
                                break;
                            }
                        }
                    }
                }
            }
        }
        
        // Redirect back to dashboard with a status message
        header("Location: dashboard.php?status=success&count=$success_count");
        exit();
    } else {
        header("Location: create_post.php?status=error&msg=upload_failed");
        exit();
    }
}

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