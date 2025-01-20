<?php
session_start();

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
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare and bind
    $stmt = $conn->prepare("SELECT password, role FROM user WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($stored_password, $role);
    $stmt->fetch();

    if ($stmt->num_rows > 0 && $password === $stored_password) {
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $role;

        if ($role == 0) {
            header('Location: adminhome.php?tab=dashboard');
        } else if ($role == 1) {
            header('Location: farmerhome.php');
        }
        exit();
    } else {
        $error = 'Invalid username or password';
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="login.css">
    <title>Login Page</title>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <?php if (isset($error)): ?>
            <p style="color: red;"><?php echo $error; ?></p>
        <?php endif; ?>
        <form action="index.php" method="post">
            <input type="text" name="username" placeholder="Username" required>
            <div class="password-container">
                <input type="password" name="password" placeholder="Password" required>
                <span class="reveal-password">👁️</span>
            </div>
            <button type="submit">Login</button>
        </form>
    </div>
    <script>
        document.querySelector('.reveal-password').addEventListener('mouseover', function() {
            const passwordInput = document.querySelector('.password-container input[type="password"]');
            passwordInput.type = 'text';
        });

        document.querySelector('.reveal-password').addEventListener('mouseout', function() {
            const passwordInput = document.querySelector('.password-container input[type="text"]');
            passwordInput.type = 'password';
        });
    </script>
</body>
</html>