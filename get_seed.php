<?php
session_start();
include 'db_connection.php';

header('Content-Type: application/json');

$response = array('success' => false);

if (isset($_GET['id'])) {
    $seed_id = $_GET['id'];

    $sql = "SELECT id, seed_name, description, availability, image FROM seeds WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $seed_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $response['success'] = true;
        $response['seed'] = $result->fetch_assoc();
    } else {
        $response['message'] = 'Seed not found';
    }

    $stmt->close();
} else {
    $response['message'] = 'Invalid request';
}

$conn->close();
echo json_encode($response);
?>