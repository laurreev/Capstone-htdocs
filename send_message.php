<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $servername = "localhost";
    $db_username = "root";
    $db_password = "";
    $dbname = "capstone"; // Replace with your database name

    $conn = new mysqli($servername, $db_username, $db_password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $recipient = $_POST['recipient'];
    $message = $_POST['message'];
    $sender = $_SESSION['username'];

    $sql = "INSERT INTO messages (username, message, recipient) VALUES ('$sender', '$message', '$recipient')";
    if ($conn->query($sql) === TRUE) {
        if ($recipient == 'admin') {
            header('Location: farmerhome.php?tab=messages');
        } else {
            header('Location: adminhome.php?tab=reports&farmer=' . $recipient);
        }
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
}
?>