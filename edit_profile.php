<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success_message = "";
$error_message = "";

// Get current user data
$user_query = "SELECT * FROM users WHERE id = $user_id";
$user_result = mysqli_query($conn, $user_query);
$user = mysqli_fetch_assoc($user_result);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $bio = mysqli_real_escape_string($conn, $_POST['bio']);
    $website = mysqli_real_escape_string($conn, $_POST['website']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate username (check if it's already taken by another user)
    if ($username !== $user['username']) {
        $username_check = "SELECT id FROM users WHERE username = '$username' AND id != $user_id";
        $username_result = mysqli_query($conn, $username_check);
        if (mysqli_num_rows($username_result) > 0) {
            $error_message = "Username already taken. Please choose another one.";
        }
    }
    
    // Validate email (check if it's already taken by another user)
    if ($email !== $user['email']) {
        $email_check = "SELECT id FROM users WHERE email = '$email' AND id != $user_id";
        $email_result = mysqli_query($conn, $email_check);
        if (mysqli_num_rows($email_result) > 0) {
            $error_message = "Email already in use. Please use another email.";
        }
    }
    
    // If no errors, proceed with update
    if (empty($error_message)) {
        // Start building the update query
        $update_query = "UPDATE users SET username = '$username', email = '$email', bio = '$bio', website = '$website'";
        
        // Handle profile picture upload
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $file_type = $_FILES['profile_picture']['type'];
            
            if (in_array($file_type, $allowed_types)) {
                $file_name = time() . '_' . $_FILES['profile_picture']['name'];
                $upload_dir = 'uploads/profile_pictures/';
                
                // Create directory if it doesn't exist
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $upload_path = $upload_dir . $file_name;
                
                if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_path)) {
                    $update_query .= ", profile_picture = '$upload_path'";
                } else {
                    $error_message = "Failed to upload profile picture. Please try again.";
                }
            } else {
                $error_message = "Invalid file type. Please upload a JPEG, PNG, or GIF image.";
            }
        }
        
        // Handle password change
        if (!empty($current_password)) {
            // Verify current password
            if (password_verify($current_password, $user['password'])) {
                // Check if new password and confirmation match
                if ($new_password === $confirm_password) {
                    if (strlen($new_password) >= 6) {
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                        $update_query .= ", password = '$hashed_password'";
                    } else {
                        $error_message = "New password must be at least 6 characters long.";
                    }
                } else {
                    $error_message = "New password and confirmation do not match.";
                }
            } else {
                $error_message = "Current password is incorrect.";
            }
        }
        
        // Complete the query
        $update_query .= ", updated_at = NOW() WHERE id = $user_id";
        
        // Execute the update if no errors
        if (empty($error_message)) {
            if (mysqli_query($conn, $update_query)) {
                // Update session variables
                $_SESSION['username'] = $username;
                $_SESSION['email'] = $email;
                
                $success_message = "Profile updated successfully!";
                
                // Refresh user data
                $user_result = mysqli_query($conn, $user_query);
                $user = mysqli_fetch_assoc($user_result);
            } else {
                $error_message = "Error updating profile: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - PinSpire</title>
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
        }
        
        .navbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 24px;
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .logo {
            display: flex;
            align-items: center;
        }
        
        .logo img {
            height: 32px;
            margin-right: 8px;
        }
        
        .logo h1 {
            color: #e60023;
            font-size: 20px;
            font-weight: 700;
        }
        
        .search-bar {
            flex: 1;
            max-width: 800px;
            margin: 0 16px;
            position: relative;
        }
        
        .search-bar input {
            width: 100%;
            padding: 12px 16px;
            padding-left: 40px;
            border-radius: 24px;
            border: none;
            background-color: #efefef;
            font-size: 16px;
            outline: none;
        }
        
        .search-bar i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #767676;
        }
        
        .nav-links {
            display: flex;
            align-items: center;
        }
        
        .nav-links a {
            margin: 0 8px;
            color: #767676;
            font-size: 20px;
            text-decoration: none;
            padding: 8px;
            border-radius: 50%;
            transition: background-color 0.3s;
        }
        
        .nav-links a:hover {
            background-color: #efefef;
        }
        
        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
            cursor: pointer;
        }
        
        .container {
            max-width: 800px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .card {
            background-color: white;
            border-radius: 16px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }
        
        .card-header {
            padding: 24px;
            border-bottom: 1px solid #efefef;
        }
        
        .card-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        
        .card-subtitle {
            color: #767676;
            font-size: 16px;
        }
        
        .card-body {
            padding: 24px;
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        .form-label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 16px;
            border-radius: 8px;
            border: 1px solid #ddd;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #e60023;
        }
        
        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }
        
        .form-text {
            font-size: 14px;
            color: #767676;
            margin-top: 4px;
        }
        
        .form-divider {
            margin: 32px 0;
            border-top: 1px solid #efefef;
            position: relative;
        }
        
        .form-divider-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: white;
            padding: 0 16px;
            color: #767676;
            font-size: 14px;
        }
        
        .profile-picture-container {
            display: flex;
            align-items: center;
            margin-bottom: 24px;
        }
        
        .profile-picture {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 24px;
            border: 3px solid white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .profile-picture-upload {
            flex: 1;
        }
        
        .file-input-container {
            position: relative;
            overflow: hidden;
            display: inline-block;
        }
        
        .file-input {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            cursor: pointer;
            width: 100%;
            height: 100%;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            border-radius: 24px;
            font-weight: 600;
            cursor: pointer;
            font-size: 16px;
            text-align: center;
            transition: all 0.3s;
            border: none;
        }
        
        .btn-primary {
            background-color: #e60023;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #d50020;
        }
        
        .btn-secondary {
            background-color: #efefef;
            color: #333;
        }
        
        .btn-secondary:hover {
            background-color: #e0e0e0;
        }
        
        .btn-block {
            display: block;
            width: 100%;
        }
        
        .card-footer {
            padding: 24px;
            border-top: 1px solid #efefef;
            display: flex;
            justify-content: flex-end;
            gap: 16px;
        }
        
        .alert {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 24px;
        }
        
        .alert-success {
            background-color: #e3f9e5;
            color: #1b7724;
            border: 1px solid #c3e6c5;
        }
        
        .alert-danger {
            background-color: #fae3e5;
            color: #e60023;
            border: 1px solid #e6c3c7;
        }
        
        @media (max-width: 768px) {
            .search-bar {
                display: none;
            }
            
            .profile-picture-container {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }
            
            .profile-picture {
                margin-right: 0;
                margin-bottom: 16px;
            }
            
            .card-footer {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo">
            <img src="https://i.pinimg.com/originals/1b/76/01/1b7601e035a83c13c208b4ec905ee6d9.png" alt="PinSpire Logo">
            <h1>PinSpire</h1>
        </div>
        
        <div class="search-bar">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Search for ideas">
        </div>
        
        <div class="nav-links">
            <a href="index.php"><i class="fas fa-home"></i></a>
            <a href="#"><i class="fas fa-bell"></i></a>
            <a href="#"><i class="fas fa-comment-dots"></i></a>
            <a href="profile.php">
                <img class="user-avatar" src="<?php echo !empty($user['profile_picture']) ? $user['profile_picture'] : 'https://i.pravatar.cc/150?u=' . $user_id; ?>" alt="User Avatar">
            </a>
        </div>
    </nav>
    
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Edit Profile</h2>
                <p class="card-subtitle">Update your personal information and account settings</p>
            </div>
            
            <div class="card-body">
                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success">
                        <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger">
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>
                
                <form action="edit_profile.php" method="POST" enctype="multipart/form-data" id="edit-profile-form">
                    <div class="profile-picture-container">
                        <img class="profile-picture" src="<?php echo !empty($user['profile_picture']) ? $user['profile_picture'] : 'https://i.pravatar.cc/150?u=' . $user_id; ?>" alt="Profile Picture" id="profile-preview">
                        
                        <div class="profile-picture-upload">
                            <div class="form-group">
                                <label class="form-label">Profile Picture</label>
                                <div class="file-input-container">
                                    <button type="button" class="btn btn-secondary" id="upload-btn">Choose File</button>
                                    <input type="file" name="profile_picture" class="file-input" id="profile-picture" accept="image/*">
                                </div>
                                <p class="form-text">Recommended: Square image, at least 300x300 pixels</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" id="username" name="username" class="form-control" value="<?php echo $user['username']; ?>" required>
                        <p class="form-text">Your username is visible to other users</p>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?php echo $user['email']; ?>" required>
                        <p class="form-text">We'll never share your email with anyone else</p>
                    </div>
                    
                    <div class="form-group">
                        <label for="bio" class="form-label">Bio</label>
                        <textarea id="bio" name="bio" class="form-control"><?php echo $user['bio'] ?? ''; ?></textarea>
                        <p class="form-text">Tell others a little about yourself</p>
                    </div>
                    
                    <div class="form-group">
                        <label for="website" class="form-label">Website</label>
                        <input type="url" id="website" name="website" class="form-control" value="<?php echo $user['website'] ?? ''; ?>" placeholder="https://example.com">
                    </div>
                    
                    <div class="form-divider">
                        <span class="form-divider-text">Change Password</span>
                    </div>
                    
                    <div class="form-group">
                        <label for="current-password" class="form-label">Current Password</label>
                        <input type="password" id="current-password" name="current_password" class="form-control">
                        <p class="form-text">Leave blank if you don't want to change your password</p>
                    </div>
                    
                    <div class="form-group">
                        <label for="new-password" class="form-label">New Password</label>
                        <input type="password" id="new-password" name="new_password" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm-password" class="form-label">Confirm New Password</label>
                        <input type="password" id="confirm-password" name="confirm_password" class="form-control">
                    </div>
                </form>
            </div>
            
            <div class="card-footer">
                <button type="button" class="btn btn-secondary" onclick="location.href='profile.php'">Cancel</button>
                <button type="submit" form="edit-profile-form" class="btn btn-primary">Save Changes</button>
            </div>
        </div>
    </div>
    
    <script>
        // Preview profile picture before upload
        document.getElementById('profile-picture').addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('profile-preview').src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
        
        // Trigger file input when button is clicked
        document.getElementById('upload-btn').addEventListener('click', function() {
            document.getElementById('profile-picture').click();
        });
        
        // Form validation
        document.getElementById('edit-profile-form').addEventListener('submit', function(event) {
            const newPassword = document.getElementById('new-password').value;
            const confirmPassword = document.getElementById('confirm-password').value;
            const currentPassword = document.getElementById('current-password').value;
            
            // If trying to change password
            if (newPassword || confirmPassword || currentPassword) {
                // Check if current password is provided
                if (!currentPassword) {
                    event.preventDefault();
                    alert('Please enter your current password to change your password.');
                    return;
                }
                
                // Check if new password and confirmation match
                if (newPassword !== confirmPassword) {
                    event.preventDefault();
                    alert('New password and confirmation do not match.');
                    return;
                }
                
                // Check password length
                if (newPassword.length < 6) {
                    event.preventDefault();
                    alert('New password must be at least 6 characters long.');
                    return;
                }
            }
            
            // Validate email format
            const email = document.getElementById('email').value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                event.preventDefault();
                alert('Please enter a valid email address.');
                return;
            }
            
            // Validate username (no spaces, special characters)
            const username = document.getElementById('username').value;
            if (username.length < 3) {
                event.preventDefault();
                alert('Username must be at least 3 characters long.');
                return;
            }
            
            // Validate website URL if provided
            const website = document.getElementById('website').value;
            if (website && !website.startsWith('http://') && !website.startsWith('https://')) {
                event.preventDefault();
                alert('Website URL must start with http:// or https://');
                return;
            }
        });
        
        // Show success message and redirect after a delay
        <?php if (!empty($success_message)): ?>
        setTimeout(function() {
            window.location.href = 'profile.php';
        }, 2000);
        <?php endif; ?>
    </script>
</body>
</html>
