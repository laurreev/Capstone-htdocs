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

// Fetch number of male farmers
$male_farmers_count = 0;
$sql = "SELECT COUNT(*) as count FROM user WHERE role = 1 AND gender = 'm'";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $male_farmers_count = $row['count'];
}

// Fetch number of female farmers
$female_farmers_count = 0;
$sql = "SELECT COUNT(*) as count FROM user WHERE role = 1 AND gender = 'f'";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $female_farmers_count = $row['count'];
}

// Fetch total number of farmers
$total_farmers_count = $male_farmers_count + $female_farmers_count;

$conn->close();
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
            <div id="dashboard" class="content-section">
                <h2>Dashboard</h2>
                <div class="dashboard-grid">
                    <div class="dashboard-item">
                        <h3>Male Farmers</h3>
                        <p><?php echo $male_farmers_count; ?></p>
                    </div>
                    <div class="dashboard-item">
                        <h3>Female Farmers</h3>
                        <p><?php echo $female_farmers_count; ?></p>
                    </div>
                </div>
                <div class="dashboard-item total">
                    <h3>Total Farmers</h3>
                    <p><?php echo $total_farmers_count; ?></p>
                </div>
            </div>
            <div id="manage-users" class="content-section" style="display: none;">This is the Manage Users content.</div>
            <div id="reports" class="content-section" style="display: none;">This is the Reports content.</div>
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

            document.querySelectorAll('.admin-nav a').forEach(link => {
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