<?php
include 'db_connection.php';

header('Content-Type: application/json');

$response = array('success' => false, 'messages' => array());

if (isset($_GET['farmer_id'])) {
    $farmer_id = $_GET['farmer_id'];

    // Fetch the farmer's username
    $sql = "SELECT username FROM user WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $farmer_id);
    $stmt->execute();
    $stmt->bind_result($farmer_username);
    $stmt->fetch();
    $stmt->close();

    if ($farmer_username) {
        // Fetch the conversation between the admin and the farmer
        $sql = "SELECT username, message, created_at FROM messages WHERE (username = ? AND recipient = 'admin') OR (username = 'admin' AND recipient = ?) ORDER BY created_at ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $farmer_username, $farmer_username);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $response['messages'][] = $row;
        }

        $stmt->close();
        $response['success'] = true;
    } else {
        $response['message'] = 'Farmer not found.';
    }
} else {
    $response['message'] = 'Farmer ID not provided.';
}

$conn->close();
echo json_encode($response);
?>