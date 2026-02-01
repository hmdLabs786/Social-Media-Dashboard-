<?php
session_start();
require 'db.php'; // Ensure your PDO connection file is in the same folder

// --- SIGN UP LOGIC ---
if (isset($_POST['signup'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // 1. Hash the password using BCRYPT
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    try {
        // 2. Check if email already exists
        $checkEmail = $pdo->prepare("SELECT email FROM users WHERE email = ?");
        $checkEmail->execute([$email]);
        
        if ($checkEmail->rowCount() > 0) {
            echo "<script>alert('Email already registered!'); window.location='index.php';</script>";
        } else {
            // 3. Insert into the database (role defaults to 'editor' as per your schema)
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
            if ($stmt->execute([$username, $email, $hashed_password])) {
                echo "<script>alert('Account created successfully! Please log in.'); window.location='index.php';</script>";
            }
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

// --- SIGN IN LOGIC ---
if (isset($_POST['signin'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    try {
        // 1. Fetch user by email
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // 2. Verify password
        if ($user && password_verify($password, $user['password_hash'])) {
            // 3. Set Session Variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // 4. Redirect to Dashboard
            header("Location: dashboard.php");
            exit();
        } else {
            echo "<script>alert('Invalid email or password!'); window.location='login.php';</script>";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>