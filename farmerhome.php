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

// Fetch messages from admin
$messages = [];
$sql = "SELECT * FROM messages WHERE (username = 'admin' AND recipient = '{$_SESSION['username']}') OR (username = '{$_SESSION['username']}' AND recipient = 'admin') ORDER BY created_at ASC";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmer Home</title>
    <link rel="stylesheet" href="farmerhome.css">
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css' rel='stylesheet' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js'></script>

    <style>
body  {
    background-image: url('images/farm.jpeg'); /* Replace with the path to your farm image */
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    background-attachment: fixed; /* Make the background fixed when scrolling */
}
</style>
</head>
<body>
    <nav class="farmer-nav">
        <ul>
            <li><a href="#" data-content="dashboard">Dashboard</a></li>
            <li><a href="#" data-content="messages">Messages</a></li>
            <li><a href="#" data-content="settings">Settings</a></li>
            <li>
                <button name="logout" class="logout-button" onclick="showLogoutConfirmation()">Logout</button>
            </li>
        </ul>
    </nav>
    <div class="farmer-container">
        <h1 id="greeting">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
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
            <div id="dashboard" class="content-section">
                <div id='calendar-container'></div>
            </div>
            <div id="messages" class="content-section" style="display: none;">
                <h2>Messages</h2>
                <div class="chat-container">
                    <?php if (!empty($messages)): ?>
                        <?php foreach ($messages as $message): ?>
                            <div class="chat-message <?php echo $message['username'] == 'admin' ? 'admin' : 'farmer'; ?>">
                                <strong><?php echo htmlspecialchars($message['username']); ?>:</strong>
                                <?php echo htmlspecialchars($message['message']); ?>
                                <em>(<?php echo $message['created_at']; ?>)</em>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No messages from admin.</p>
                    <?php endif; ?>
                </div>
                <h2>Send a Message to Admin</h2>
                <form id="message-form" action="send_message.php" method="post">
                    <div class="form-group">
                        <label for="message">Message:</label>
                        <textarea id="message" name="message" placeholder="Type your message here..." rows="4" required></textarea>
                        <input type="hidden" name="recipient" value="admin">
                    </div>
                    <button type="submit" class="btn">Send Message</button>
                </form>
            </div>
            <div id="settings" class="content-section" style="display:none;">
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

    <!-- Logout Confirmation Modal -->
    <div id="logout-confirmation-modal" class="modal">
        <div class="modal-content">
            <h2>Confirm Logout</h2>
            <p>Are you sure you want to logout?</p>
            <form id="logout-form" method="post">
                <button type="submit" name="logout" class="btn confirm-btn">Confirm</button>
                <button type="button" class="btn cancel-btn" onclick="closeLogoutConfirmation()">Cancel</button>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
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
        });
        document.querySelectorAll('.farmer-nav a').forEach(link => {
            link.addEventListener('click', function(event) {
                event.preventDefault();
                document.querySelectorAll('.content-section').forEach(section => {
                    section.style.display = 'none';
                });
                const contentId = this.getAttribute('data-content');
                document.getElementById(contentId).style.display = 'block';
                history.pushState(null, '', `?tab=${contentId}`);
                localStorage.setItem('activeTab', contentId);
            });
        });

        function showConfirmation() {
            document.getElementById('confirmation-modal').style.display = 'block';
        }

        function closeConfirmation() {
            document.getElementById('confirmation-modal').style.display = 'none';
        }

        function confirmUpdate() {
            document.getElementById('settings-form').submit();
        }

        function showLogoutConfirmation() {
            document.getElementById('logout-confirmation-modal').style.display = 'block';
        }

        function closeLogoutConfirmation() {
            document.getElementById('logout-confirmation-modal').style.display = 'none';
        }

        function confirmLogout() {
            document.getElementById('logout-form').submit();
        }

        // Handle tab redirection
        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab');
        const activeTab = localStorage.getItem('activeTab') || 'dashboard';
        if (tab) {
            document.querySelectorAll('.content-section').forEach(section => {
                section.style.display = 'none';
            });
            document.getElementById(tab).style.display = 'block';
        } else {
            document.querySelectorAll('.content-section').forEach(section => {
                section.style.display = 'none';
            });
            document.getElementById(activeTab).style.display = 'block';
        }

        // Initialize FullCalendar
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar-container');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth'
            });
            calendar.render();
        });
    </script>
</body>
</html>