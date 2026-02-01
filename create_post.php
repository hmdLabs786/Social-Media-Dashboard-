<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require 'db.php';

// Check which platforms are connected
$stmt = $pdo->prepare("SELECT platform FROM linked_socials WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$linked = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMD | Create Post</title>
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

        /* --- SIDEBAR --- */
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
        }

        .nav-item.active { border-left: 4px solid var(--accent); }

        /* --- MAIN CONTENT --- */
        .main-content {
            margin-left: 260px;
            flex: 1;
            padding: 60px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .post-container {
            width: 100%;
            max-width: 700px;
        }

        h1 { font-size: 2.2rem; margin-bottom: 30px; }

        .editor-card {
            background: var(--card-bg);
            border: 1px solid var(--glass-border);
            border-radius: 28px;
            padding: 35px;
            backdrop-filter: blur(10px);
        }

        /* --- CUSTOM FILE UPLOAD --- */
        .file-upload-wrapper {
            position: relative;
            width: 100%;
            height: 180px;
            border: 2px dashed var(--glass-border);
            border-radius: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin-bottom: 25px;
            transition: 0.3s;
            cursor: pointer;
            background: rgba(255, 255, 255, 0.01);
        }

        .file-upload-wrapper:hover {
            border-color: var(--accent);
            background: rgba(163, 137, 244, 0.05);
        }

        .file-upload-wrapper i {
            font-size: 2.5rem;
            color: var(--accent);
            margin-bottom: 10px;
        }

        .file-upload-wrapper p { color: var(--text-dim); font-size: 0.9rem; }

        #image-input {
            position: absolute;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        /* --- TEXTAREA --- */
        textarea {
            width: 100%;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--glass-border);
            border-radius: 18px;
            padding: 20px;
            color: white;
            font-size: 1.05rem;
            resize: none;
            margin-bottom: 25px;
            outline: none;
            transition: 0.3s;
        }

        textarea:focus { border-color: var(--accent); background: rgba(255, 255, 255, 0.08); }

        /* --- PLATFORM CHIPS --- */
        .section-label {
            font-size: 0.85rem;
            color: var(--text-dim);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
            display: block;
        }

        .platform-select { display: flex; gap: 15px; margin-bottom: 35px; }

        ..platform-checkbox {
    position: absolute;
    opacity: 0;
    width: 20px; /* Give it some width */
    height: 20px;
    cursor: pointer;
    z-index: 2; /* Put it on top */
}

.platform-label {
    padding: 12px 24px;
    border-radius: 14px;
    border: 1px solid var(--glass-border);
    cursor: pointer;
    transition: 0.3s;
    display: flex;
    align-items: center;
    gap: 10px;
    background: rgba(255, 255, 255, 0.02);
    color: var(--text-dim);
    font-weight: 600;
    position: relative;
    z-index: 1; /* Put label behind the input area */
}

.platform-checkbox:checked + .platform-label {
    border-color: var(--accent) !important;
    background: rgba(163, 137, 244, 0.15) !important;
    color: white !important;
    transform: scale(1.05);
}

        /* --- SUBMIT BUTTON --- */
        .btn-publish {
            background: #ffffff;
            color: #000;
            padding: 18px;
            border-radius: 16px;
            border: none;
            font-weight: 800;
            font-size: 1rem;
            cursor: pointer;
            transition: 0.3s;
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }

        .btn-publish:hover {
            background: var(--accent);
            color: #fff;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(163, 137, 244, 0.3);
        }
        
        #file-name {
            margin-top: 8px;
            color: var(--accent);
            font-weight: 600;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="logo">SMD</div>
        <a href="dashboard.php" class="nav-item"><i class="fa-solid fa-house"></i> Overview</a>
        <a href="create_post.php" class="nav-item active"><i class="fa-solid fa-calendar-plus"></i> Schedule Post</a>
        <a href="#" class="nav-item"><i class="fa-solid fa-chart-line"></i> Analytics</a>
        <a href="linked_accounts.php" class="nav-item"><i class="fa-solid fa-link"></i> Linked Accounts</a>
        <a href="#" class="nav-item"><i class="fa-solid fa-gear"></i> Settings</a>
    </div>

    <div class="main-content">
        <div class="post-container">
            <h1>Create Post</h1>
            
            <div class="editor-card">
                <form action="schedule_handler.php" method="POST" enctype="multipart/form-data">
                    
                    <span class="section-label">Media Attachment</span>
                    <div class="file-upload-wrapper" id="upload-box">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        <p>Drag & drop or click to upload image</p>
                        <span id="file-name"></span>
                        <input type="file" name="image" id="image-input" accept="image/*" required>
                    </div>

                    <span class="section-label">Caption</span>
                    <textarea name="caption" rows="5" placeholder="Write something engaging..."></textarea>
                    
                    <span class="section-label">Post to</span>
                    <div class="platform-select">
    <?php if(in_array('facebook', $linked)): ?>
        <input type="checkbox" name="platforms[]" value="facebook" id="cb-fb" class="platform-checkbox" checked>
        <label for="cb-fb" class="platform-label">
            <i class="fa-brands fa-facebook" style="color:#1877f2"></i> Facebook
        </label>
    <?php endif; ?>

    <?php if(in_array('instagram', $linked)): ?>
        <input type="checkbox" name="platforms[]" value="instagram" id="cb-ig" class="platform-checkbox" checked>
        <label for="cb-ig" class="platform-label">
            <i class="fa-brands fa-instagram" style="color:#e4405f"></i> Instagram
        </label>
    <?php endif; ?>
</div>
<span class="section-label">Schedule Date & Time</span>
<input type="datetime-local" name="scheduled_at" id="schedule-input" class="date-input" required>

<style>
    .date-input {
        width: 100%;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid var(--glass-border);
        border-radius: 14px;
        padding: 15px;
        color: white;
        font-size: 1rem;
        margin-bottom: 25px;
        outline: none;
    }
    /* Style for the calendar icon in Chrome/Edge */
    ::-webkit-calendar-picker-indicator {
        filter: invert(1);
        cursor: pointer;
    }
</style>
                    <button type="submit" class="btn-publish">
                        Publish Content <i class="fa-solid fa-paper-plane"></i>
                    </button>
                    
                </form>
            </div>
        </div>
    </div>

    <script>
        // Script to show the file name when a user selects a file
        const fileInput = document.getElementById('image-input');
        const fileNameDisplay = document.getElementById('file-name');
        const uploadBox = document.getElementById('upload-box');

        fileInput.addEventListener('change', function() {
            if (this.files && this.files.length > 0) {
                fileNameDisplay.textContent = "Selected: " + this.files[0].name;
                uploadBox.style.borderColor = "var(--accent)";
                uploadBox.style.background = "rgba(163, 137, 244, 0.05)";
            }
        });
    </script>

</body>
</html>