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
hideSidePanel();
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

function editFarmer(id, username, password, gender) {
    document.getElementById('farmer-id').value = id;
    document.getElementById('username').value = username;
    document.getElementById('password').value = '*****'; // Show placeholder
    document.getElementById('gender').value = gender;
    document.querySelector('button[onclick="showAddConfirmation()"]').style.display = 'none';
    document.querySelector('button[onclick="showUpdateConfirmation()"]').style.display = 'block';
}
function showAddConfirmation() {
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    const gender = document.getElementById('gender').value;

    if (username && password && gender) {
        document.getElementById('add-confirmation-modal').style.display = 'block';
    } else {
        showErrorAlert('Please fill out all fields.');
    }
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
    const passwordField = document.getElementById('password');
    if (passwordField.value === '*****') {
        passwordField.disabled = true; // Disable the password field if it contains the placeholder
    } else {
        passwordField.disabled = false;
    }
    document.getElementById('manage-farmers-form').submit();
    passwordField.disabled = false; // Re-enable the password field after submission
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
function hideSidePanel() {
    const sidePanel = document.querySelector('.side-panel');
    sidePanel.classList.remove('visible');
}

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




document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.upload-image-form').forEach(form => {
        form.addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(this);
            fetch('upload_image.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Image uploaded successfully.', 'success');
                    refreshSeedsTable();
                } else {
                    showErrorAlert(data.message || 'Image upload failed.');
                }
            })
            .catch(error => {
                console.error('Error uploading image:', error);
                showErrorAlert('An error occurred while uploading the image.');
            });
        });
    });
});

function showUploadConfirmation(button) {
    const form = button.closest('form');
    const modal = document.getElementById('upload-seed-modal');
    const message = document.getElementById('upload-seed-message');
    const confirmButton = document.getElementById('upload-seed-confirm-button');

    message.textContent = 'Are you sure you want to upload this image?';
    confirmButton.onclick = function() {
        const formData = new FormData(form);
        fetch('upload_image.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Image uploaded successfully.', 'success');
                refreshSeedsTable();
            } else {
                showErrorAlert(data.message || 'Image upload failed.');
            }
        })
        .catch(error => {
            console.error('Error uploading image:', error);
            showErrorAlert('An error occurred while uploading the image.');
        });
        closeUploadSeedModal();
    };

    modal.style.display = 'block';
}

function closeUploadSeedModal() {
    const modal = document.getElementById('upload-seed-modal');
    modal.style.display = 'none';
}

function showDeleteConfirmation(seedId) {
    const modal = document.getElementById('confirmation-modal');
    const message = document.getElementById('confirmation-message');
    const confirmButton = document.getElementById('confirm-button');

    message.textContent = 'Are you sure you want to delete this image?';
    confirmButton.onclick = function() {
        deleteImage(seedId);
        closeConfirmationModal();
    };

    modal.style.display = 'block';
}

function closeConfirmationModal() {
    const modal = document.getElementById('confirmation-modal');
    modal.style.display = 'none';
}

document.getElementById('add-seed-form').addEventListener('submit', function(event) {
    event.preventDefault();
    const formData = new FormData(this);
    formData.append('form_type', 'add_seed'); // Add form type to distinguish the request
    fetch('adminhome.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        console.log('Response data:', data); // Debugging step
        closeAddSeedModal();
        showAlert('Seed added successfully.', 'success');
        refreshSeedsTable();
    })
    .catch(error => {
        console.error('Error adding seed:', error);
        showAlert('An error occurred while adding the seed.', 'error');
    });
});

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
            showAlert('Image uploaded successfully.', 'success');
            refreshSeedsTable();
        } else {
            showErrorAlert(data.message || 'Image upload failed.');
        }
    })
    .catch(error => {
        console.error('Error uploading image:', error);
        showErrorAlert('An error occurred while uploading the image.');
    });
}

function updateImage(seedId) {
    const input = document.createElement('input');
    input.type = 'file';
    input.onchange = function() {
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
                showAlert('Image updated successfully.', 'success');
                refreshSeedsTable();
            } else {
                showErrorAlert(data.message || 'Image update failed.');
            }
        })
        .catch(error => {
            console.error('Error updating image:', error);
            showErrorAlert('An error occurred while updating the image.');
        });
    };
    input.click();
}

function deleteImage(seedId) {
    const formData = new FormData();
    formData.append('seed_id', seedId);

    fetch('delete_image.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Image deleted successfully.', 'success');
            refreshSeedsTable();
        } else {
            showErrorAlert(data.message || 'Image deletion failed.');
        }
    })
    .catch(error => {
        console.error('Error deleting image:', error);
        showErrorAlert('An error occurred while deleting the image.');
    });
}

