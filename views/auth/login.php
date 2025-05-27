<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Network Monitor - Login</title>
    <link rel="stylesheet" href="assets/css/auth.css">
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
                <input type="text" id="username" name="username" class="form-input" 
                       placeholder="Enter your username" required autocomplete="username"
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-input" 
                       placeholder="Enter your password" required autocomplete="current-password">
            </div>

            <button type="submit" class="login-btn">Sign In</button>
        </form>
    </div>
</body>
</html>
