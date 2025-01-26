document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.farmer-nav a, .side-panel a').forEach(link => {
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
    const tab = urlParams.get('tab') || 'dashboard'; // Default to 'dashboard' if no tab parameter is present
    document.querySelectorAll('.content-section').forEach(section => {
        section.style.display = 'none';
    });
    document.getElementById(tab).style.display = 'block';
    history.pushState(null, '', `?tab=${tab}`);
    localStorage.setItem('activeTab', tab);
    });

function toggleSidePanel() {
    const sidePanel = document.querySelector('.side-panel');
    if (sidePanel.classList.contains('visible')) {
        sidePanel.classList.remove('visible');
    } else {
        sidePanel.classList.add('visible');
    }
}
function hideSidePanel() {
    const sidePanel = document.querySelector('.side-panel');
    sidePanel.classList.remove('visible');
}

// Initialize FullCalendar
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar-container');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth'
    });
    calendar.render();
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