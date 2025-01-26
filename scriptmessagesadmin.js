document.addEventListener('DOMContentLoaded', function() {
    // Add event listeners for farmer list links
    document.querySelectorAll('.farmer-list a').forEach(link => {
        link.addEventListener('click', function(event) {
            event.preventDefault();
            const farmer = this.getAttribute('data-farmer');
            document.querySelector('.conversation-box').setAttribute('data-farmer', farmer);
            console.log(`Farmer selected: ${farmer}`); // Debug log
            fetchMessages(farmer);
            history.pushState(null, '', `adminhome.php?tab=messages&farmer=${encodeURIComponent(farmer)}`);
        });
    });

    // Add event listener for the refresh button
    const refreshButton = document.querySelector('.ref-btn');
    if (refreshButton) {
        refreshButton.addEventListener('click', function() {
            console.log('Refresh button clicked'); // Debug log
            refreshMessages();
        });
    }
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
            document.querySelector('.h3label').style.display = 'none';
            document.querySelector('.back-btn').style.display = 'inline-block';
            document.querySelector('.ref-btn').style.display = 'inline-block';
            document.querySelector('.conversation-box').style.display = 'block';
            document.getElementById('reply-form').style.display = 'block';
            document.getElementById('recipient').value = farmer;
            document.getElementById('conversation-farmer').textContent = farmer;
            // Scroll to the latest message
            messageList.scrollTop = messageList.scrollHeight;
        })
        .catch(error => console.error('Error fetching messages:', error));
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

function showFarmersList() {
    document.querySelector('.farmer-list').style.display = 'block';
    document.querySelector('.back-btn').style.display = 'none';
    document.querySelector('.ref-btn').style.display = 'none';
    document.querySelector('.conversation-box').style.display = 'none';
    document.getElementById('reply-form').style.display = 'none';
    document.querySelector('.h3label').style.display = 'block';
    document.querySelector('.chat-container').innerHTML = '';
    history.pushState(null, '', 'adminhome.php?tab=messages');
}

document.querySelectorAll('.chat-box').forEach(box => {
    box.addEventListener('click', function() {
        document.getElementById('recipient').value = this.querySelector('h4').textContent.replace('Conversation with ', '');
    });
});

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