<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit();
}

include 'db_connection.php';

// Handle add farmer
if (isset($_POST['add_farmer'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $gender = $_POST['gender'];

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO user (username, password, role, gender) VALUES (?, ?, 1, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $hashedPassword, $gender);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Farmer added successfully.";
    } else {
        $_SESSION['error'] = "Error adding farmer: " . $stmt->error;
    }

    $stmt->close();
}

// Handle update farmer
if (isset($_POST['update_farmer'])) {
    $id = $_POST['id'];
    $username = $_POST['username'];
    $gender = $_POST['gender'];
    $password = $_POST['password'];

    if (!empty($password)) {
        // Hash the new password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE user SET username = ?, password = ?, gender = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $username, $hashedPassword, $gender, $id);
    } else {
        $sql = "UPDATE user SET username = ?, gender = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $username, $gender, $id);
    }

    if ($stmt->execute()) {
        $_SESSION['success'] = "Farmer updated successfully.";
    } else {
        $_SESSION['error'] = "Error updating farmer: " . $stmt->error;
    }

    $stmt->close();
}

// Handle delete farmer
if (isset($_POST['delete_farmer'])) {
    $id = $_POST['id'];

    $sql = "DELETE FROM user WHERE id='$id'";
    if ($conn->query($sql) === TRUE) {
        $_SESSION['success'] = "Farmer deleted successfully.";
    } else {
        $_SESSION['error'] = "Error deleting farmer: " . $conn->error;
    }
}

// Fetch all farmers
$farmers = [];
$sql = "SELECT * FROM user WHERE role = 1";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $farmers[] = $row;
    }
}

$conn->close();
?>