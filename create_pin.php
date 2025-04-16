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
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $user_id = $_SESSION['user_id'];
    
    // Handle image  $_POST['category']);
    $user_id = $_SESSION['user_id'];
    
    // Handle image
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png");
        $filename = $_FILES["image"]["name"];
        $filetype = $_FILES["image"]["type"];
        $filesize = $_FILES["image"]["size"];
        
        // Verify file extension
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if(!array_key_exists($ext, $allowed)) {
            $error = "Error: Please select a valid file format.";
        }
        
        // Verify file size - 5MB maximum
        $maxsize = 5 * 1024 * 1024;
        if($filesize > $maxsize) {
            $error = "Error: File size is larger than the allowed limit.";
        }
        
        // Verify MIME type of the file
        if(in_array($filetype, $allowed)) {
            // Check if file exists before uploading it
            $target_dir = "uploads/";
            
            // Create directory if it doesn't exist
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $target_file = $target_dir . uniqid() . "_" . basename($filename);
            
            if(move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                // Insert pin into database
                $image_url = $target_file;
                $sql = "INSERT INTO pins (user_id, title, description, image_url, category, created_at) 
                        VALUES ($user_id, '$title', '$description', '$image_url', '$category', NOW())";
                
                if(mysqli_query($conn, $sql)) {
                    $success = "Pin created successfully!";
                } else {
                    $error = "Error: " . mysqli_error($conn);
                }
            } else {
                $error = "Error: There was a problem uploading your file. Please try again.";
            }
        } else {
            $error = "Error: There was a problem with your file. Please try again.";
        }
    } else {
        $error = "Error: Please select an image.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Pin - PinSpire</title>
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
        
        .create-pin-container {
            background-color: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 800px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        
        .create-pin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 24px;
            border-bottom: 1px solid #efefef;
        }
        
        .create-pin-title {
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
        
        .create-pin-content {
            display: flex;
            flex-direction: row;
            padding: 24px;
        }
        
        @media (max-width: 768px) {
            .create-pin-content {
                flex-direction: column;
            }
        }
        
        .image-upload {
            flex: 1;
            padding-right: 24px;
            border-right: 1px solid #efefef;
        }
        
        @media (max-width: 768px) {
            .image-upload {
                padding-right: 0;
                border-right: none;
                border-bottom: 1px solid #efefef;
                padding-bottom: 24px;
                margin-bottom: 24px;
            }
        }
        
        .upload-area {
            border: 2px dashed #ddd;
            border-radius: 16px;
            padding: 40px;
            text-align: center;
            cursor: pointer;
            transition: background-color 0.3s;
            height: 300px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow: hidden;
        }
        
        .upload-area:hover {
            background-color: #f9f9f9;
        }
        
        .upload-icon {
            font-size: 48px;
            color: #767676;
            margin-bottom: 16px;
        }
        
        .upload-text {
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .upload-subtext {
            color: #767676;
            font-size: 14px;
        }
        
        .upload-input {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }
        
        .preview-image {
            max-width: 100%;
            max-height: 300px;
            border-radius: 8px;
            display: none;
        }
        
        .pin-details {
            flex: 1;
            padding-left: 24px;
        }
        
        @media (max-width: 768px) {
            .pin-details {
                padding-left: 0;
            }
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
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
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
        
        .create-pin-btn {
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
        
        .create-pin-btn:hover {
            background-color: #d50c22;
        }
        
        .create-pin-btn:disabled {
            background-color: #f0f0f0;
            color: #767676;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <div class="create-pin-container">
        <div class="create-pin-header">
            <h2 class="create-pin-title">Create Pin</h2>
            <button class="close-btn" onclick="location.href='index.php'"><i class="fas fa-times"></i></button>
        </div>
        
        <?php if($error): ?>
            <div class="error-message" style="margin: 16px 24px;"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if($success): ?>
            <div class="success-message" style="margin: 16px 24px;"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form action="create_pin.php" method="POST" enctype="multipart/form-data">
            <div class="create-pin-content">
                <div class="image-upload">
                    <div class="upload-area" id="uploadArea">
                        <i class="fas fa-cloud-upload-alt upload-icon"></i>
                        <div class="upload-text">Click to upload</div>
                        <div class="upload-subtext">Recommendation: Use high-quality .jpg files less than 20MB</div>
                        <input type="file" name="image" id="imageInput" class="upload-input" accept="image/*" required>
                        <img id="previewImage" class="preview-image" src="#" alt="Preview">
                    </div>
                </div>
                
                <div class="pin-details">
                    <div class="form-group">
                        <label for="title">Title</label>
                        <input type="text" id="title" name="title" placeholder="Add a title" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" placeholder="Tell everyone what your Pin is about"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="category">Category</label>
                        <select id="category" name="category" required>
                            <option value="">Select a category</option>
                            <option value="Fashion">Fashion</option>
                            <option value="Home">Home</option>
                            <option value="DIY">DIY</option>
                            <option value="Art">Art</option>
                            <option value="Food">Food</option>
                            <option value="Travel">Travel</option>
                            <option value="Fitness">Fitness</option>
                            <option value="Technology">Technology</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="create-pin-btn" id="createPinBtn" disabled>Create Pin</button>
                </div>
            </div>
        </form>
    </div>

    <script>
        const imageInput = document.getElementById('imageInput');
        const previewImage = document.getElementById('previewImage');
        const uploadArea = document.getElementById('uploadArea');
        const createPinBtn = document.getElementById('createPinBtn');
        
        imageInput.addEventListener('change', function() {
            if(this.files && this.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                    previewImage.style.display = 'block';
                    uploadArea.querySelector('.upload-icon').style.display = 'none';
                    uploadArea.querySelector('.upload-text').style.display = 'none';
                    uploadArea.querySelector('.upload-subtext').style.display = 'none';
                    createPinBtn.disabled = false;
                }
                
                reader.readAsDataURL(this.files[0]);
            }
        });
        
        // Redirect to profile page after successful pin creation
        <?php if($success): ?>
        setTimeout(function() {
            window.location.href = 'profile.php';
        }, 2000);
        <?php endif; ?>
    </script>
</body>
</html>
