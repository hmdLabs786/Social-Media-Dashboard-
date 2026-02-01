<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

// 1. Get count of pending posts
$stmt_pending = $pdo->prepare("SELECT COUNT(*) FROM scheduled_posts WHERE user_id = ? AND status = 'pending'");
$stmt_pending->execute([$user_id]);
$pending_count = $stmt_pending->fetchColumn();

// 2. Get count of published posts
$stmt_pub = $pdo->prepare("SELECT COUNT(*) FROM scheduled_posts WHERE user_id = ? AND status = 'published'");
$stmt_pub->execute([$user_id]);
$published_count = $stmt_pub->fetchColumn();

// 3. Analytics Summary Logic (Best Performing Post)
$best_post_stmt = $pdo->prepare("
    SELECT p.caption, (ps.likes + ps.comments) as total_engagement 
    FROM scheduled_posts p
    JOIN post_stats ps ON p.id = ps.post_id
    WHERE p.user_id = ?
    ORDER BY total_engagement DESC
    LIMIT 1
");
$best_post_stmt->execute([$user_id]);
$best_post = $best_post_stmt->fetch();

// 4. Real Total Reach & Engagement (Sum of your stats)
$totals_stmt = $pdo->prepare("SELECT SUM(likes) as t_likes, SUM(comments) as t_comments FROM post_stats ps JOIN scheduled_posts p ON ps.post_id = p.id WHERE p.user_id = ?");
$totals_stmt->execute([$user_id]);
$totals = $totals_stmt->fetch();
$real_eng = ($totals['t_likes'] ?? 0) + ($totals['t_comments'] ?? 0);

// 5. Chart Data
$chart_stmt = $pdo->prepare("
    SELECT DATE_FORMAT(fetched_at, '%D %b') as date_label, 
           SUM(likes) as total_likes 
    FROM post_stats ps
    JOIN scheduled_posts p ON ps.post_id = p.id
    WHERE p.user_id = ?
    GROUP BY DATE(fetched_at) 
    ORDER BY fetched_at ASC 
    LIMIT 7
");
$chart_stmt->execute([$user_id]);
$chart_data = $chart_stmt->fetchAll();

$labels = [];
$likes_data = [];
foreach ($chart_data as $row) {
    $labels[] = $row['date_label'];
    $likes_data[] = $row['total_likes'];
}
// Find this in your dashboard.php
if ($_SESSION['role'] === 'admin') {
    // Admin sees everything from everyone
    $stmt = $pdo->prepare("SELECT * FROM scheduled_posts ORDER BY scheduled_at DESC");
    $stmt->execute();
} else {
    // Regular users only see their own posts
    $stmt = $pdo->prepare("SELECT * FROM scheduled_posts WHERE user_id = ? ORDER BY scheduled_at DESC");
    $stmt->execute([$user_id]);
}
$all_posts = $stmt->fetchAll();
// Fetch the post log
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMD | Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            font-size: 2rem; font-weight: 800;
            background: linear-gradient(to right, #fff, var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 50px; padding-left: 15px;
        }

        .nav-item {
            display: flex; align-items: center; padding: 15px;
            color: var(--text-dim); text-decoration: none;
            border-radius: 12px; margin-bottom: 10px; transition: 0.3s;
        }

        .nav-item i { margin-right: 15px; font-size: 1.2rem; }
        .nav-item:hover, .nav-item.active { background: var(--card-bg); color: var(--text-main); }
        .nav-item.active { border-left: 4px solid var(--accent); }

        .main-content { margin-left: 260px; flex: 1; padding: 40px; }

        header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }

        .user-profile {
            display: flex; align-items: center; background: var(--card-bg);
            padding: 8px 20px; border-radius: 30px; border: 1px solid var(--glass-border);
        }

        .logout-btn { color: #ff4d4d; text-decoration: none; font-size: 0.9rem; margin-left: 20px; font-weight: 600; }

        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 25px; margin-bottom: 40px; }
        .stat-card { background: var(--card-bg); border: 1px solid var(--glass-border); padding: 30px; border-radius: 24px; transition: 0.3s; }
        .stat-card .value { font-size: 2rem; font-weight: 700; }

        .action-banner {
            background: linear-gradient(135deg, #1e1e26 0%, #2d1b4e 100%);
            padding: 40px; border-radius: 30px; border: 1px solid var(--glass-border);
            display: flex; justify-content: space-between; align-items: center;
        }

        .btn-create {
            background: #fff; color: #000; padding: 15px 30px;
            border-radius: 12px; text-decoration: none; font-weight: 700; transition: 0.3s;
        }

        .summary-box {
            background: rgba(163, 137, 244, 0.1);
            border-left: 4px solid var(--accent);
            padding: 20px; border-radius: 12px; margin-bottom: 20px;
        }

        @media print {
            .sidebar, .btn-create, .action-banner, .logout-btn { display: none !important; }
            .main-content { margin-left: 0 !important; }
            body { background: white; color: black; }
            .stat-card, .card { border: 1px solid #ddd !important; color: black; }
            .post-table { color: black !important; }
        }

        /* Toast & Progress styles kept from original */
        .toast-notification { position: fixed; top: 25px; right: 30px; border-radius: 12px; background: #1a1a1c; padding: 20px; border-left: 6px solid #a389f4; transform: translateX(calc(100% + 30px)); transition: 0.5s; z-index: 9999; }
        .toast-notification.active { transform: translateX(0%); }
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
        <header>
            <div>
                <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
                <p style="color: var(--text-dim);">Performance report for your connected socials.</p>
            </div>
            <div class="user-profile">
                <span><?php echo $_SESSION['role']; ?></span>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </header>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Reach</h3>
                <div class="value"><?php echo number_format($published_count * 342); ?></div> <p style="color: #00ff88; font-size: 0.8rem;">Live estimate</p>
            </div>
            <div class="stat-card">
                <h3>Scheduled</h3>
                <div class="value"><?php echo $pending_count; ?></div>
                <p style="color: var(--text-dim); font-size: 0.8rem;">Pending posts</p>
            </div>
            <div class="stat-card">
                <h3>Engagements</h3>
                <div class="value"><?php echo number_format($real_eng); ?></div>
                <p style="color: #00ff88; font-size: 0.8rem;">Likes + Comments</p>
            </div>
        </div>

        <?php if ($best_post): ?>
        <div class="summary-box">
            <h4 style="color: var(--accent); margin-bottom: 5px;"><i class="fa-solid fa-trophy"></i> Weekly Highlight</h4>
            <p>Your best performing post was: <strong>"<?php echo htmlspecialchars($best_post['caption']); ?>"</strong> with <?php echo $best_post['total_engagement']; ?> interactions!</p>
        </div>
        <?php endif; ?>

        <div class="card" style="background: var(--card-bg); padding: 25px; border-radius: 24px; margin-bottom: 40px; border: 1px solid var(--glass-border);">
            <h3>Engagement Growth</h3>
            <canvas id="analyticsChart" style="max-height: 300px;"></canvas>
        </div>

        <div class="action-banner">
            <div>
                <h2>Export Performance</h2>
                <p style="color: #aaa;">Generate a clean report for your social media growth.</p>
            </div>
            <div>
                <button onclick="window.print()" class="btn-create" style="background: transparent; color: #fff; border: 1px solid #fff; margin-right: 10px;">
                    <i class="fa-solid fa-file-pdf"></i> Download PDF
                </button>
                <a href="create_post.php" class="btn-create">Create New Post</a>
            </div>
        </div>

        <br><br>

        <div class="post-log card" style="background: var(--card-bg); padding: 25px; border-radius: 24px; border: 1px solid var(--glass-border);">
            <h2>Post History</h2>
            <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                <thead style="text-align: left; color: var(--text-dim); border-bottom: 1px solid var(--glass-border);">
                    <tr>
                        <th style="padding: 10px;">Media</th>
                        <th>Caption</th>
                        <th>Platform</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($all_posts as $post): ?>
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                        <td style="padding: 15px 10px;"><img src="<?php echo $post['image_url']; ?>" width="45" style="border-radius: 8px;"></td>
                        <td><?php echo htmlspecialchars(substr($post['caption'], 0, 40)) . '...'; ?></td>
                        <td><?php echo strtoupper($post['platforms']); ?></td>
                        <td><span style="font-size: 0.8rem; padding: 4px 10px; border-radius: 10px; background: <?php echo $post['status'] == 'published' ? '#28a745' : '#ffc107'; ?>; color: #fff;"><?php echo ucfirst($post['status']); ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('analyticsChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($labels); ?>,
                datasets: [{
                    label: 'Likes',
                    data: <?php echo json_encode($likes_data); ?>,
                    borderColor: '#a389f4',
                    backgroundColor: 'rgba(163, 137, 244, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' } },
                    x: { grid: { display: false } }
                },
                plugins: { legend: { display: false } }
            }
        });

        // Cron Auto-Ping
        setInterval(() => {
            fetch('cron_worker.php').then(r => r.text()).then(d => console.log('Cron check done.'));
        }, 60000);
    </script>
</body>
</html>