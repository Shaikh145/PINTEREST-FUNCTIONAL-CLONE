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

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Get user's pins
$pins_sql = "SELECT * FROM pins WHERE user_id = $user_id ORDER BY created_at DESC";
$pins_result = mysqli_query($conn, $pins_sql);

// Get user's boards
$boards_sql = "SELECT * FROM boards WHERE user_id = $user_id ORDER BY created_at DESC";
$boards_result = mysqli_query($conn, $boards_sql);

// Get user's saved pins
$saved_pins_sql = "SELECT pins.*, boards.name as board_name FROM saved_pins 
                  JOIN pins ON saved_pins.pin_id = pins.id 
                  LEFT JOIN boards ON saved_pins.board_id = boards.id 
                  WHERE saved_pins.user_id = $user_id 
                  ORDER BY saved_pins.created_at DESC";
$saved_pins_result = mysqli_query($conn, $saved_pins_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $username; ?>'s Profile - PinSpire</title>
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
        
        .profile-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 40px 24px;
            background-color: white;
            margin-bottom: 24px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }
        
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 16px;
            border: 4px solid white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .profile-username {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        
        .profile-email {
            color: #767676;
            margin-bottom: 16px;
        }
        
        .profile-stats {
            display: flex;
            gap: 24px;
            margin-bottom: 24px;
        }
        
        .stat {
            text-align: center;
        }
        
        .stat-number {
            font-size: 20px;
            font-weight: 700;
        }
        
        .stat-label {
            color: #767676;
            font-size: 14px;
        }
        
        .profile-actions {
            display: flex;
            gap: 16px;
        }
        
        .profile-btn {
            padding: 8px 16px;
            border-radius: 24px;
            font-weight: 600;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
        }
        
        .profile-btn i {
            margin-right: 8px;
        }
        
        .edit-btn {
            background-color: #efefef;
            color: #333;
            border: none;
        }
        
        .share-btn {
            background-color: #efefef;
            color: #333;
            border: none;
        }
        
        .logout-btn {
            background-color: #e60023;
            color: white;
            border: none;
        }
        
        .tabs {
            display: flex;
            justify-content: center;
            margin-bottom: 24px;
            background-color: white;
            padding: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }
        
        .tab {
            padding: 12px 24px;
            font-weight: 600;
            cursor: pointer;
            position: relative;
            color: #767676;
        }
        
        .tab.active {
            color: #e60023;
        }
        
        .tab.active::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background-color: #e60023;
        }
        
        .tab-content {
            display: none;
            padding: 0 24px;
            max-width: 1600px;
            margin: 0 auto;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .pin-container {
            column-count: 5;
            column-gap: 16px;
        }
        
        @media (max-width: 1200px) {
            .pin-container {
                column-count: 4;
            }
        }
        
        @media (max-width: 992px) {
            .pin-container {
                column-count: 3;
            }
        }
        
        @media (max-width: 768px) {
            .pin-container {
                column-count: 2;
            }
            .search-bar {
                display: none;
            }
        }
        
        @media (max-width: 576px) {
            .pin-container {
                column-count: 1;
            }
        }
        
        .pin {
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
        
        .pin:hover {
            transform: translateY(-4px);
        }
        
        .pin:hover .pin-overlay {
            opacity: 1;
        }
        
        .pin-image {
            width: 100%;
            display: block;
        }
        
        .pin-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(transparent 70%, rgba(0, 0, 0, 0.3));
            opacity: 0;
            transition: opacity 0.3s;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 12px;
        }
        
        .pin-actions {
            display: flex;
            justify-content: flex-end;
        }
        
        .pin-save {
            background-color: #e60023;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 24px;
            font-weight: 600;
            cursor: pointer;
        }
        
        .pin-info {
            padding: 12px;
        }
        
        .pin-title {
            font-weight: 600;
            margin-bottom: 4px;
            font-size: 14px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .pin-user {
            display: flex;
            align-items: center;
            font-size: 12px;
            color: #767676;
        }
        
        .pin-user img {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            margin-right: 8px;
        }
        
        .board-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 16px;
        }
        
        .board {
            background-color: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s;
            cursor: pointer;
        }
        
        .board:hover {
            transform: translateY(-4px);
        }
        
        .board-preview {
            height: 150px;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            grid-template-rows: repeat(2, 1fr);
            gap: 2px;
        }
        
        .board-preview-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .board-info {
            padding: 12px;
        }
        
        .board-title {
            font-weight: 600;
            margin-bottom: 4px;
            font-size: 16px;
        }
        
        .board-count {
            font-size: 12px;
            color: #767676;
        }
        
        .create-board {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background-color: #f0f2f5;
            border-radius: 16px;
            height: 200px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .create-board:hover {
            background-color: #e0e0e0;
        }
        
        .create-board i {
            font-size: 32px;
            color: #767676;
            margin-bottom: 8px;
        }
        
        .create-board-text {
            font-weight: 600;
            color: #333;
        }
        
        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px;
            text-align: center;
        }
        
        .empty-state i {
            font-size: 48px;
            color: #767676;
            margin-bottom: 16px;
        }
        
        .empty-state-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .empty-state-text {
            color: #767676;
            margin-bottom: 16px;
        }
        
        .empty-state-btn {
            background-color: #e60023;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 24px;
            font-weight: 600;
            cursor: pointer;
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
                <img class="user-avatar" src="https://i.pravatar.cc/150?u=<?php echo $user_id; ?>" alt="User Avatar">
            </a>
        </div>
    </nav>
    
    <div class="profile-header">
        <img class="profile-avatar" src="https://i.pravatar.cc/150?u=<?php echo $user_id; ?>" alt="<?php echo $username; ?>">
        <h2 class="profile-username"><?php echo $username; ?></h2>
        <p class="profile-email"><?php echo $_SESSION['email']; ?></p>
        
        <div class="profile-stats">
            <div class="stat">
                <div class="stat-number"><?php echo mysqli_num_rows($pins_result); ?></div>
                <div class="stat-label">Pins</div>
            </div>
            <div class="stat">
                <div class="stat-number"><?php echo mysqli_num_rows($boards_result); ?></div>
                <div class="stat-label">Boards</div>
            </div>
            <div class="stat">
                <div class="stat-number"><?php echo mysqli_num_rows($saved_pins_result); ?></div>
                <div class="stat-label">Saved</div>
            </div>
        </div>
        
        <div class="profile-actions">
            <button class="profile-btn edit-btn"><i class="fas fa-pencil-alt"></i> Edit Profile</button>
            <button class="profile-btn share-btn"><i class="fas fa-share"></i> Share</button>
            <button class="profile-btn logout-btn" onclick="logout()"><i class="fas fa-sign-out-alt"></i> Logout</button>
        </div>
    </div>
    
    <div class="tabs">
        <div class="tab active" data-tab="created">Created</div>
        <div class="tab" data-tab="saved">Saved</div>
        <div class="tab" data-tab="boards">Boards</div>
    </div>
    
    <div class="tab-content active" id="created">
        <?php if(mysqli_num_rows($pins_result) > 0): ?>
            <div class="pin-container">
                <?php while($pin = mysqli_fetch_assoc($pins_result)): ?>
                    <div class="pin" onclick="viewPin(<?php echo $pin['id']; ?>)">
                        <img class="pin-image" src="<?php echo $pin['image_url']; ?>" alt="<?php echo $pin['title']; ?>">
                        <div class="pin-overlay">
                            <div class="pin-actions">
                                <button class="pin-save" onclick="event.stopPropagation(); editPin(<?php echo $pin['id']; ?>)">Edit</button>
                            </div>
                        </div>
                        <div class="pin-info">
                            <div class="pin-title"><?php echo $pin['title']; ?></div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-thumbtack"></i>
                <h3 class="empty-state-title">You haven't created any pins yet</h3>
                <p class="empty-state-text">Create your first pin to share your ideas with the world</p>
                <button class="empty-state-btn" onclick="location.href='create_pin.php'">Create Pin</button>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="tab-content" id="saved">
        <?php if(mysqli_num_rows($saved_pins_result) > 0): ?>
            <div class="pin-container">
                <?php while($pin = mysqli_fetch_assoc($saved_pins_result)): ?>
                    <div class="pin" onclick="viewPin(<?php echo $pin['id']; ?>)">
                        <img class="pin-image" src="<?php echo $pin['image_url']; ?>" alt="<?php echo $pin['title']; ?>">
                        <div class="pin-overlay">
                            <div class="pin-actions">
                                <button class="pin-save" onclick="event.stopPropagation(); unsavePin(<?php echo $pin['id']; ?>)">Unsave</button>
                            </div>
                        </div>
                        <div class="pin-info">
                            <div class="pin-title"><?php echo $pin['title']; ?></div>
                            <?php if($pin['board_name']): ?>
                                <div class="pin-user">
                                    <i class="fas fa-folder"></i>
                                    <span><?php echo $pin['board_name']; ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-bookmark"></i>
                <h3 class="empty-state-title">You haven't saved any pins yet</h3>
                <p class="empty-state-text">Browse and save pins that inspire you</p>
                <button class="empty-state-btn" onclick="location.href='index.php'">Browse Pins</button>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="tab-content" id="boards">
        <div class="board-container">
            <div class="create-board" onclick="location.href='create_board.php'">
                <i class="fas fa-plus"></i>
                <div class="create-board-text">Create Board</div>
            </div>
            
            <?php if(mysqli_num_rows($boards_result) > 0): ?>
                <?php while($board = mysqli_fetch_assoc($boards_result)): ?>
                    <div class="board" onclick="viewBoard(<?php echo $board['id']; ?>)">
                        <div class="board-preview">
                            <?php
                            // Get pins from this board
                            $board_pins_sql = "SELECT pins.image_url FROM saved_pins 
                                              JOIN pins ON saved_pins.pin_id = pins.id 
                                              WHERE saved_pins.board_id = {$board['id']} 
                                              LIMIT 4";
                            $board_pins_result = mysqli_query($conn, $board_pins_sql);
                            
                            $preview_count = 0;
                            while($preview_pin = mysqli_fetch_assoc($board_pins_result)) {
                                echo '<img class="board-preview-img" src="' . $preview_pin['image_url'] . '" alt="Preview">';
                                $preview_count++;
                            }
                            
                            // Fill remaining preview slots with placeholders
                            for($i = $preview_count; $i < 4; $i++) {
                                echo '<div class="board-preview-img" style="background-color: #f0f2f5;"></div>';
                            }
                            ?>
                        </div>
                        <div class="board-info">
                            <div class="board-title"><?php echo $board['name']; ?></div>
                            <?php
                            // Count pins in this board
                            $count_sql = "SELECT COUNT(*) as count FROM saved_pins WHERE board_id = {$board['id']}";
                            $count_result = mysqli_query($conn, $count_sql);
                            $count = mysqli_fetch_assoc($count_result)['count'];
                            ?>
                            <div class="board-count"><?php echo $count; ?> pins</div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Tab switching
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', function() {
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                
                const tabId = this.getAttribute('data-tab');
                document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
                document.getElementById(tabId).classList.add('active');
            });
        });
        
        function viewPin(pinId) {
            window.location.href = 'view_pin.php?id=' + pinId;
        }
        
        function editPin(pinId) {
            event.stopPropagation();
            window.location.href = 'edit_pin.php?id=' + pinId;
        }
        
        function unsavePin(pinId) {
            event.stopPropagation();
            if(confirm('Are you sure you want to unsave this pin?')) {
                // Use JavaScript to redirect to unsave_pin.php
                window.location.href = 'unsave_pin.php?id=' + pinId;
            }
        }
        
        function viewBoard(boardId) {
            window.location.href = 'view_board.php?id=' + boardId;
        }
        
        function logout() {
            if(confirm('Are you sure you want to logout?')) {
                window.location.href = 'logout.php';
            }
        }
    </script>
</body>
</html>
