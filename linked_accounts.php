<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require 'db.php';

// Fetch already linked accounts for this user
// Note: Ensure you have a 'social_accounts' table with 'user_id' and 'platform' columns
$linked = [];
try {
    $stmt = $pdo->prepare("SELECT platform FROM linked_socials WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $linked = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    // If table doesn't exist yet, $linked remains an empty array
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMD | Linked Accounts</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0a0a0c;
            --card-bg: rgba(255, 255, 255, 0.03);
            --accent: #a389f4;
            --text-main: #ffffff;
            --text-dim: #888888;
            --glass-border: rgba(255, 255, 255, 0.1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }

        body {
            background-color: var(--bg);
            color: var(--text-main);
            display: flex;
            min-height: 100vh;
        }

        /* --- SIDEBAR (Matched to Dashboard) --- */
        .sidebar {
            width: 260px;
            background: rgba(255, 255, 255, 0.01);
            backdrop-filter: blur(20px);
            border-right: 1px solid var(--glass-border);
            display: flex;
            flex-direction: column;
            padding: 30px 20px;
            position: fixed;
            height: 100vh;
        }

        .logo {
            font-size: 2rem;
            font-weight: 800;
            background: linear-gradient(to right, #fff, var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 50px;
            padding-left: 15px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            padding: 15px;
            color: var(--text-dim);
            text-decoration: none;
            border-radius: 12px;
            margin-bottom: 10px;
            transition: 0.3s;
        }

        .nav-item i { margin-right: 15px; font-size: 1.2rem; width: 20px; text-align: center; }

        .nav-item:hover, .nav-item.active {
            background: var(--card-bg);
            color: var(--text-main);
            box-shadow: inset 0 0 10px rgba(255,255,255,0.05);
        }

        .nav-item.active { border-left: 4px solid var(--accent); }

        /* --- MAIN CONTENT --- */
        .main-content {
            margin-left: 260px;
            flex: 1;
            padding: 60px;
        }

        h1 { font-size: 2.5rem; margin-bottom: 10px; }
        .subtitle { color: var(--text-dim); margin-bottom: 40px; }

        /* --- PLATFORM GRID --- */
        .platform-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
        }

        .platform-card {
            background: var(--card-bg);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 40px 20px;
            text-align: center;
            transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .platform-card:hover {
            transform: translateY(-10px);
            border-color: var(--accent);
            background: rgba(255, 255, 255, 0.05);
        }

        .platform-card i { font-size: 3.5rem; margin-bottom: 20px; }
        
        /* Brand Colors */
        .fa-facebook { color: #1877f2; }
        .fa-instagram { color: #e4405f; }
        .fa-x-twitter { color: #fff; }

        .status-tag {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-dim);
            display: block;
            margin-bottom: 20px;
        }

        .btn-connect {
            display: inline-block;
            padding: 12px 25px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.9rem;
            background: #fff;
            color: #000;
            transition: 0.3s;
            width: 100%;
        }

        .btn-connect.linked {
            background: rgba(0, 255, 136, 0.1);
            color: #00ff88;
            border: 1px solid #00ff88;
            cursor: default;
        }

        .btn-connect:not(.linked):hover {
            background: var(--accent);
            color: #fff;
            box-shadow: 0 10px 20px rgba(163, 137, 244, 0.3);
        }

    </style>
</head>
<body>

    <div class="sidebar">
        <div class="logo">SMD</div>
        <a href="dashboard.php" class="nav-item active"><i class="fa-solid fa-house"></i> Overview</a>
<a href="create_post.php" class="nav-item"><i class="fa-solid fa-calendar-plus"></i> Schedule Post</a>

<?php if ($_SESSION['role'] === 'admin'): ?>
    <a href="admin_users.php" class="nav-item"><i class="fa-solid fa-users-gear"></i> Manage Users</a>
<?php endif; ?>

<a href="linked_accounts.php" class="nav-item"><i class="fa-solid fa-link"></i> Linked Accounts</a>
    </div>

    <div class="main-content">
        <h1>Linked Accounts</h1>
        <p class="subtitle">Connect your social profiles to start automating your workflow.</p>

        <div class="platform-grid">
            <div class="platform-card">
                <i class="fa-brands fa-facebook"></i>
                <h2>Facebook</h2>
                <span class="status-tag"><?php echo in_array('facebook', $linked) ? 'Active Connection' : 'Disconnected'; ?></span>
                <?php if(in_array('facebook', $linked)): ?>
                    <a href="#" class="btn-connect linked">Connected</a>
                <?php else: ?>
                    <a href="connect_fb.php" class="btn-connect">Connect Page</a>
                <?php endif; ?>
            </div>

            <div class="platform-card">
                <i class="fa-brands fa-instagram"></i>
                <h2>Instagram</h2>
                <span class="status-tag"><?php echo in_array('instagram', $linked) ? 'Active Connection' : 'Disconnected'; ?></span>
                <?php if(in_array('instagram', $linked)): ?>
                    <a href="#" class="btn-connect linked">Connected</a>
                <?php else: ?>
                    <a href="connect_ig.php" class="btn-connect">Connect Business</a>
                <?php endif; ?>
            </div>

            <div class="platform-card">
                <i class="fa-brands fa-x-twitter"></i>
                <h2>Twitter / X</h2>
                <span class="status-tag"><?php echo in_array('twitter', $linked) ? 'Active Connection' : 'Disconnected'; ?></span>
                <?php if(in_array('twitter', $linked)): ?>
                    <a href="#" class="btn-connect linked">Connected</a>
                <?php else: ?>
                    <a href="connect_tw.php" class="btn-connect">Connect Account</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

</body>
</html>