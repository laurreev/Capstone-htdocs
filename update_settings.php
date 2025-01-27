<?php
session_start();
include 'db_connection.php';

header('Content-Type: application/json');

$response = array('success' => false);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm-password'];

    if ($password !== $confirm_password) {
        $response['message'] = 'Passwords do not match.';
    } else {
        $hashedPassword = !empty($password) ? password_hash($password, PASSWORD_DEFAULT) : null;

        if (!empty($password)) {
            $sql = "UPDATE user SET username = ?, password = ? WHERE username = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $username, $hashedPassword, $_SESSION['username']);
        } else {
            $sql = "UPDATE user SET username = ? WHERE username = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $username, $_SESSION['username']);
        }

        if ($stmt->execute()) {
            $_SESSION['username'] = $username;
            $response['success'] = true;
            $response['message'] = 'Settings updated successfully.';
        } else {
            $response['message'] = 'Error updating settings: ' . $stmt->error;
        }

        $stmt->close();
    }
} else {
    $response['message'] = 'Invalid request method.';
}

$conn->close();
echo json_encode($response);
?>