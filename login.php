<?php
// login.php - Complete login page
$timeout_message = isset($_GET['timeout']) ? 'Your session has expired. Please log in again.' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Network Monitor - Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
 
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a1a1a 0%, #2c2c2c 100%);
            color: #ffffff;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
 
        .login-container {
            background: #2a2a2a;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 400px;
            border: 1px solid #333;
        }
 
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
 
        .logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #00d4aa, #00b894);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 30px;
            font-weight: bold;
            color: #1a1a1a;
        }
 
        .login-title {
            font-size: 1.8em;
            font-weight: bold;
            margin-bottom: 10px;
            color: #00d4aa;
        }
 
        .login-subtitle {
            color: #cccccc;
            font-size: 0.9em;
        }
 
        .form-group {
            margin-bottom: 20px;
        }
 
        .form-label {
            display: block;
            margin-bottom: 8px;
            color: #cccccc;
            font-weight: 500;
        }
 
        .form-input {
            width: 100%;
            padding: 12px 15px;
            background: #1a1a1a;
            border: 2px solid #333;
            border-radius: 8px;
            color: #ffffff;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
 
        .form-input:focus {
            outline: none;
            border-color: #00d4aa;
            box-shadow: 0 0 0 3px rgba(0, 212, 170, 0.1);
        }
 
        .form-input::placeholder {
            color: #888;
        }
 
        .login-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #00d4aa, #00b894);
            border: none;
            border-radius: 8px;
            color: #1a1a1a;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
 
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 212, 170, 0.3);
        }
 
        .error-message {
            background: #e74c3c;
            color: white;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
        }
 
        .timeout-message {
            background: #f39c12;
            color: white;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
        }
 
        .default-accounts {
            margin-top: 30px;
            padding: 20px;
            background: #1a1a1a;
            border-radius: 8px;
            border-left: 4px solid #3498db;
        }
 
        .default-accounts h4 {
            color: #3498db;
            margin-bottom: 10px;
            font-size: 14px;
        }
 
        .account-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 13px;
            color: #cccccc;
        }
 
        .account-username {
            font-weight: bold;
            color: #00d4aa;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="logo">NET</div>
            <h1 class="login-title">Network Monitor</h1>
            <p class="login-subtitle">Secure Access Portal</p>
        </div>
 
        <?php if ($timeout_message): ?>
            <div class="timeout-message">
                <?= htmlspecialchars($timeout_message) ?>
            </div>
        <?php endif; ?>
 
        <?php if (isset($login_error)): ?>
            <div class="error-message">
                <?= htmlspecialchars($login_error) ?>
            </div>
        <?php endif; ?>
 
        <form method="POST" action="">
            <div class="form-group">
                <label for="username" class="form-label">Username</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    class="form-input"
                    placeholder="Enter your username"
                    required
                    autocomplete="username"
                    value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                >
            </div>
 
            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-input"
                    placeholder="Enter your password"
                    required
                    autocomplete="current-password"
                >
            </div>
 
            <button type="submit" class="login-btn">
                Sign In
            </button>
        </form>
 
        
    </div>
</body>
</html>