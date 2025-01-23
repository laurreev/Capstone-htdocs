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

$data = json_decode(file_get_contents('php://input'), true);
$seedId = $data['seed_id'];
$availability = $data['availability'];

$sql = "UPDATE seeds SET availability = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('si', $availability, $seedId);
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Seed availability updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update seed availability']);
}
$stmt->close();
$conn->close();
?>