function showDeleteSeedConfirmation(seedId) {
    const modal = document.getElementById('confirmation-modal');
    const message = document.getElementById('confirmation-message');
    const confirmButton = document.getElementById('confirm-button');

    message.textContent = 'Are you sure you want to delete this seed?';
    confirmButton.onclick = function() {
        deleteSeed(seedId);
        closeConfirmationModal();
    };

    modal.style.display = 'block';
}

function showAddSeedConfirmation() {
    const modal = document.getElementById('add-seed-modal');
    const confirmButton = document.getElementById('add-seed-confirm-button');

    confirmButton.onclick = function() {
        addSeed();
        closeAddSeedFormModal();
        closeAddSeedModal();
    };

    modal.style.display = 'block';
}

function closeAddSeedModal() {
    const modal = document.getElementById('add-seed-modal');
    modal.style.display = 'none';
}

function addSeed() {
    const form = document.getElementById('add-seed-form');
    const formData = new FormData(form);

    // Conditionally append the image field only if the file input is enabled
    if (!document.getElementById('image').disabled) {
        const imageInput = document.getElementById('image');
        if (imageInput.files.length > 0) {
            formData.append('image', imageInput.files[0]);
        }
    }

    fetch('add_seed.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            refreshSeedsTable();
        } else {
            showErrorAlert(data.message || 'Seed addition failed.');
        }
    })
    .catch(error => {
        console.error('Error adding seed:', error);
        showErrorAlert('An error occurred while adding the seed.');
    });
}

function showAddSeedForm() {
    const modal = document.getElementById('add-seed-form-modal');
    const form = document.getElementById('add-seed-form');
    form.reset(); // Reset the form fields
    document.getElementById('seed_id').value = ''; // Clear the seed ID
    modal.style.display = 'block';
}

function showEditSeedForm(seedId) {
    const modal = document.getElementById('add-seed-form-modal');
    const form = document.getElementById('add-seed-form');
    form.reset(); // Reset the form fields

    // Fetch the seed details and populate the form
    fetch(`get_seed.php?id=${seedId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('seed_id').value = data.seed.id;
                document.getElementById('seed_name').value = data.seed.seed_name;
                document.getElementById('description').value = data.seed.description;
                document.getElementById('availability').value = data.seed.availability;
                if (data.seed.image) {
                    document.getElementById('existing-image').src = `uploads/${data.seed.image}`;
                    document.getElementById('existing-image').style.display = 'block';
                    document.getElementById('image').disabled = true; // Disable the file input
                } else {
                    document.getElementById('existing-image').style.display = 'none';
                    document.getElementById('image').disabled = false; // Enable the file input
                }
            } else {
                showErrorAlert(data.message || 'Failed to fetch seed details.');
            }
        })
        .catch(error => {
            console.error('Error fetching seed details:', error);
            showErrorAlert('An error occurred while fetching the seed details.');
        });

    modal.style.display = 'block';
}

function enableFileInput() {
    document.getElementById('image').disabled = false;
}

function closeAddSeedFormModal() {
    const modal = document.getElementById('add-seed-form-modal');
    modal.style.display = 'none';
}
function deleteSeed(seedId) {
    const formData = new FormData();
    formData.append('seed_id', seedId);

    fetch('delete_seed.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Seed deleted successfully.', 'success');
            refreshSeedsTable();
        } else {
            showErrorAlert(data.message || 'Seed deletion failed.');
        }
    })
    .catch(error => {
        console.error('Error deleting seed:', error);
        showErrorAlert('An error occurred while deleting the seed.');
    });
}


function refreshSeedsTable() {
    fetch('adminhome.php?tab=items-list')
    .then(response => response.text())
    .then(data => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(data, 'text/html');
        const newTableBody = doc.querySelector('.seeds-table tbody');
        document.querySelector('.seeds-table tbody').innerHTML = newTableBody.innerHTML;
        // Reattach event listeners to the new forms
        document.querySelectorAll('.upload-image-form').forEach(form => {
            form.addEventListener('submit', function(event) {
                event.preventDefault();
                const formData = new FormData(this);
                fetch('upload_image.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('Image uploaded successfully.', 'success');
                        refreshSeedsTable();
                    } else {
                        showErrorAlert(data.message || 'Image upload failed.');
                    }
                })
                .catch(error => {
                    console.error('Error uploading image:', error);
                    showErrorAlert('An error occurred while uploading the image.');
                });
            });
        });
    })
    .catch(error => {
        console.error('Error refreshing seeds table:', error);
    });
}

function showAlert(message, type) {
    const alertBox = document.getElementById('alert');
    alertBox.textContent = message;
    alertBox.className = `alert ${type}`;
    alertBox.style.display = 'block';
    setTimeout(() => {
        alertBox.style.display = 'none';
    }, 3000);
}

function showErrorAlert(message) {
    const alertBox = document.getElementById('alert');
    alertBox.textContent = message;
    alertBox.className = 'alert error'; // Assuming 'error' class sets the background color to red
    alertBox.style.display = 'block';
    setTimeout(() => {
        alertBox.style.display = 'none';
    }, 3000);
}