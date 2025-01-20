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

// Handle add farmer
if (isset($_POST['add_farmer'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $gender = $_POST['gender'];

    $sql = "INSERT INTO user (username, password, role, gender) VALUES ('$username', '$password', 1, '$gender')";
    if ($conn->query($sql) === TRUE) {
        $_SESSION['success'] = "Farmer added successfully.";
    } else {
        $_SESSION['error'] = "Error adding farmer: " . $conn->error;
    }
}

// Handle update farmer
if (isset($_POST['update_farmer'])) {
    $id = $_POST['id'];
    $username = $_POST['username'];
    $gender = $_POST['gender'];

    $sql = "UPDATE user SET username='$username', gender='$gender' WHERE id='$id'";
    if ($conn->query($sql) === TRUE) {
        $_SESSION['success'] = "Farmer updated successfully.";
    } else {
        $_SESSION['error'] = "Error updating farmer: " . $conn->error;
    }
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
