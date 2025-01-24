document.querySelectorAll('.admin-nav a, .side-panel a').forEach(link => {
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

document.querySelectorAll('.farmer-list a').forEach(link => {
link.addEventListener('click', function(event) {
event.preventDefault();
const farmer = this.getAttribute('data-farmer');
fetchMessages(farmer);
history.pushState(null, '', `adminhome.php?tab=messages&farmer=${encodeURIComponent(farmer)}`);
});
});

function fetchMessages(farmer) {
fetch(`send_message.php?farmer=${farmer}`)
.then(response => response.json())
.then(data => {
    const messageList = document.querySelector('.chat-container');
    messageList.innerHTML = '';
    data.forEach(message => {
        const messageItem = document.createElement('div');
        messageItem.classList.add('chat-message');
        messageItem.classList.add(message.username === 'admin' ? 'admin' : 'farmer');
        messageItem.innerHTML = `<strong>${message.username}:</strong> ${message.message} <em>(${message.created_at})</em>`;
        messageList.appendChild(messageItem);
    });
    document.querySelector('.farmer-list').style.display = 'none';
    document.querySelector('.back-btn').style.display = 'block';
    document.querySelector('.conversation-box').style.display = 'block';
    document.getElementById('reply-form').style.display = 'block';
    document.getElementById('recipient').value = farmer;
    document.getElementById('conversation-farmer').textContent = farmer;
    // Scroll to the latest message
    messageList.scrollTop = messageList.scrollHeight;
})
.catch(error => console.error('Error fetching messages:', error));
}

function showFarmersList() {
document.querySelector('.farmer-list').style.display = 'block';
document.querySelector('.back-btn').style.display = 'none';
document.querySelector('.conversation-box').style.display = 'none';
document.getElementById('reply-form').style.display = 'none';
document.querySelector('.chat-container').innerHTML = '';
history.pushState(null, '', 'adminhome.php?tab=messages');
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

// Handle message tab redirection
const farmer = urlParams.get('farmer');
if (tab) {
document.querySelectorAll('.content-section').forEach(section => {
section.style.display = 'none';
});
document.getElementById(tab).style.display = 'block';
if (tab === 'messages' && farmer) {
fetchMessages(farmer);
}
} else {
document.querySelectorAll('.content-section').forEach(section => {
section.style.display = 'none';
});
document.getElementById(activeTab).style.display = 'block';
}

function showConfirmation() {
    document.getElementById('confirmation-modal').style.display = 'block';
}

function closeConfirmation() {
    document.getElementById('confirmation-modal').style.display = 'none';
}

function confirmUpdateSettings() {
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

function editFarmer(id, username, password, gender) {
document.getElementById('farmer-id').value = id;
document.getElementById('username').value = username;
document.getElementById('password').value = password;
document.getElementById('gender').value = gender;
document.querySelector('button[onclick="showAddConfirmation()"]').style.display = 'none';
document.querySelector('button[onclick="showUpdateConfirmation()"]').style.display = 'block';
}
function showAddConfirmation() {
document.getElementById('add-confirmation-modal').style.display = 'block';
}

function closeAddConfirmation() {
document.getElementById('add-confirmation-modal').style.display = 'none';
}

function confirmAdd() {
document.getElementById('manage-farmers-form').submit();
}

function showUpdateConfirmation() {
document.getElementById('update-confirmation-modal').style.display = 'block';
}

function closeUpdateConfirmation() {
document.getElementById('update-confirmation-modal').style.display = 'none';
}

function confirmUpdate() {
document.getElementById('manage-farmers-form').submit();
}

function showDeleteConfirmation(id) {
document.getElementById('delete-farmer-id').value = id;
document.getElementById('delete-confirmation-modal').style.display = 'block';
}

function closeDeleteConfirmation() {
document.getElementById('delete-confirmation-modal').style.display = 'none';
}

function showAddSeedModal() {
    document.getElementById('add-seed-modal').style.display = 'block';
}

function closeAddSeedModal() {
    document.getElementById('add-seed-modal').style.display = 'none';
}

function toggleSidePanel() {
const sidePanel = document.querySelector('.side-panel');
if (sidePanel.classList.contains('visible')) {
sidePanel.classList.remove('visible');
} else {
sidePanel.classList.add('visible');
}
};
function toggleActionButtons(seedId) {
    const actionButtons = document.getElementById(`action-buttons-${seedId}`);
    if (actionButtons.style.display === 'none') {
        actionButtons.style.display = 'block';
    } else {
        actionButtons.style.display = 'none';
    }
}

function uploadImage(seedId, input) {
    const formData = new FormData();
    formData.append('seed_id', seedId);
    formData.append('image', input.files[0]);

    fetch('upload_image.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Image uploaded successfully');
        } else {
            alert('Image upload failed');
        }
    })
    .catch(error => {
        console.error('Error uploading image:', error);
    });
}

function editSeed(seedId) {
    const newAvailability = prompt('Enter new availability (Active/Inactive):');
    if (newAvailability !== null && (newAvailability.toLowerCase() === 'active' || newAvailability.toLowerCase() === 'inactive')) {
        fetch('edit_seed.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ seed_id: seedId, availability: newAvailability })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Seed availability updated successfully');
                location.reload(); // Reload the page to reflect changes
            } else {
                alert('Failed to update seed availability');
            }
        })
        .catch(error => {
            console.error('Error updating seed availability:', error);
        });
    } else {
        alert('Invalid availability. Please enter "Active" or "Inactive".');
    }
}

function deleteSeed(seedId) {
    if (confirm('Are you sure you want to delete this seed?')) {
        fetch('delete_seed.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ seed_id: seedId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Seed deleted successfully');
                location.reload(); // Reload the page to reflect changes
            } else {
                alert('Failed to delete seed');
            }
        })
        .catch(error => {
            console.error('Error deleting seed:', error);
        });
    }
}


document.querySelectorAll('.upload-image-form').forEach(form => {
    form.addEventListener('submit', function(event) {
        event.preventDefault();
        console.log('Form submission intercepted');
        const formData = new FormData(this);
        fetch('upload_image.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Response received');
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            showAlert(data.message, data.success ? 'success' : 'error');
        })
        .catch(error => {
            console.error('Error uploading image:', error);
            showAlert('An error occurred while uploading the image.', 'error');
        });
    });
});

function showAlert(message, type) {
    const alertBox = document.getElementById('alert');
    alertBox.textContent = message;
    alertBox.className = `alert ${type}`;
    alertBox.style.display = 'block';
    setTimeout(() => {
        alertBox.style.display = 'none';
    }, 3000);
}
