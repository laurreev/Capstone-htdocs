<?php
session_start();
include 'db_connection.php';

header('Content-Type: application/json');

$response = array('success' => false);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $seed_id = isset($_POST['seed_id']) ? $_POST['seed_id'] : '';
    $seed_name = $_POST['seed_name'];
    $description = $_POST['description'];
    $availability = $_POST['availability'];
    $imageName = '';

    if (!empty($_FILES['image']['tmp_name'])) {
        $image = $_FILES['image']['tmp_name'];
        $imageName = basename($_FILES['image']['name']);
        $targetDir = __DIR__ . '/uploads/';
        $targetFile = $targetDir . $imageName;

        // Ensure the uploads directory exists
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        // Move the uploaded file to the target directory
        if (!move_uploaded_file($image, $targetFile)) {
            $response['message'] = 'Failed to move uploaded file';
            echo json_encode($response);
            exit;
        }
    }

    if ($seed_id) {
        // Update existing seed
        if ($imageName) {
            $sql = "UPDATE seeds SET seed_name = ?, description = ?, availability = ?, image = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ssssi', $seed_name, $description, $availability, $imageName, $seed_id);
        } else {
            $sql = "UPDATE seeds SET seed_name = ?, description = ?, availability = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sssi', $seed_name, $description, $availability, $seed_id);
        }
    } else {
        // Check if the seed name already exists
        $sql = "SELECT COUNT(*) FROM seeds WHERE seed_name = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $seed_name);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            $response['message'] = 'Seed name already exists';
            echo json_encode($response);
            exit;
        }

        // Insert new seed
        $sql = "INSERT INTO seeds (seed_name, description, availability, image) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssss', $seed_name, $description, $availability, $imageName);
    }

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = $seed_id ? 'Seed updated successfully' : 'Seed added successfully';
    } else {
        $response['message'] = 'Failed to save seed';
    }
    $stmt->close();
} else {
    $response['message'] = 'Invalid request method';
}

$conn->close();
echo json_encode($response);
?>