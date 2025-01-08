<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit();
}

if (isset($_POST['logout'])) {
    session_destroy();
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
        <h1 id="greeting"></h1>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert success">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert error">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        <div id="content">
            <div id="calendar" class="content-section">
                <div id='calendar-container'></div>
            </div>
            <div id="manage-crops" class="content-section" style="display: none;">This is the Manage Crops content.</div>
            <div id="settings" class="content-section" style="display: none;">
                <h2>Settings</h2>
                <form id="settings-form" action="update_settings.php" method="post">
                    <div class="form-group">
                        <label for="username">Username:</label>
                        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($_SESSION['username']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="password">New Password:</label>
                        <input type="password" id="password" name="password">
                    </div>
                    <div class="form-group">
                        <label for="confirm-password">Confirm Password:</label>
                        <input type="password" id="confirm-password" name="confirm-password">
                    </div>
                    <button type="button" class="btn" onclick="showConfirmation()">Update Settings</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirmation-modal" class="modal">
        <div class="modal-content">
            <h2>Confirm Update</h2>
            <p>Are you sure you want to update your settings?</p>
            <button class="btn confirm-btn" onclick="confirmUpdate()">Confirm</button>
            <button class="btn cancel-btn" onclick="closeConfirmation()">Cancel</button>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar-container');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth'
            });
            calendar.render();

            // Set greeting based on time of day
            var greetingEl = document.getElementById('greeting');
            var now = new Date();
            var hours = now.getHours();
            var greeting;
            if (hours < 12) {
                greeting = 'Good morning';
            } else if (hours < 18) {
                greeting = 'Good afternoon';
            } else {
                greeting = 'Good evening';
            }
            greetingEl.textContent = greeting + ', <?php echo htmlspecialchars($_SESSION['username']); ?>!';

            document.querySelectorAll('.farmer-nav a').forEach(link => {
                link.addEventListener('click', function(event) {
                    event.preventDefault();
                    document.querySelectorAll('.content-section').forEach(section => {
                        section.style.display = 'none';
                    });
                    document.getElementById(this.getAttribute('data-content')).style.display = 'block';
                });
            });

            // Handle tab redirection
            const urlParams = new URLSearchParams(window.location.search);
            const tab = urlParams.get('tab');
            if (tab) {
                document.querySelectorAll('.content-section').forEach(section => {
                    section.style.display = 'none';
                });
                document.getElementById(tab).style.display = 'block';
            }
        });

        function confirmLogout() {
            return confirm('Are you sure you want to logout?');
        }

        function showConfirmation() {
            document.getElementById('confirmation-modal').style.display = 'block';
        }

        function closeConfirmation() {
            document.getElementById('confirmation-modal').style.display = 'none';
        }

        function confirmUpdate() {
            document.getElementById('settings-form').submit();
        }
    </script>
</body>
</html>