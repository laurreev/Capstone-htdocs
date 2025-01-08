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
    <title>Farmer</title>
    <link rel="stylesheet" href="farmerhome.css">
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css' rel='stylesheet' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js'></script>
</head>
<body>
    <nav class="farmer-nav">
        <ul>
            <li><a href="#" data-content="calendar">Calendar</a></li>
            <li><a href="#" data-content="manage-crops">Manage Crops</a></li>
            <li><a href="#" data-content="settings">Settings</a></li>
            <li>
                <form id="logout-form" method="post" style="display: inline;">
                    <button type="submit" name="logout" class="logout-button" onclick="return confirmLogout()">Logout</button>
                </form>
            </li>
        </ul>
    </nav>
    <div class="farmer-container">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        <div id="content">
            <div id="calendar" class="content-section">
                <div id='calendar-container'></div>
            </div>
            <div id="manage-crops" class="content-section" style="display: none;">This is the Manage Crops content.</div>
            <div id="settings" class="content-section" style="display: none;">This is the Settings content.</div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar-container');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth'
            });
            calendar.render();
        });

        document.querySelectorAll('.farmer-nav a').forEach(link => {
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