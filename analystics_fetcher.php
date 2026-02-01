<?php
require 'db.php';

function get_fb_stats($post_id, $access_token) {
    // We ask FB for "insights" (impressions and engagement)
    $url = "https://graph.facebook.com/v24.0/{$post_id}/insights?metric=post_impressions_unique,post_engagements&access_token={$access_token}";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $res = json_decode(curl_exec($ch), true);
    
    $stats = ['reach' => 0, 'eng' => 0];
    if (isset($res['data'])) {
        foreach ($res['data'] as $metric) {
            if ($metric['name'] == 'post_impressions_unique') $stats['reach'] = $metric['values'][0]['value'];
            if ($metric['name'] == 'post_engagements') $stats['eng'] = $metric['values'][0]['value'];
        }
    }
    return $stats;
}

// 1. Get all published posts that have a platform ID
$stmt = $pdo->query("SELECT p.*, s.access_token FROM scheduled_posts p 
                     JOIN linked_socials s ON p.user_id = s.user_id AND s.platform = 'facebook'
                     WHERE p.status = 'published' AND p.platform_post_id IS NOT NULL");
$published_posts = $stmt->fetchAll();

foreach ($published_posts as $post) {
    // Fetch stats (Example for Facebook)
    $stats = get_fb_stats($post['platform_post_id'], $post['access_token']);
    
    // Here you would normally save these to an 'analytics' table
    // For now, let's just echo them to see if it's working
    echo "Post: {$post['caption']} | Reach: {$stats['reach']} | Engagement: {$stats['eng']}<br>";
}