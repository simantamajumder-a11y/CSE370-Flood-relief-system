<?php
require_once '../config.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = clean_input($_POST['username']);
    $password = clean_input($_POST['password']);
    
    // Check credentials
    $query = "SELECT * FROM admins WHERE username='$username' AND password=MD5('$password')";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) == 1) {
        $admin = mysqli_fetch_assoc($result);
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = $admin['admin_id'];
        $_SESSION['admin_name'] = $admin['admin_name'];
        $_SESSION['admin_username'] = $admin['username'];
        
        // Update last login
        mysqli_query($conn, "UPDATE admins SET last_login=NOW() WHERE admin_id='{$admin['admin_id']}'");
        
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid username or password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Flood Relief System</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            background: white;
            padding: 3rem;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 450px;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .login-header h1 {
            color: #667eea;
            margin-bottom: 0.5rem;
            font-size: 2rem;
        }
        
        .login-header p {
            color: #666;
            font-size: 0.95rem;
        }
        
        .login-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1.5rem;
            border: 1px solid #f5c6cb;
        }
        
        .back-home {
            text-align: center;
            margin-top: 1.5rem;
        }
        
        .back-home a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }
        
        .back-home a:hover {
            color: #764ba2;
        }
        
        .info-box {
            margin-top: 2rem;
            padding: 1.5rem;
            border-top: 1px solid #e0e0e0;
            background: #f8f9fa;
            border-radius: 5px;
        }
        
        .info-box h4 {
            color: #667eea;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }
        
        .info-box p {
            margin: 0;
            font-size: 0.85rem;
            color: #666;
        }
        
        .credentials {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }
        
        .credentials div {
            background: white;
            padding: 0.5rem;
            border-radius: 3px;
            border: 1px solid #e0e0e0;
        }
        
        .credentials strong {
            color: #667eea;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="login-icon">🔐</div>
            <h1>Admin Login</h1>
            <p>Flood Relief Management System</p>
        </div>
        
        <?php if ($error): ?>
            <div class="error-message">
                <strong>⚠️ Error:</strong> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autofocus>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn" style="width: 100%; padding: 1rem; font-size: 1.1rem;">
                🔑 Login
            </button>
        </form>
        
        <div class="back-home">
            <a href="../index.php">← Back to Homepage</a>
        </div>
        
        <div class="info-box">
            <h4>🔐 Admin Access Required</h4>
            <p>This area is restricted to authorized administrators only. Please login with your admin credentials to continue.</p>
            
            <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e0e0e0;">
                <h4>📝 Default Test Credentials:</h4>
                <div class="credentials">
                    <div><strong>Username:</strong> admin</div>
                    <div><strong>Password:</strong> admin123</div>
                </div>
                <p style="margin-top: 0.5rem; font-size: 0.8rem; color: #999;">⚠️ Change these credentials in production environment</p>
            </div>
        </div>
    </div>
</body>
</html>