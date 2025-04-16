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

$pin_id = mysqli_real_escape_string($conn, $_GET['id']);
$user_id = $_SESSION['user_id'];

// Check if pin belongs to user
$check_sql = "SELECT * FROM pins WHERE id = $pin_id AND user_id = $user_id";
$check_result = mysqli_query($conn, $check_sql);

if(mysqli_num_rows($check_result) == 0) {
    header("Location: profile.php");
    exit();
}

// Get pin image path
$pin = mysqli_fetch_assoc($check_result);
$image_path = $pin['image_url'];

// Delete saved pins first (foreign key constraint)
$delete_saved_sql = "DELETE FROM saved_pins WHERE pin_id = $pin_id";
mysqli_query($conn, $delete_saved_sql);

// Delete pin
$delete_pin_sql = "DELETE FROM pins WHERE id = $pin_id";
if(mysqli_query($conn, $delete_pin_sql)) {
    // Delete image file if it exists
    if(file_exists($image_path)) {
        unlink($image_path);
    }
    
    header("Location: profile.php");
    exit();
} else {
    echo "Error deleting pin: " . mysqli_error($conn);
}
?>
