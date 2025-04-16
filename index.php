<?php
session_start();
$conn = mysqli_connect("localhost", "uklz9ew3hrop3", "zyrbspyjlzjb", "dbugadvpf6p8e1");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch trending pins
$sql = "SELECT pins.*, users.username FROM pins 
        JOIN users ON pins.user_id = users.id 
        ORDER BY created_at DESC LIMIT 20";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PinSpire - Your Visual Discovery Engine</title>
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
        
        .categories {
            display: flex;
            justify-content: center;
            padding: 16px;
            flex-wrap: wrap;
            gap: 8px;
            background-color: white;
            margin-bottom: 16px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }
        
        .category {
            padding: 8px 16px;
            background-color: #efefef;
            border-radius: 24px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .category:hover {
            background-color: #e0e0e0;
        }
        
        .category.active {
            background-color: #000;
            color: white;
        }
        
        .pin-container {
            column-count: 5;
            column-gap: 16px;
            padding: 16px;
            max-width: 1600px;
            margin: 0 auto;
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
        
        .create-pin-btn {
            position: fixed;
            bottom: 24px;
            right: 24px;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background-color: #e60023;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            cursor: pointer;
            transition: transform 0.3s;
            border: none;
        }
        
        .create-pin-btn:hover {
            transform: scale(1.1);
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
    
    <div class="categories">
        <div class="category active">All</div>
        <div class="category">Fashion</div>
        <div class="category">Home</div>
        <div class="category">DIY</div>
        <div class="category">Art</div>
        <div class="category">Food</div>
        <div class="category">Travel</div>
        <div class="category">Fitness</div>
        <div class="category">Technology</div>
    </div>
    
    <div class="pin-container">
        <?php 
        if(mysqli_num_rows($result) > 0) {
            while($row = mysqli_fetch_assoc($result)) {
                $randomHeight = rand(200, 400);
                echo '<div class="pin" onclick="viewPin(' . $row['id'] . ')">';
                echo '<img class="pin-image" src="' . $row['image_url'] . '" alt="' . $row['title'] . '" style="height: ' . $randomHeight . 'px; object-fit: cover;">';
                echo '<div class="pin-overlay">';
                echo '<div class="pin-actions">';
                echo '<button class="pin-save" onclick="event.stopPropagation(); savePin(' . $row['id'] . ')">Save</button>';
                echo '</div>';
                echo '</div>';
                echo '<div class="pin-info">';
                echo '<div class="pin-title">' . $row['title'] . '</div>';
                echo '<div class="pin-user">';
                echo '<img src="https://i.pravatar.cc/150?u=' . $row['user_id'] . '" alt="User">';
                echo '<span>' . $row['username'] . '</span>';
                echo '</div>';
                echo '</div>';
                echo '</div>';
            }
        } else {
            // If no pins exist yet, show some dummy pins
            $dummyPins = [
                ['title' => 'Modern Living Room Design', 'category' => 'Home', 'height' => 350],
                ['title' => 'Summer Fashion Trends', 'category' => 'Fashion', 'height' => 280],
                ['title' => 'Homemade Pizza Recipe', 'category' => 'Food', 'height' => 400],
                ['title' => 'DIY Wall Art', 'category' => 'DIY', 'height' => 320],
                ['title' => 'Travel Photography Tips', 'category' => 'Travel', 'height' => 250],
                ['title' => 'Minimalist Workspace', 'category' => 'Home', 'height' => 300],
                ['title' => 'Healthy Breakfast Ideas', 'category' => 'Food', 'height' => 380],
                ['title' => 'Watercolor Painting Tutorial', 'category' => 'Art', 'height' => 340],
                ['title' => 'Home Workout Routine', 'category' => 'Fitness', 'height' => 290],
                ['title' => 'Tech Gadgets 2023', 'category' => 'Technology', 'height' => 270],
                ['title' => 'Scandinavian Interior Design', 'category' => 'Home', 'height' => 310],
                ['title' => 'Street Style Outfits', 'category' => 'Fashion', 'height' => 360],
                ['title' => 'Vegan Dinner Recipes', 'category' => 'Food', 'height' => 330],
                ['title' => 'Handmade Jewelry', 'category' => 'DIY', 'height' => 240],
                ['title' => 'European City Breaks', 'category' => 'Travel', 'height' => 370]
            ];
            
            foreach($dummyPins as $index => $pin) {
                $userId = $index + 1;
                $pinId = $index + 1;
                $imageId = 100 + $index;
                
                echo '<div class="pin" onclick="viewPin(' . $pinId . ')">';
                echo '<img class="pin-image" src="https://source.unsplash.com/random/' . $imageId . '/?'. strtolower($pin['category']) .'" alt="' . $pin['title'] . '" style="height: ' . $pin['height'] . 'px; object-fit: cover;">';
                echo '<div class="pin-overlay">';
                echo '<div class="pin-actions">';
                echo '<button class="pin-save" onclick="event.stopPropagation(); savePin(' . $pinId . ')">Save</button>';
                echo '</div>';
                echo '</div>';
                echo '<div class="pin-info">';
                echo '<div class="pin-title">' . $pin['title'] . '</div>';
                echo '<div class="pin-user">';
                echo '<img src="https://i.pravatar.cc/150?u=' . $userId . '" alt="User">';
                echo '<span>User' . $userId . '</span>';
                echo '</div>';
                echo '</div>';
                echo '</div>';
            }
        }
        ?>
    </div>
    
    <?php if(isset($_SESSION['user_id'])): ?>
    <button class="create-pin-btn" onclick="location.href='create_pin.php'">
        <i class="fas fa-plus"></i>
    </button>
    <?php endif; ?>

    <script>
        function viewPin(pinId) {
            window.location.href = 'view_pin.php?id=' + pinId;
        }
        
        function savePin(pinId) {
            <?php if(isset($_SESSION['user_id'])): ?>
                // Show board selection modal (to be implemented)
                alert('Pin saved successfully!');
            <?php else: ?>
                alert('Please log in to save pins');
                window.location.href = 'login.php';
            <?php endif; ?>
        }
        
        // Category filtering
        document.querySelectorAll('.category').forEach(category => {
            category.addEventListener('click', function() {
                document.querySelectorAll('.category').forEach(c => c.classList.remove('active'));
                this.classList.add('active');
                
                const categoryName = this.textContent;
                if(categoryName === 'All') {
                    document.querySelectorAll('.pin').forEach(pin => {
                        pin.style.display = 'block';
                    });
                } else {
                    document.querySelectorAll('.pin').forEach(pin => {
                        const pinCategory = pin.querySelector('.pin-title').textContent;
                        if(pinCategory.includes(categoryName)) {
                            pin.style.display = 'block';
                        } else {
                            pin.style.display = 'none';
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>
