<?php

include 'db_connection.php';

// Fetch all farmers excluding the admin
$sql = "SELECT username FROM user WHERE username != 'admin'"; // Replace with your table and column names
$result = $conn->query($sql);

$farmers = array();
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $farmers[] = $row['username']; // Replace with your column name
    }
} else {
    error_log("No farmers found");
}

// Debugging: Log the fetched farmers
error_log("Fetched farmers: " . json_encode($farmers));

// Return the farmers in JSON format
header('Content-Type: application/json');
echo json_encode($farmers);

$conn->close();
?>