<?php
session_start();
require_once 'db.php';

if(!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$pin_id = mysqli_real_escape_string($conn, $_GET['id']);

// Get pin details
$sql = "SELECT pins.*, users.username FROM pins 
        JOIN users ON pins.user_id = users.id 
        WHERE pins.id = $pin_id";
$result = mysqli_query($conn, $sql);

if(mysqli_num_rows($result) == 0) {
    header("Location: index.php");
    exit();
}

$pin = mysqli_fetch_assoc($result);

// Update view count
$update_views = "UPDATE pins SET views = views + 1 WHERE id = $pin_id";
mysqli_query($conn, $update_views);

// Get related pins
$category = $pin['category'];
$related_sql = "SELECT pins.*, users.username FROM pins 
                JOIN users ON pins.user_id = users.id 
                WHERE pins.category = '$category' AND pins.id != $pin_id 
                ORDER BY RAND() LIMIT 10";
$related_result = mysqli_query($conn, $related_sql);

// Check if user has saved this pin
$is_saved = false;
if(isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $check_saved_sql = "SELECT * FROM saved_pins WHERE user_id = $user_id AND pin_id = $pin_id";
    $check_saved_result = mysqli_query($conn, $check_saved_sql);
    $is_saved = mysqli_num_rows($check_saved_result) > 0;
    
    // Get user's boards
    $boards_sql = "SELECT * FROM boards WHERE user_id = $user_id";
    $boards_result = mysqli_query($conn, $boards_sql);
}

// Handle save pin action
if(isset($_POST['save_pin']) && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $board_id = isset($_POST['board_id']) ? mysqli_real_escape_string($conn, $_POST['board_id']) : null;
    
    // Check if pin is already saved
    $check_sql = "SELECT * FROM saved_pins WHERE user_id = $user_id AND pin_id = $pin_id";
    $check_result = mysqli_query($conn, $check_sql);
    
    if(mysqli_num_rows($check_result) == 0) {
        // Save pin
        if($board_id) {
            $save_sql = "INSERT INTO saved_pins (user_id, pin_id, board_id, created_at) VALUES ($user_id, $pin_id, $board_id, NOW())";
        } else {
            $save_sql = "INSERT INTO saved_pins (user_id, pin_id, created_at) VALUES ($user_id, $pin_id, NOW())";
        }
        
        mysqli_query($conn, $save_sql);
        $is_saved = true;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pin['title']; ?> - PinSpire</title>
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
        
        .pin-container {
            max-width: 1000px;
            margin: 40px auto;
            background-color: white;
            border-radius: 32px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            display: flex;
            flex-direction: row;
        }
        
        @media (max-width: 768px) {
            .pin-container {
                flex-direction: column;
                margin: 20px;
            }
        }
        
        .pin-image-container {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f0f2f5;
            padding: 24px;
        }
        
        .pin-image {
            max-width: 100%;
            max-height: 600px;
            border-radius: 16px;
        }
        
        .pin-details {
            flex: 1;
            padding: 32px;
            display: flex;
            flex-direction: column;
        }
        
        .pin-actions {
            display: flex;
            justify-content: space-between;
            margin-bottom: 24px;
        }
        
        .pin-action-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #f0f2f5;
            color: #333;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
            border: none;
        }
        
        .pin-action-btn:hover {
            background-color: #e0e0e0;
        }
        
        .save-btn {
            background-color: #e60023;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 24px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .save-btn:hover {
            background-color: #d50c22;
        }
        
        .saved-btn {
            background-color: #111;
            color: white;
        }
        
        .pin-title {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 16px;
        }
        
        .pin-description {
            color: #333;
            margin-bottom: 24px;
            line-height: 1.5;
        }
        
        .pin-meta {
            display: flex;
            align-items: center;
            margin-bottom: 24px;
        }
        
        .pin-category {
            background-color: #f0f2f5;
            padding: 6px 12px;
            border-radius: 16px;
            font-size: 14px;
            font-weight: 500;
            margin-right: 12px;
        }
        
        .pin-date {
            color: #767676;
            font-size: 14px;
        }
        
        .pin-user {
            display: flex;
            align-items: center;
            margin-top: auto;
            padding-top: 24px;
            border-top: 1px solid #efefef;
        }
        
        .pin-user-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 16px;
        }
        
        .pin-user-info {
            flex: 1;
        }
        
        .pin-user-name {
            font-weight: 600;
            margin-bottom: 4px;
        }
        
        .pin-user-follow {
            color: #767676;
            font-size: 14px;
        }
        
        .follow-btn {
            background-color: #efefef;
            color: #333;
            border: none;
            padding: 8px 16px;
            border-radius: 24px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .follow-btn:hover {
            background-color: #e0e0e0;
        }
        
        .related-pins {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .related-pins-title {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 24px;
            text-align: center;
        }
        
        .related-pins-container {
            column-count: 5;
            column-gap: 16px;
        }
        
        @media (max-width: 1200px) {
            .related-pins-container {
                column-count: 4;
            }
        }
        
        @media (max-width: 992px) {
            .related-pins-container {
                column-count: 3;
            }
        }
        
        @media (max-width: 768px) {
            .related-pins-container {
                column-count: 2;
            }
        }
        
        @media (max-width: 576px) {
            .related-pins-container {
                column-count: 1;
            }
        }
        
        .related-pin {
            break-inside: avoid;
            margin-bottom: 16px;
            border-radius: 16px;
            overflow: hidden;
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s;
            cursor: pointer;
            position: relative;
        }
        
        .related-pin:hover {
            transform: translateY(-4px);
        }
        
        .related-pin-image {
            width: 100%;
            display: block;
        }
        
        .related-pin-info {
            padding: 12px;
        }
        
        .related-pin-title {
            font-weight: 600;
            margin-bottom: 4px;
            font-size: 14px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .related-pin-user {
            display: flex;
            align-items: center;
            font-size: 12px;
            color: #767676;
        }
        
        .related-pin-user img {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            margin-right: 8px;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            justify-content: center;
            align-items: center;
        }
        
        .modal-content {
            background-color: white;
            border-radius: 16px;
            width: 400px;
            max-width: 90%;
            padding: 24px;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
        
        .modal-title {
            font-size: 20px;
            font-weight: 600;
        }
        
        .modal-close {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: #767676;
        }
        
        .board-list {
            max-height: 300px;
            overflow-y: auto;
        }
        
        .board-item {
            display: flex;
            align-items: center;
            padding: 12px;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .board-item:hover {
            background-color: #f0f2f5;
        }
        
        .board-item-preview {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            background-color: #f0f2f5;
            margin-right: 16px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .board-item-preview i {
            font-size: 24px;
            color: #767676;
        }
        
        .board-item-info {
            flex: 1;
        }
        
        .board-item-name {
            font-weight: 600;
            margin-bottom: 4px;
        }
        
        .board-item-count {
            font-size: 12px;
            color: #767676;
        }
        
        .create-board-btn {
            display: flex;
            align-items: center;
            padding: 12px;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-top: 16px;
            border-top: 1px solid #efefef;
            padding-top: 16px;
        }
        
        .create-board-btn:hover {
            background-color: #f0f2f5;
        }
        
        .create-board-btn i {
            font-size: 24px;
            color: #767676;
            margin-right: 16px;
        }
        
        .create-board-text {
            font-weight: 600;
        }
        
        .auth-buttons {
            display: flex;
            gap: 10px;
        }
        
        .auth-btn {
            padding: 8px 16px;
            border-radius: 24px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }
        
        .login-btn {
            background-color: #efefef;
            color: #333;
        }
        
        .signup-btn {
            background-color: #e60023;
            color: white;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo" onclick="location.href='index.php'" style="cursor: pointer;">
            <img src="https://i.pinimg.com/originals/1b/76/01/1b7601e035a83c13c208b4ec905ee6d9.png" alt="PinSpire Logo">
            <h1>PinSpire</h1>
        </div>
        
        <div class="  alt="PinSpire Logo">
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
            
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="profile.php">
                    <img class="user-avatar" src="https://i.pravatar.cc/150?u=<?php echo $_SESSION['user_id']; ?>" alt="User Avatar">
                </a>
            <?php else: ?>
                <div class="auth-buttons">
                    <a href="login.php" class="auth-btn login-btn">Log in</a>
                    <a href="signup.php" class="auth-btn signup-btn">Sign up</a>
                </div>
            <?php endif; ?>
        </div>
    </nav>
    
    <div class="pin-container">
        <div class="pin-image-container">
            <img src="<?php echo $pin['image_url']; ?>" alt="<?php echo $pin['title']; ?>" class="pin-image">
        </div>
        
        <div class="pin-details">
            <div class="pin-actions">
                <div>
                    <button class="pin-action-btn"><i class="fas fa-ellipsis-h"></i></button>
                    <button class="pin-action-btn"><i class="fas fa-share"></i></button>
                </div>
                
                <?php if(isset($_SESSION['user_id'])): ?>
                    <?php if($is_saved): ?>
                        <button class="save-btn saved-btn" disabled>Saved</button>
                    <?php else: ?>
                        <button class="save-btn" onclick="openBoardModal()">Save</button>
                    <?php endif; ?>
                <?php else: ?>
                    <button class="save-btn" onclick="location.href='login.php'">Save</button>
                <?php endif; ?>
            </div>
            
            <h1 class="pin-title"><?php echo $pin['title']; ?></h1>
            
            <?php if($pin['description']): ?>
                <p class="pin-description"><?php echo $pin['description']; ?></p>
            <?php endif; ?>
            
            <div class="pin-meta">
                <div class="pin-category"><?php echo $pin['category']; ?></div>
                <div class="pin-date"><?php echo date('M d, Y', strtotime($pin['created_at'])); ?></div>
            </div>
            
            <div class="pin-user">
                <img src="https://i.pravatar.cc/150?u=<?php echo $pin['user_id']; ?>" alt="User" class="pin-user-avatar">
                <div class="pin-user-info">
                    <div class="pin-user-name"><?php echo $pin['username']; ?></div>
                    <div class="pin-user-follow">1.2k followers</div>
                </div>
                
                <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] != $pin['user_id']): ?>
                    <button class="follow-btn">Follow</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php if(mysqli_num_rows($related_result) > 0): ?>
    <div class="related-pins">
        <h2 class="related-pins-title">More like this</h2>
        
        <div class="related-pins-container">
            <?php while($related_pin = mysqli_fetch_assoc($related_result)): ?>
                <div class="related-pin" onclick="location.href='view_pin.php?id=<?php echo $related_pin['id']; ?>'">
                    <img src="<?php echo $related_pin['image_url']; ?>" alt="<?php echo $related_pin['title']; ?>" class="related-pin-image">
                    <div class="related-pin-info">
                        <div class="related-pin-title"><?php echo $related_pin['title']; ?></div>
                        <div class="related-pin-user">
                            <img src="https://i.pravatar.cc/150?u=<?php echo $related_pin['user_id']; ?>" alt="User">
                            <span><?php echo $related_pin['username']; ?></span>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if(isset($_SESSION['user_id']) && !$is_saved): ?>
    <div class="modal" id="boardModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Save to board</h3>
                <button class="modal-close" onclick="closeBoardModal()"><i class="fas fa-times"></i></button>
            </div>
            
            <div class="board-list">
                <?php 
                if(isset($boards_result) && mysqli_num_rows($boards_result) > 0):
                    while($board = mysqli_fetch_assoc($boards_result)):
                ?>
                    <form method="POST" action="view_pin.php?id=<?php echo $pin_id; ?>">
                        <input type="hidden" name="board_id" value="<?php echo $board['id']; ?>">
                        <input type="hidden" name="save_pin" value="1">
                        <div class="board-item" onclick="this.closest('form').submit();">
                            <div class="board-item-preview">
                                <i class="fas fa-thumbtack"></i>
                            </div>
                            <div class="board-item-info">
                                <div class="board-item-name"><?php echo $board['name']; ?></div>
                                <div class="board-item-count">
                                    <?php
                                    $board_id = $board['id'];
                                    $count_sql = "SELECT COUNT(*) as count FROM saved_pins WHERE board_id = $board_id";
                                    $count_result = mysqli_query($conn, $count_sql);
                                    $count = mysqli_fetch_assoc($count_result)['count'];
                                    echo $count . ' pins';
                                    ?>
                                </div>
                            </div>
                        </div>
                    </form>
                <?php 
                    endwhile;
                else:
                ?>
                    <p style="text-align: center; padding: 20px;">You don't have any boards yet.</p>
                <?php endif; ?>
            </div>
            
            <div class="create-board-btn" onclick="location.href='create_board.php'">
                <i class="fas fa-plus"></i>
                <div class="create-board-text">Create board</div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <script>
        function openBoardModal() {
            document.getElementById('boardModal').style.display = 'flex';
        }
        
        function closeBoardModal() {
            document.getElementById('boardModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('boardModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>
