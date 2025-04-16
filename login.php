<?php
session_start();

// Check if user is already logged in
if(isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$conn = mysqli_connect("localhost", "uklz9ew3hrop3", "zyrbspyjlzjb", "dbugadvpf6p8e1");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$error = "";

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    
    $sql = "SELECT id, username, email, password FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);
    
    if(mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        
        if(password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['email'] = $row['email'];
            
            header("Location: index.php");
            exit();
        } else {
            $error = "Invalid password";
        }
    } else {
        $error = "User not found";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PinSpire</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f0f2f5;
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        
        .login-container {
            background-color: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            width: 400px;
            padding: 32px;
            text-align: center;
        }
        
        .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
        }
        
        .logo img {
            height: 40px;
            margin-right: 8px;
        }
        
        .logo h1 {
            color: #e60023;
            font-size: 24px;
            font-weight: 700;
        }
        
        h2 {
            font-size: 20px;
            margin-bottom: 24px;
            color: #333;
        }
        
        .form-group {
            margin-bottom: 16px;
            text-align: left;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            border-color: #e60023;
            outline: none;
        }
        
        .error-message {
            color: #e60023;
            margin-bottom: 16px;
            font-size: 14px;
        }
        
        .login-btn {
            background-color: #e60023;
            color: white;
            border: none;
            border-radius: 24px;
            padding: 12px;
            width: 100%;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-top: 8px;
        }
        
        .login-btn:hover {
            background-color: #d50c22;
        }
        
        .divider {
            display: flex;
            align-items: center;
            margin: 24px 0;
            color: #767676;
        }
        
        .divider::before,
        .divider::after {
            content: "";
            flex: 1;
            border-bottom: 1px solid #ddd;
        }
        
        .divider span {
            padding: 0 16px;
        }
        
        .social-login {
            display: flex;
            justify-content: center;
            gap: 16px;
            margin-bottom: 24px;
        }
        
        .social-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background-color: #f0f2f5;
            color: #333;
            font-size: 20px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .social-btn:hover {
            background-color: #e0e0e0;
        }
        
        .signup-link {
            margin-top: 24px;
            font-size: 14px;
            color: #767676;
        }
        
        .signup-link a {
            color: #e60023;
            text-decoration: none;
            font-weight: 600;
        }
        
        .signup-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <img src="https://i.pinimg.com/originals/1b/76/01/1b7601e035a83c13c208b4ec905ee6d9.png" alt="PinSpire Logo">
            <h1>PinSpire</h1>
        </div>
        
        <h2>Welcome back</h2>
        
        <?php if($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="login-btn">Log in</button>
        </form>
        
        <div class="divider"><span>OR</span></div>
        
        <div class="social-login">
            <div class="social-btn"><i class="fab fa-facebook-f"></i></div>
            <div class="social-btn"><i class="fab fa-google"></i></div>
            <div class="social-btn"><i class="fab fa-twitter"></i></div>
        </div>
        
        <div class="signup-link">
            Don't have an account? <a href="signup.php">Sign up</a>
        </div>
    </div>

    <script>
        // JavaScript for redirection
        document.querySelectorAll('.social-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                alert('Social login not implemented in this demo');
            });
        });
    </script>
</body>
</html>
