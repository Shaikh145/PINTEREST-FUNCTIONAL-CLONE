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

// Delete saved pins in this board first (foreign key constraint)
$delete_saved_sql = "DELETE FROM saved_pins WHERE board_id = $board_id";
mysqli_query($conn, $delete_saved_sql);

// Delete board
$delete_board_sql = "DELETE FROM boards WHERE id = $board_id";
if(mysqli_query($conn, $delete_board_sql)) {
    header("Location: profile.php");
    exit();
} else {
    echo "Error deleting board: " . mysqli_error($conn);
}
?>
