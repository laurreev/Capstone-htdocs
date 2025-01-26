<?php
session_start();
include 'db_connection.php';

header('Content-Type: application/json');

$db_password = "";
$dbname = "capstone"; // Replace with your database name

$conn = new mysqli($servername, $db_username, $db_password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $seedId = $_POST['seed_id'];

    // Fetch the current image path
    $sql = "SELECT image FROM seeds WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $seedId);
    $stmt->execute();
    $stmt->bind_result($imageName);
    $stmt->fetch();
    $stmt->close();

    if ($imageName) {
        $imagePath = __DIR__ . '/uploads/' . $imageName;

        // Delete the image file
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }

        // Update the database to remove the image
        $sql = "UPDATE seeds SET image = NULL WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $seedId);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Image deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update database']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Image not found']);
    }
}

$conn->close();
?>