<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Database connection
$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "capstone"; // Replace with your database name

$conn = new mysqli($servername, $db_username, $db_password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$username = $_POST['username'];
$password = $_POST['password'];
$confirm_password = $_POST['confirm-password'];

if ($password !== $confirm_password) {
    $_SESSION['error'] = "Passwords do not match.";
    header('Location: adminhome.php?tab=settings');
    exit();
}

if (!empty($password)) {
    $sql = "UPDATE user SET username = ?, password = ? WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $password, $_SESSION['username']);
} else {
    $sql = "UPDATE user SET username = ? WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $_SESSION['username']);
}

if ($stmt->execute()) {
    $_SESSION['username'] = $username;
    $_SESSION['success'] = "Settings updated successfully.";
} else {
    $_SESSION['error'] = "Error updating settings.";
}

$stmt->close();
$conn->close();

header('Location: adminhome.php?tab=settings');
exit();
?>