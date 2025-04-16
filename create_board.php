<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$conn = mysqli_connect("localhost", "uklz9ew3hrop3", "zyrbspyjlzjb", "dbugadvpf6p8e1");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$error = "";
$success = "";

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $user_id = $_SESSION['user_id'];
    
    // Check if board name already exists for this user
    $check_sql = "SELECT * FROM boards WHERE user_id = $user_id AND name = '$name'";
    $check_result = mysqli_query($conn, $check_sql);
    
    if(mysqli_num_rows($check_result) > 0) {
        $error = "You already have a board with this name";
    } else {
        // Insert board into database
        $sql = "INSERT INTO boards (user_id, name, description, created_at) VALUES ($user_id, '$name', '$description', NOW())";
        
        if(mysqli_query($conn, $sql)) {
            $success = "Board created successfully!";
        } else {
            $error = "Error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Board - PinSpire</title>
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
            padding: 20px;
        }
        
        .create-board-container {
            background-color: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            overflow: hidden;
        }
        
        .create-board-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 24px;
            border-bottom: 1px solid #efefef;
        }
        
        .create-board-title {
            font-size: 20px;
            font-weight: 600;
        }
        
        .close-btn {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: #767676;
        }
        
        .create-board-content {
            padding: 24px;
        }
        
        .form-group {
            margin-bottom: 16px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            border-color: #e60023;
            outline: none;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .error-message {
            color: #e60023;
            margin-bottom: 16px;
            font-size: 14px;
        }
        
        .success-message {
            color: #28a745;
            margin-bottom: 16px;
            font-size: 14px;
        }
        
        .create-board-btn {
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
        }
        
        .create-board-btn:hover {
            background-color: #d50c22;
        }
    </style>
</head>
<body>
    <div class="create-board-container">
        <div class="create-board-header">
            <h2 class="create-board-title">Create Board</h2>
            <button class="close-btn" onclick="location.href='profile.php'"><i class="fas fa-times"></i></button>
        </div>
        
        <div class="create-board-content">
            <?php if($error): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="success-message"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form action="create_board.php" method="POST">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" placeholder="Like 'Places to Go' or 'Recipes to Make'" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description (optional)</label>
                    <textarea id="description" name="description" placeholder="What's your board about?"></textarea>
                </div>
                
                <button type="submit" class="create-board-btn">Create</button>
            </form>
        </div>
    </div>

    <script>
        // Redirect to profile page after successful board creation
        <?php if($success): ?>
        setTimeout(function() {
            window.location.href = 'profile.php';
        }, 2000);
        <?php endif; ?>
    </script>
</body>
</html>
