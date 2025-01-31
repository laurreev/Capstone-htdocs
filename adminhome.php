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

include 'db_connection.php';

// Fetch seeds from the database
$seeds = [];
$sql = "SELECT * FROM seeds";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $seeds[] = $row;
    }
} else {
    echo "Error fetching seeds: " . $conn->error;
}

// Fetch all seeds for items list section
$all_seeds = [];
$sql = "SELECT * FROM seeds";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $all_seeds[] = $row;
    }
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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['form_type']) && $_POST['form_type'] == 'add_seed') {
        // Handle adding a seed
        if (isset($_POST['seed_name']) && isset($_POST['description']) && isset($_POST['availability'])) {
            $seed_name = $_POST['seed_name'];
            $description = $_POST['description'];
            $availability = $_POST['availability'];

            // Update the column names to match your database schema
            $sql = "INSERT INTO seeds (seed_name, description, availability) VALUES ('$seed_name', '$description', '$availability')";
            if ($conn->query($sql) === TRUE) {
                $_SESSION['success'] = "Seed added successfully.";
            } else {
                $_SESSION['error'] = "Error adding seed: " . $conn->error;
            }
        } else {
            $_SESSION['error'] = "All fields are required.";
        }
        header('Location: adminhome.php?tab=manage-farmer');
        exit();
    } elseif (isset($_POST['id']) && !empty($_POST['id'])) {
        // Handle updating a farmer
        $id = $_POST['id'];
        $username = $_POST['username'];
        $gender = $_POST['gender'];

        if (isset($_POST['password']) && $_POST['password'] !== '*****') {
            $password = $_POST['password'];
            // Hash the new password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $sql = "UPDATE user SET username = ?, password = ?, gender = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssi", $username, $hashedPassword, $gender, $id);
        } else {
            $sql = "UPDATE user SET username = ?, gender = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $username, $gender, $id);
        }
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Farmer updated successfully.";
        } else {
            $_SESSION['error'] = "Error updating farmer: " . $stmt->error;
        }
        
        $stmt->close();
        header('Location: adminhome.php?tab=items-list');
        exit();
        } elseif (isset($_POST['delete_id']) && !empty($_POST['delete_id'])) {
            // Handle deleting a farmer
            $id = $_POST['delete_id'];
        
            $sql = "DELETE FROM user WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
        
            if ($stmt->execute()) {
                $_SESSION['success'] = "Farmer deleted successfully.";
            } else {
                $_SESSION['error'] = "Error deleting farmer: " . $stmt->error;
            }
        
            $stmt->close();
            header('Location: adminhome.php?tab=manage-farmer');
            exit();
        } else {
            // Handle adding a farmer
            $username = $_POST['username'];
            $password = $_POST['password'];
            $gender = $_POST['gender'];
        
            // Hash the password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
            $sql = "INSERT INTO user (username, password, role, gender) VALUES (?, ?, 1, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $username, $hashedPassword, $gender);
        
            if ($stmt->execute()) {
                $_SESSION['success'] = "Farmer added successfully.";
            } else {
                $_SESSION['error'] = "Error adding farmer: " . $stmt->error;
            }
    }
    header('Location: adminhome.php?tab=manage-farmer');
    exit();
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
            <li><a href="#" data-content="messages">Messages</a></li> <!-- Updated nav item -->
            <li><a href="#" data-content="settings">Settings</a></li>
            <li>
                <button type="button" class="logout-button" onclick="showLogoutConfirmation()">Logout</button>
            </li>
        </ul>
    </nav>
    <div class="admin-container">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert success" id="success-alert"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <script>
            setTimeout(function() {
                document.getElementById('success-alert').style.display = 'none';
            }, 3000);
        </script>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert error" id="error-alert"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <script>
            setTimeout(function() {
                document.getElementById('error-alert').style.display = 'none';
            }, 3000);
        </script>
    <?php endif; ?>
    <button type="button" class="toggle-panel-button" onclick="toggleSidePanel()">☰</button>
    <div class="side-panel">
            <ul>
                <li><a href="#" data-content="dashboard">Dashboard</a></li>
                <li><a href="#" data-content="items-list">Items List</a></li>
                <li><a href="#" data-content="category-list">Category List</a></li>
                <li><a href="#" data-content="reservation-list">Reservation List</a></li>
                <li><a href="#" data-content="reservation-report">Reservation Report</a></li>
                <li><a href="#" data-content="manage-farmer">Manage Farmers</a></li>
            </ul>
        </div>
        <di class="main-content">
            <div id="alert" class="alert" style="display:none;"></div>
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
            <div id="items-list" class="content-section" style="display:none;">
                <h2>Variety of Seeds</h2>
                <button class="btn add-seed-btn" onclick="showAddSeedForm()">Add new seed</button>
                <table class="seeds-table">
                    <thead>
                        <tr>
                            <th>Seed Name</th>
                            <th>Description</th>
                            <th>Availability</th>
                            <th>Image</th>
                            <th>Image Actions</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($all_seeds as $seed): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($seed['seed_name']); ?></td>
                            <td><?php echo htmlspecialchars($seed['description']); ?></td>
                            <td><?php echo htmlspecialchars($seed['availability']); ?></td>
                            <td>
                                <?php if (!empty($seed['image'])): ?>
                                    <img src="uploads/<?php echo htmlspecialchars($seed['image']); ?>" alt="Seed Image" width="100">
                                <?php else: ?>
                                    No image
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($seed['image'])): ?>
                                    <button class="btn edit-btn" onclick="updateImage(<?php echo $seed['id']; ?>)">Update Image</button>
                                    <button class="btn delete-btn" onclick="showDeleteImageConfirmation(<?php echo $seed['id']; ?>)">Delete Image</button>
                                <?php else: ?>
                                    <form class="upload-image-form" enctype="multipart/form-data">
                                        <input type="file" name="image" required>
                                        <input type="hidden" name="seed_id" value="<?php echo $seed['id']; ?>">
                                        <button type="button" class="btn upload-seed-btn" onclick="showUploadConfirmation(this)">Upload Image</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button type="button" class="btn action-btn" onclick="toggleActionButtons(<?php echo $seed['id']; ?>)">Action</button>
                                <div id="action-buttons-<?php echo $seed['id']; ?>" class="action-buttons" style="display:none;">
                                    <button type="button" class="btn edit-btn" onclick="showEditSeedForm(<?php echo $seed['id']; ?>)">Edit</button>
                                    <button type="button" class="btn delete-btn" onclick="showDeleteSeedConfirmation(<?php echo $seed['id']; ?>)">Delete</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
                <div id="category-list" class="content-section" style="display:none;">
                    <h2>Category List</h2>
                    <!-- Add your category list content here -->
                </div>
                <div id="reservation-list" class="content-section" style="display:none;">
                    <h2>Reservation List</h2>
                    <!-- Add your reservation list content here -->
                </div>
                <div id="reservation-report" class="content-section" style="display:none;">
                    <h2>Reservation Report</h2>
                    <!-- Add your reservation report content here -->
                </div>
                <div id="manage-farmer" class="content-section" style="display:none;">
    <h2>Manage Farmers</h2>
    <div class="manage-farmers-container">
        <form id="manage-farmers-form" method="post" action="adminhome.php">
            <h4>Add Farmer</h4>
            <input type="hidden" id="farmer-id" name="id">
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
            <button type="button" class="btn add-farmer-btn" onclick="showAddConfirmation()">Add Farmer</button>
            <button type="button" class="btn edit-farmer-btn" onclick="showUpdateConfirmation()" style="display:none;">Update Farmer</button>
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
                <button type="button" class="btn edit-farmer-btn" data-action="edit" data-id="<?php echo $farmer['id']; ?>" onclick="editFarmer('<?php echo $farmer['id']; ?>', '<?php echo $farmer['username']; ?>', '*****', '<?php echo $farmer['gender']; ?>')">Edit</button>
                <button type="button" class="btn delete-farmer-btn" data-action="delete" data-id="<?php echo $farmer['id']; ?>" onclick="showDeleteConfirmation('<?php echo $farmer['id']; ?>')">Delete</button>
            </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
                
