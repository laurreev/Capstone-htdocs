<?php
session_start();
include 'db_connection.php';

header('Content-Type: application/json');

$response = array('success' => false);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $seed_id = $_POST['seed_id'];

    // Fetch the seed's image
    $sql = "SELECT image FROM seeds WHERE id='$seed_id'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $image = $row['image'];

        // Delete the seed's image if it exists
        if (!empty($image) && file_exists(__DIR__ . '/uploads/' . $image)) {
            unlink(__DIR__ . '/uploads/' . $image);
        }
    }

    // Delete the seed from the database
    $sql = "DELETE FROM seeds WHERE id='$seed_id'";
    if ($conn->query($sql) === TRUE) {
        $response['success'] = true;
        $response['message'] = "Seed deleted successfully.";
    } else {
        $response['message'] = "Error deleting seed: " . $conn->error;
    }
} else {
    $response['message'] = "Invalid request method";
}

$conn->close();
echo json_encode($response);
?>