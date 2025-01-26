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
    $image = $_FILES['image']['tmp_name'];
    $imageName = basename($_FILES['image']['name']);
    $targetDir = __DIR__ . '/uploads/';
    $targetFile = $targetDir . $imageName;

    // Ensure the uploads directory exists
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    // Fetch the current image path
    $sql = "SELECT image FROM seeds WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $seedId);
    $stmt->execute();
    $stmt->bind_result($oldImageName);
    $stmt->fetch();
    $stmt->close();

    // Move the uploaded file to the target directory
    if (move_uploaded_file($image, $targetFile)) {
        // Delete the old image file if it exists
        if ($oldImageName) {
            $oldImagePath = $targetDir . $oldImageName;
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }
        }

        // Update database with new image file path
        $sql = "UPDATE seeds SET image = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('si', $imageName, $seedId);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Image uploaded successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update database']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file']);
    }
}

$conn->close();
?>