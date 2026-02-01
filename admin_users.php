<?php
session_start();
require 'db.php';

// SECURITY: If not logged in OR not an admin, kick them out
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

// Handle Role Update
if (isset($_POST['update_role'])) {
    // Changed 'id' to 'user_id'
    $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE user_id = ?");
    $stmt->execute([$_POST['role'], $_POST['user_id']]);
    $msg = "User updated successfully!";
}

// Handle Delete
if (isset($_GET['delete'])) {
    // Changed 'id' to 'user_id'
    $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: admin_users.php");
}

// 🛑 THE ERROR WAS HERE: Changed 'id' to 'user_id'
$stmt = $pdo->query("SELECT user_id, username, email, role, created_at FROM users");
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin | User Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --bg: #0a0a0c; --card-bg: rgba(255, 255, 255, 0.03); --accent: #a389f4; --text-main: #fff; --glass-border: rgba(255, 255, 255, 0.1); }
        body { background: var(--bg); color: var(--text-main); font-family: 'Plus Jakarta Sans', sans-serif; padding: 40px; }
        .container { max-width: 1000px; margin: auto; }
        .user-table { width: 100%; border-collapse: collapse; background: var(--card-bg); border-radius: 15px; overflow: hidden; border: 1px solid var(--glass-border); }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid var(--glass-border); }
        th { background: rgba(163, 137, 244, 0.1); color: var(--accent); }
        .role-badge { padding: 4px 10px; border-radius: 8px; font-size: 0.8rem; text-transform: uppercase; font-weight: bold; }
        .admin { background: #a389f4; color: white; }
        .user { background: #444; color: white; }
        select { background: #1a1a1c; color: white; border: 1px solid var(--glass-border); padding: 5px; border-radius: 5px; }
        .btn-del { color: #ff4d4d; text-decoration: none; margin-left: 10px; font-size: 0.9rem; }
        .back-link { color: var(--text-main); text-decoration: none; display: inline-block; margin-bottom: 20px; }
    </style>
</head>
<body>

<div class="container">
    <a href="dashboard.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
    <h1>User Management</h1>
    <p style="color: #888; margin-bottom: 30px;">Control permissions and roles for all team members.</p>

    <?php if (isset($msg)) echo "<p style='color: #00ff88; margin-bottom: 15px;'>$msg</p>"; ?>

    <table class="user-table">
        <thead>
            <tr>
                <th>Username</th>
                <th>Email</th>
                <th>Current Role</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
<?php foreach ($users as $user): ?>
<tr>
    <td><?php echo htmlspecialchars($user['username']); ?></td>
    <td><?php echo htmlspecialchars($user['email']); ?></td>
    <td>
        <span class="role-badge <?php echo $user['role']; ?>">
            <?php echo $user['role']; ?>
        </span>
    </td>
    <td>
        <form method="POST" style="display:inline-block;">
            <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
            <select name="role">
                <option value="user" <?php if($user['role']=='user') echo 'selected'; ?>>User</option>
                <option value="admin" <?php if($user['role']=='admin') echo 'selected'; ?>>Admin</option>
            </select>
            <button type="submit" name="update_role" style="background:none; border:none; color:var(--accent); cursor:pointer; font-weight:bold;">Update</button>
        </form>
        
        <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
            <a href="?delete=<?php echo $user['user_id']; ?>" class="btn-del" onclick="return confirm('Delete this user?')">Delete</a>
        <?php endif; ?>
    </td>
</tr>
<?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>