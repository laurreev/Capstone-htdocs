 // Add event listener for the refresh button
    const refreshButton = document.querySelector('.ref-btn');
    if (refreshButton) {
        refreshButton.addEventListener('click', function() {
            console.log('Refresh button clicked'); // Debug log
            refreshMessages();
        });
    }



function refreshMessages() {
    const conversationBox = document.querySelector('.conversation-box');
    const farmer = conversationBox.getAttribute('data-farmer');
    console.log(`Data-farmer attribute: ${farmer}`); // Debug log
    if (farmer) {
        console.log(`Refreshing messages for farmer: ${farmer}`); // Debug log
        fetch(`send_message.php?farmer=${farmer}`)
            .then(response => response.json())
            .then(data => {
                console.log('Data received:', data); // Debug log
                const messageList = document.querySelector('.chat-container');
                messageList.innerHTML = '';
                data.forEach(message => {
                    const messageItem = document.createElement('div');
                    messageItem.classList.add('chat-message');
                    messageItem.classList.add(message.username === 'admin' ? 'admin' : 'farmer');
                    messageItem.innerHTML = `<strong>${message.username}:</strong> ${message.message} <em>(${message.created_at})</em>`;
                    messageList.appendChild(messageItem);
                });
                // Scroll to the latest message
                messageList.scrollTop = messageList.scrollHeight;
            })
            .catch(error => console.error('Error refreshing messages:', error));
    } else {
        console.log('No farmer selected'); // Debug log
    }
}



document.querySelectorAll('.chat-box').forEach(box => {
    box.addEventListener('click', function() {
        document.getElementById('recipient').value = this.querySelector('h4').textContent.replace('Conversation with ', '');
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const selectedFarmer = urlParams.get('farmer');
    const tab = urlParams.get('tab');

    if (selectedFarmer) {
        openConversation(selectedFarmer);
    } else {
        showFarmersList();
    }

    if (tab) {
        document.querySelectorAll('.content-section').forEach(section => {
            section.style.display = 'none';
        });
        document.getElementById(tab).style.display = 'block';
        if (tab === 'messages' && selectedFarmer) {
            openConversation(selectedFarmer); // Use openConversation instead of fetchMessages directly
        }
    } else {
        document.querySelectorAll('.content-section').forEach(section => {
            section.style.display = 'none';
        });
        document.getElementById('activeTab').style.display = 'block';
    }

    // Fetch and display all farmers in the "Find Farmers" box
    fetchAllFarmers();
});

function fetchAllFarmers() {
    console.log('Fetching all farmers');
    fetch('get_all_farmers.php') // Fetch the list of farmers from the new PHP file
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            console.log('Fetched farmers:', data); // Debugging: Log the fetched farmers
            const allFarmerList = document.getElementById('all-farmer-list');
            allFarmerList.innerHTML = ''; // Clear the list before appending new farmers
            data.forEach(farmer => {
                const farmerItem = document.createElement('li');
                farmerItem.textContent = farmer;
                farmerItem.addEventListener('click', () => openConversation(farmer));
                allFarmerList.appendChild(farmerItem);
            });
        })
        .catch(error => console.error('Error fetching all farmers:', error));
}

function openConversation(farmer) {
    const farmerName = farmer;
    console.log(`Opening conversation with farmer: ${farmerName}`);
    document.getElementById('conversation-farmer').textContent = farmerName;
    document.getElementById('recipient').value = farmerName;

    // Set the data-farmer attribute on the conversation box
    document.querySelector('.conversation-box').setAttribute('data-farmer', farmerName);

    // Hide the find farmers box and show the conversation box
    document.querySelector('.find-farmers-box').style.display = 'none';
    document.querySelector('.conversation-box').style.display = 'block';

    // Hide the farmers with messages list and header, and show the back and refresh buttons
    document.querySelector('.farmer-list').style.display = 'none';
    document.querySelector('.h3label').style.display = 'none';
    document.querySelector('.back-btn').style.display = 'inline-block';
    document.querySelector('.ref-btn').style.display = 'inline-block';

    // Revert the style of the message-box to its original width
    document.querySelector('.message-box').style.width = '100%';

    // Show the reply form
    document.getElementById('reply-form').style.display = 'block';

    // Store the selected farmer in local storage
    localStorage.setItem('selectedFarmer', farmerName);

    // Update the URL with the farmer parameter
    history.pushState(null, '', `?tab=messages&farmer=${encodeURIComponent(farmerName)}`);

    // Fetch and display the conversation using existing PHP code
    fetchMessages(farmerName);
}

function fetchMessages(farmerName) {
    // Use the existing PHP code to fetch messages
    const messagesContainer = document.querySelector('.chat-container');
    messagesContainer.innerHTML = ''; // Clear the container before appending new messages

    console.log(`Fetching messages for farmer: ${farmerName}`);
    fetch(`adminhome.php?farmer=${encodeURIComponent(farmerName)}`)
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const messages = doc.querySelectorAll('.chat-message');
            console.log(`Found ${messages.length} messages`);
            if (messages.length > 0) {
                messages.forEach(message => {
                    messagesContainer.appendChild(message.cloneNode(true)); // Clone the message node to avoid duplication
                });
            } else {
                messagesContainer.innerHTML = '<p>No messages from this farmer.</p>';
            }
        })
        .catch(error => console.error('Error fetching messages:', error));
}

function showFarmersList() {
    document.querySelector('.farmer-list').style.display = 'block';
    document.querySelector('.back-btn').style.display = 'none';
    document.querySelector('.ref-btn').style.display = 'none';
    document.querySelector('.find-farmers-box').style.display = 'block';
    document.querySelector('.conversation-box').style.display = 'none';
    document.querySelector('.message-box').style.width = '70%';
    document.querySelector('.chat-container').innerHTML = '';
    document.getElementById('reply-form').style.display = 'none';
    document.querySelector('.h3label').style.display = 'block';
    document.querySelector('.chat-container').innerHTML = '';
    history.pushState(null, '', 'adminhome.php?tab=messages');
    history.pushState(null, '', '?tab=messages');
}