<?php
require 'db.php';

function get_meta_stats($platform_post_id, $access_token, $platform) {
    if ($platform == 'facebook') {
        // FB: Ask for Likes and Comments count
        $url = "https://graph.facebook.com/v24.0/{$platform_post_id}?fields=reactions.summary(total_count),comments.summary(total_count)&access_token={$access_token}";
    } else {
        // IG: Ask for Media Insights
        $url = "https://graph.facebook.com/v24.0/{$platform_post_id}?fields=like_count,comments_count&access_token={$access_token}";
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = json_decode(curl_exec($ch), true);
    curl_close($ch);

    return $response;
}

// Get all published posts
$stmt = $pdo->query("SELECT p.*, s.access_token FROM scheduled_posts p 
                     JOIN linked_socials s ON p.user_id = s.user_id 
                     WHERE p.status = 'published' AND p.platform_post_id IS NOT NULL");
$posts = $stmt->fetchAll();

foreach ($posts as $post) {
    $data = get_meta_stats($post['platform_post_id'], $post['access_token'], $post['platforms']);
    
    // Parse response based on platform
    $likes = 0;
    $comments = 0;

    if ($post['platforms'] == 'facebook') {
        $likes = $data['reactions']['summary']['total_count'] ?? 0;
        $comments = $data['comments']['summary']['total_count'] ?? 0;
    } else {
        $likes = $data['like_count'] ?? 0;
        $comments = $data['comments_count'] ?? 0;
    }

    // Save to post_stats table
    $ins = $pdo->prepare("INSERT INTO post_stats (post_id, likes, comments) VALUES (?, ?, ?)");
    $ins->execute([$post['id'], $likes, $comments]);
    
    echo "Updated stats for Post #{$post['id']}: $likes Likes, $comments Comments<br>";
}