<div id="messages" class="content-section" style="display:none;">
    <h2>Messages</h2>
    <div class="messages-container">
        <div class="message-box" style="width: 70%;"> <!-- Updated width -->
            <h3 class="h3label">Farmers with Messages</h3>
            <ul class="farmer-list">
                <?php foreach ($farmers as $farmer): ?>
                    <li><a href="#" data-farmer="<?php echo htmlspecialchars($farmer); ?>" onclick="openConversation('<?php echo htmlspecialchars($farmer); ?>')"><?php echo htmlspecialchars($farmer); ?></a></li>
                <?php endforeach; ?>
            </ul>
            <button class="btn back-btn" onclick="showFarmersList()" style="display:none;">Back to messages</button>
            <button class="btn ref-btn" onclick="refreshMessages()" style="display:none;">Refresh Messages</button>
        </div>
        <div class="find-farmers-box" style="width: 30%;"> <!-- New container -->
            <h3 class="h3label">Find Farmers</h3>
            <input type="text" id="search-farmer" placeholder="Search for a farmer...">
            <button class="btn search-btn" onclick="searchFarmer()">Search</button>
            <ul id="all-farmer-list" class="scrollable-list">
                <!-- Farmer list will be populated by JavaScript -->
            </ul>
        </div>
    </div>
    <div class="conversation-box" style="display:none;"> <!-- Moved outside of messages-container -->
        <h3>Conversation with <span id="conversation-farmer"></span></h3>
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
            <input type="hidden" name="recipient" id="recipient" value="<?php echo htmlspecialchars($selected_farmer); ?>">
            <textarea name="message" rows="4" placeholder="Type your message here..." required></textarea>
            <button type="submit" class="btn send-message-btn" id="send-message-btn">Send Message</button>
        </form>
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
                    <button type="button" class="btn" onclick="showSettingsConfirmation()">Update Settings</button>
                </form>
            </div>
        </div>
    </div>

