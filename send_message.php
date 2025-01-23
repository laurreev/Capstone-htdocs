<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: index.php');
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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $recipient = $_POST['recipient'];
    $message = $_POST['message'];
    $sender = $_SESSION['username'];

    $sql = "INSERT INTO messages (username, message, recipient) VALUES ('$sender', '$message', '$recipient')";
    if ($conn->query($sql) === TRUE) {
        header('Location: adminhome.php?tab=messages&farmer=' . urlencode($recipient));
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['farmer'])) {
    $farmer = $_GET['farmer'];
    $messages = [];

    $sql = "SELECT * FROM messages WHERE (username = '$farmer' AND recipient = 'admin') OR (username = 'admin' AND recipient = '$farmer') ORDER BY created_at ASC";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }
    }

    header('Content-Type: application/json');
    echo json_encode($messages);
}
?>