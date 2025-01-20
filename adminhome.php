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

// Fetch list of farmers with messages
$farmers = [];
$sql = "SELECT DISTINCT username FROM messages WHERE username IN (SELECT username FROM user WHERE role = 1)";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $farmers[] = $row['username'];
    }
}

// Fetch messages from a specific farmer if selected
$selected_farmer = isset($_GET['farmer']) ? $_GET['farmer'] : '';
$messages = [];
if ($selected_farmer) {
    $sql = "SELECT * FROM messages WHERE (username = '$selected_farmer' AND recipient = 'admin') OR (username = 'admin' AND recipient = '$selected_farmer') ORDER BY created_at ASC";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }
    }
}

// Handle add farmer
if (isset($_POST['add_farmer'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
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

// Fetch all farmers for manage users section
$all_farmers = [];
$sql = "SELECT * FROM user WHERE role = 1";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $all_farmers[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin</title>
    <link rel="stylesheet" href="adminhome.css">
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
    <nav class="admin-nav">
        <ul>
            <li><a href="#" data-content="dashboard">Dashboard</a></li>
            <li><a href="#" data-content="reports">Reports</a></li>
            <li><a href="#" data-content="settings">Settings</a></li>
            <li>
                <button type="button" class="logout-button" onclick="showLogoutConfirmation()">Logout</button>
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
                    <div class="dashboard-item total">
                        <h3>Total Farmers</h3>
                        <p><?php echo $total_farmers_count; ?></p>
                    </div>
                </div>
            </div>
            <div id="reports" class="content-section" style="display:none;">
                <h2>Reports</h2>
                <div class="reports-container">
                    <div class="left-box">
                        <h3>Farmers with Messages</h3>
                        <ul class="farmer-list">
                            <?php foreach ($farmers as $farmer): ?>
                                <li><a href="adminhome.php?tab=reports&farmer=<?php echo htmlspecialchars($farmer); ?>"><?php echo htmlspecialchars($farmer); ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                        <?php if ($selected_farmer): ?>
                            <button class="btn back-btn" onclick="window.location.href='adminhome.php?tab=reports'">Back to messages</button>
                            <h3>Conversation with <?php echo htmlspecialchars($selected_farmer); ?></h3>
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
                                    <p>No messages from this farmer.</p>
                                <?php endif; ?>
                            </div>
                            <form id="reply-form" method="post" action="send_message.php">
                                <input type="hidden" name="recipient" value="<?php echo htmlspecialchars($selected_farmer); ?>">
                                <textarea name="message" rows="4" placeholder="Type your message here..." required></textarea>
                                <button type="submit" class="btn">Send</button>
                            </form>
                        <?php endif; ?>
                    </div>
                    <div class="right-box">
                        <h3>Manage Farmers</h3>
                        <form method="post" action="adminhome.php">
                            <h4>Add Farmer</h4>
                            <div class="form-group">
                                <label for="username">Username:</label>
                                <input type="text" id="username" name="username" required>
                            </div>
                            <div class="form-group">
                                <label for="password">Password:</label>
                                <input type="password" id="password" name="password" required>
                            </div>
                            <div class="form-group">
                                <label for="gender">Gender:</label>
                                <select id="gender" name="gender" required>
                                    <option value="m">Male</option>
                                    <option value="f">Female</option>
                                </select>
                            </div>
                            <button type="submit" name="add_farmer" class="btn">Add Farmer</button>
                        </form>

                        <h4>Existing Farmers</h4>
                        <table>
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Gender</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($all_farmers as $farmer): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($farmer['username']); ?></td>
                                        <td><?php echo htmlspecialchars($farmer['gender']); ?></td>
                                        <td>
                                            <form method="post" action="adminhome.php" style="display:inline;">
                                                <input type="hidden" name="id" value="<?php echo $farmer['id']; ?>">
                                                <input type="hidden" name="username" value="<?php echo $farmer['username']; ?>">
                                                <input type="hidden" name="gender" value="<?php echo $farmer['gender']; ?>">
                                                <button type="submit" name="update_farmer" class="btn">Update</button>
                                            </form>
                                            <form method="post" action="adminhome.php" style="display:inline;">
                                                <input type="hidden" name="id" value="<?php echo $farmer['id']; ?>">
                                                <button type="submit" name="delete_farmer" class="btn">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
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
        </div>
    </div>

    <script>
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
            link.addEventListener('click', function() {
                document.querySelectorAll('.content-section').forEach(section => {
                    section.style.display = 'none';
                });
                document.getElementById(this.getAttribute('data-content')).style.display = 'block';
            });
        });

        /*   // Handle tab redirection
           const urlParams = new URLSearchParams(window.location.search);
            const tab = urlParams.get('tab');
            if (tab) {
                document.querySelectorAll('.content-section').forEach(section => {
                    section.style.display = 'none';
                });
                document.getElementById(tab).style.display = 'block';
            }; */

   

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

        document.querySelectorAll('.chat-box').forEach(box => {
            box.addEventListener('click', function() {
                document.getElementById('recipient').value = this.querySelector('h4').textContent.replace('Conversation with ', '');
            });
        });
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
    </script>
</body>
</html>