<!-- Add Confirmation Modal -->
<div id="add-confirmation-modal" class="modal">
    <div class="modal-content">
        <h2>Confirm Add</h2>
        <p>Are you sure you want to add this farmer?</p>
        <button class="btn confirm-btn" onclick="confirmAdd()">Confirm</button>
        <button class="btn cancel-btn" onclick="closeAddConfirmation()">Cancel</button>
    </div>
</div>

<!-- Update Confirmation Modal -->
<div id="update-confirmation-modal" class="modal">
    <div class="modal-content">
        <h2>Confirm Update</h2>
        <p>Are you sure you want to update this farmer?</p>
        <button class="btn confirm-btn" onclick="confirmUpdate()">Confirm</button>
        <button class="btn cancel-btn" onclick="closeUpdateConfirmation()">Cancel</button>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="delete-confirmation-modal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeDeleteConfirmation()">&times;</span>
        <h2>Confirm Delete</h2>
        <p>Are you sure you want to delete this farmer?</p>
        <form id="delete-form" method="post" action="adminhome.php">
            <input type="hidden" id="delete-farmer-id" name="delete_id">
            <div class="modal-buttons">
                <button type="submit" class="btn confirm-btn">Confirm</button>
                <button type="button" class="btn cancel-btn" onclick="closeDeleteConfirmation()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Upload Seed Confirmation Modal -->
<div id="upload-seed-modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeUploadSeedModal()">&times;</span>
            <p id="upload-seed-message"></p>
            <button id="upload-seed-confirm-button" class="btn upload-seed-confirm-button">Confirm</button>
            <button class="btn upload-seed-cancel-button" onclick="closeUploadSeedModal()">Cancel</button>
        </div>
    </div>
<!-- Add Seed Form Modal -->
<div id="add-seed-form-modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeAddSeedFormModal()">&times;</span>
            <form id="add-seed-form" enctype="multipart/form-data">
                <input type="hidden" id="seed_id" name="seed_id">
                <label for="seed_name">Seed Name:</label>
                <input type="text" id="seed_name" name="seed_name" required>
                <label for="description">Description:</label>
                <textarea id="description" name="description" required></textarea>
                <label for="availability">Availability:</label>
                <select id="availability" name="availability" required>
                    <option value="Available">Available</option>
                    <option value="Unavailable">Unavailable</option>
                </select>
                <label for="image">Image:</label>
                <input type="file" id="image" name="image">
                <div id="image-actions" style="display: flex; align-items: center; gap: 10px;">
    <img id="existing-image" src="" alt="Existing Image" style="display:none; width: 100px; margin-top: 10px;">
    <button type="button" class="btn change-image-btn" id="change-image-btn" onclick="enableFileInput()" style="display:none;">Change Image</button>
    <button type="button" class="btn cancel-change-btn" id="cancel-change-btn" onclick="disableFileInput()" style="display:none;">Cancel Change</button>
</div>
                <button type="button" class="btn confirm-btn" onclick="showAddSeedConfirmation()">Submit</button>
            </form>
        </div>
    </div>

     <!-- Add Seed Confirmation Modal -->
     <div id="add-seed-modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeAddSeedModal()">&times;</span>
            <p id="add-seed-message">Are you sure you want to add this seed?</p>
            <button id="add-seed-confirm-button" class="btn confirm-btn">Confirm</button>
            <button class="btn cancel-btn" onclick="closeAddSeedModal()">Cancel</button>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="confirmation-modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeConfirmationModal()">&times;</span>
            <p id="confirmation-message"></p>
            <button id="confirm-button" class="btn confirm-btn">Confirm</button>
            <button class="btn cancel-btn" onclick="closeConfirmationModal()">Cancel</button>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirmation-modal" class="modal">
        <div class="modal-content">
            <h2>Confirm Update</h2>
            <p>Are you sure you want to update your settings?</p>
            <button class="btn confirm-btn" onclick="confirmUpdateSettings()">Confirm</button>
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

    <!-- Add Seed Modal -->
    <div id="add-seed-modal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeAddSeedModal()">&times;</span>
        <h2>Add Seed</h2>
        <form id="add-seed-form" method="post" action="adminhome.php">
            <div class="form-group">
                <label for="seed_name">Seed Name:</label>
                <input type="text" id="seed_name" name="seed_name" required>
            </div>
            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description" required></textarea>
            </div>
            <div class="form-group">
                <label for="availability">Availability:</label>
                <select id="availability" name="availability" required>
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>
            </div>
            <button type="submit" class="btn add-seed-btn">Add Seed</button>
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
</script>

    <script src="scriptadmin.js">
        
    </script>
    <script src="scriptmessagesadmin.js"></script>
</body>
</html>