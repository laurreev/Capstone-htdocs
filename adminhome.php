<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin</title>
    <link rel="stylesheet" href="adminhome.css">
</head>
<body>
    <nav class="admin-nav">
        <ul>
            <li><a href="#" data-content="dashboard">Dashboard</a></li>
            <li><a href="#" data-content="manage-users">Manage Users</a></li>
            <li><a href="#" data-content="reports">Reports</a></li>
            <li><a href="#" data-content="settings">Settings</a></li>
            <li>
                <form id="logout-form" method="post" style="display: inline;">
                    <button type="submit" name="logout" class="logout-button" onclick="return confirmLogout()">Logout</button>
                </form>
            </li>
        </ul>
    </nav>
    <div class="admin-container">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        <div id="content">
            <div id="dashboard" class="content-section">This is the Dashboard content.</div>
            <div id="manage-users" class="content-section" style="display: none;">This is the Manage Users content.</div>
            <div id="reports" class="content-section" style="display: none;">This is the Reports content.</div>
            <div id="settings" class="content-section" style="display: none;">This is the Settings content.</div>
        </div>
    </div>
    <script>
        document.querySelectorAll('.admin-nav a').forEach(link => {
            link.addEventListener('click', function(event) {
                event.preventDefault();
                document.querySelectorAll('.content-section').forEach(section => {
                    section.style.display = 'none';
                });
                document.getElementById(this.getAttribute('data-content')).style.display = 'block';
            });
        });

        function confirmLogout() {
            return confirm('Are you sure you want to logout?');
        }
    </script>
</body>
</html>