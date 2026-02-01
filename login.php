<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMD | Premium Social Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --lux-purple: #2d1b4e;
            --accent-light: #a389f4;
            --glass-bg: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.125);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }

        body {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #0a0a0c;
            overflow: hidden;
            color: white;
        }

        /* Animated Mesh Background */
        .background {
            position: absolute;
            width: 100%; height: 100%;
            background: radial-gradient(circle at 0% 0%, #1a1a2e 0%, #0a0a0c 50%),
                        radial-gradient(circle at 100% 100%, #2d1b4e 0%, #0a0a0c 50%);
            z-index: -1;
        }

        .orb {
            position: absolute;
            width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(118, 75, 162, 0.15) 0%, transparent 70%);
            filter: blur(60px);
            animation: float 20s infinite alternate;
        }
        @keyframes float {
            0% { transform: translate(-10%, -10%) rotate(0deg); }
            100% { transform: translate(20%, 10%) rotate(360deg); }
        }

        /* Auth Card Layout */
        .auth-container {
            width: 1000px;
            height: 600px;
            display: flex;
            background: rgba(255, 255, 255, 0.01);
            backdrop-filter: blur(40px);
            border: 1px solid var(--glass-border);
            border-radius: 40px;
            box-shadow: 0 50px 100px rgba(0,0,0,0.5);
            overflow: hidden;
            position: relative;
        }

        /* Left Side: Interactive Visuals */
        .visual-panel {
            flex: 1.2;
            background: linear-gradient(225deg, #1e1e26 0%, #0a0a0c 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 60px;
            position: relative;
            border-right: 1px solid var(--glass-border);
            transition: 0.8s cubic-bezier(0.77, 0, 0.175, 1);
        }

        .logo-box {
            font-size: 4rem;
            font-weight: 800;
            background: linear-gradient(to right, #fff, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 20px;
            letter-spacing: -3px;
        }

        /* Right Side: Forms */
        .form-panel {
            flex: 1;
            padding: 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            transition: 0.8s cubic-bezier(0.77, 0, 0.175, 1);
        }

        h2 { font-size: 2.2rem; font-weight: 600; margin-bottom: 10px; color: #fff; }
        p.subtitle { color: #888; font-size: 0.9rem; margin-bottom: 40px; }

        /* Elegant Inputs */
        .input-wrapper {
            position: relative;
            margin-bottom: 25px;
        }

        .input-wrapper input {
            width: 100%;
            background: transparent;
            border: none;
            border-bottom: 1px solid #333;
            padding: 12px 5px;
            color: #fff;
            font-size: 1rem;
            transition: 0.4s;
            outline: none;
        }

        .input-wrapper input:focus {
            border-bottom-color: var(--accent-light);
        }

        .input-wrapper label {
            position: absolute;
            left: 5px;
            top: 12px;
            color: #555;
            pointer-events: none;
            transition: 0.4s;
        }

        .input-wrapper input:focus + label,
        .input-wrapper input:valid + label {
            top: -10px;
            font-size: 0.75rem;
            color: var(--accent-light);
            font-weight: 600;
        }

        /* Luxury Button */
        .btn-lux {
            background: #fff;
            color: #000;
            padding: 16px;
            border-radius: 12px;
            border: none;
            font-weight: 700;
            cursor: pointer;
            transition: 0.4s;
            margin-top: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-lux:hover {
            background: var(--accent-light);
            color: #fff;
            box-shadow: 0 15px 30px rgba(163, 137, 244, 0.3);
            transform: translateY(-3px);
        }

        .switch-btn {
            background: transparent;
            color: #888;
            border: 1px solid #333;
            padding: 10px 25px;
            border-radius: 30px;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 20px;
        }

        .switch-btn:hover { border-color: #fff; color: #fff; }

        /* Animation Classes */
        .slide-up { animation: slideUp 0.8s forwards; }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .hidden { display: none; }

        .active-signup .visual-panel { transform: translateX(100%); border-right: none; border-left: 1px solid var(--glass-border); }
        .active-signup .form-panel { transform: translateX(-120%); }

    </style>
</head>
<body>

<div class="background"></div>
<div class="orb"></div>

<div class="auth-container" id="container">
    <div class="visual-panel">
        <div class="logo-box">SMD</div>
        <h3 id="visualTitle" style="font-weight: 400; color: #ccc;">The Future of Socials.</h3>
        <p id="visualDesc" style="color: #666; margin-top: 10px; font-size: 0.85rem; text-align: center;">
            Elevate your digital footprint with AI-driven scheduling and elite analytics.
        </p>
        <button class="switch-btn" id="toggleBtn">Create Account</button>
    </div>

    <div class="form-panel">
        <div id="loginSection" class="slide-up">
            <h2>Sign In</h2>
            <p class="subtitle">Access your premium dashboard.</p>
            <form action="auth.php" method="POST" style="display:flex; flex-direction:column;">
                <div class="input-wrapper">
                    <input type="email" name="email" required autocomplete="off">
                    <label>Email Address</label>
                </div>
                <div class="input-wrapper">
                    <input type="password" name="password" required autocomplete="off">
                    <label>Password</label>
                </div>
                <button type="submit" name="signin" class="btn-lux">Authorize</button>
            </form>
        </div>

        <div id="signupSection" class="hidden slide-up">
            <h2>Join SMD</h2>
            <p class="subtitle">Experience luxury management.</p>
            <form action="auth.php" method="POST" style="display:flex; flex-direction:column;">
                <div class="input-wrapper">
                    <input type="text" name="username" required autocomplete="off">
                    <label>Full Name</label>
                </div>
                <div class="input-wrapper">
                    <input type="email" name="email" required autocomplete="off">
                    <label>Email Address</label>
                </div>
                <div class="input-wrapper">
                    <input type="password" name="password" required autocomplete="off">
                    <label>Password</label>
                </div>
                <button type="submit" name="signup" class="btn-lux">Register</button>
            </form>
        </div>
    </div>
</div>

<script>
    const container = document.getElementById('container');
    const toggleBtn = document.getElementById('toggleBtn');
    const loginSection = document.getElementById('loginSection');
    const signupSection = document.getElementById('signupSection');
    const vTitle = document.getElementById('visualTitle');

    toggleBtn.addEventListener('click', () => {
        container.classList.toggle('active-signup');
        
        if(container.classList.contains('active-signup')) {
            loginSection.classList.add('hidden');
            signupSection.classList.remove('hidden');
            toggleBtn.innerText = 'Go back to Login';
            vTitle.innerText = 'Start the Journey.';
        } else {
            loginSection.classList.remove('hidden');
            signupSection.classList.add('hidden');
            toggleBtn.innerText = 'Create Account';
            vTitle.innerText = 'The Future of Socials.';
        }
    });
</script>

</body>
</html>