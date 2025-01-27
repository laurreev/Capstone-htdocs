<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit();
}

include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $recipient = $_POST['recipient'];
    $message = $_POST['message'];
    $sender = $_SESSION['username'];

    $sql = "INSERT INTO messages (username, message, recipient) VALUES ('$sender', '$message', '$recipient')";
    if ($conn->query($sql) === TRUE) {
        header('Location: farmerhome.php?tab=messages');
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>