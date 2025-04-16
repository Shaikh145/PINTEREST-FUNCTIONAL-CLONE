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

if(!isset($_GET['id'])) {
    header("Location: profile.php");
    exit();
}

$board_id = mysqli_real_escape_string($conn, $_GET['id']);
$user_id = $_SESSION['user_id'];

// Check if board belongs to user
$check_sql = "SELECT * FROM boards WHERE id = $board_id AND user_id = $user_id";
$check_result = mysqli_query($conn, $check_sql);

if(mysqli_num_rows($check_result) == 0) {
    header("Location: profile.php");
    exit();
}

$board = mysqli_fetch_assoc($check_result);

$error = "";
$success = "";

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    
    // Check if board name already exists for this user (excluding current board)
    $check_name_sql = "SELECT * FROM boards WHERE user_id = $user_id AND name = '$name' AND id != $board_id";
    $check_name_result = mysqli_query($conn, $check_name_sql);
    
    if(mysqli_num_rows($check_name_result) > 0) {
        $error = "You already have a board with this name";
    } else {
        // Update board
        $update_sql = "UPDATE boards SET name = '$name', description = '$description' WHERE id = $board_id";
        
        if(mysqli_query($conn, $update_sql)) {
            $success = "Board updated successfully!";
            $board['name'] = $name;
            $board['description'] = $description;
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
    <title>Edit Board - PinSpire</title>
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
        
        .edit-board-container {
            background-color: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            overflow: hidden;
        }
        
        .edit-board-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 24px;
            border-bottom: 1px solid #efefef;
        }
        
        .edit-board-title {
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
        
        .edit-board-content {
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
        
        .update-board-btn {
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
        
        .update-board-btn:hover {
            background-color: #d50c22;
        }
        
        .delete-board-btn {
            background-color: white;
            color: #e60023;
            border: 2px solid #e60023;
            border-radius: 24px;
            padding: 12px;
            width: 100%;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-top: 16px;
        }
        
        .delete-board-btn:hover {
            background-color: #fff0f0;
        }
    </style>
</head>
<body>
    <div class="edit-board-container">
        <div class="edit-board-header">
            <h2 class="edit-board-title">Edit Board</h2>
            <button class="close-btn" onclick="location.href='view_board.php?id=<?php echo $board_id; ?>'"><i class="fas fa-times"></i></button>
        </div>
        
        <div class="edit-board-content">
            <?php if($error): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="success-message"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form action="edit_board.php?id=<?php echo $board_id; ?>" method="POST">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" value="<?php echo $board['name']; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description (optional)</label>
                    <textarea id="description" name="description"><?php echo $board['description']; ?></textarea>
                </div>
                
                <button type="submit" class="update-board-btn">Update Board</button>
                <button type="button" class="delete-board-btn" onclick="deleteBoard()">Delete Board</button>
            </form>
        </div>
    </div>

    <script>
        function deleteBoard() {
            if(confirm('Are you sure you want to delete this board? This action cannot be undone.')) {
                window.location.href = 'delete_board.php?id=<?php echo $board_id; ?>';
            }
        }
        
        // Redirect to board page after successful update
        <?php if($success): ?>
        setTimeout(function() {
            window.location.href = 'view_board.php?id=<?php echo $board_id; ?>';
        }, 2000);
        <?php endif; ?>
    </script>
</body>
</html>
