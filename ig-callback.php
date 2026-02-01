<?php
session_start();
require 'db.php';

$app_id = "YOUR_APP_ID_HERE";
$app_secret = "YOUR_SECRET_KEY_HERE";
$redirect_url = "https://amee-epididymal-roslyn.ngrok-free.dev/SMD/ig-callback.php";

if (isset($_GET['code'])) {
    $code = $_GET['code'];

    // 1. Exchange code for an Access Token
    $token_url = "https://graph.facebook.com/v24.0/oauth/access_token?client_id=$app_id&redirect_uri=" . urlencode($redirect_url) . "&client_secret=$app_secret&code=$code";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $token_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $params = json_decode($response, true);
    $user_token = $params['access_token'];

    // 2. Ask for the Instagram ID linked to the user's Facebook Pages
    $ig_url = "https://graph.facebook.com/v24.0/me/accounts?fields=instagram_business_account,name&access_token=" . $user_token;
    
    $ig_response = file_get_contents($ig_url);
    $ig_data = json_decode($ig_response, true);

    $instagram_id = null;

    // Loop through pages to find the one with an IG account attached
    if (!empty($ig_data['data'])) {
        foreach ($ig_data['data'] as $page) {
            if (isset($page['instagram_business_account'])) {
                $instagram_id = $page['instagram_business_account']['id'];
                break; 
            }
        }
    }

    if ($instagram_id) {
        $platform = 'instagram';
        
        // 3. Save to Database (Cleaning up old ones first)
        $delete = $pdo->prepare("DELETE FROM linked_socials WHERE user_id = ? AND platform = ?");
        $delete->execute([$_SESSION['user_id'], $platform]);

        $stmt = $pdo->prepare("INSERT INTO linked_socials (user_id, platform, platform_id, access_token) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $platform, $instagram_id, $user_token]);

        header("Location: linked_accounts.php?success=ig");
        exit();
    } else {
        echo "<h2>Error</h2>";
        echo "No Instagram Business account found. Ensure your IG is converted to a Business account and linked to a Facebook Page.";
    }
} else {
    echo "Connection failed.";
}