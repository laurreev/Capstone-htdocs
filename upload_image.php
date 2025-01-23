<?php
session_start();

if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Database connection
$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "capstone"; // Replace with your database name

$conn = new mysqli($servername, $db_username, $db_password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $seedId = $_POST['seed_id'];
    $image = $_FILES['image']['tmp_name'];
    $imageData = file_get_contents($image);

    // Update database with image data
    $sql = "UPDATE seeds SET image = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $imageData, $seedId);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Image uploaded successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update database']);
    }
    $stmt->close();
}

$conn->close();
?>