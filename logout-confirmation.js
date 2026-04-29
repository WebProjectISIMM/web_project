/**
 * Logout Confirmation Dialog
 * Used across all pages to confirm logout
 */

function confirmLogout() {
    if (confirm('👋 Êtes-vous sûr de vouloir vous déconnecter ?')) {
        window.location.href = '/Web_Project/logout.php';
    }
}

// Replace all logout links with confirmation
document.addEventListener('DOMContentLoaded', function() {
    // Find all logout links
    const logoutLinks = document.querySelectorAll('a[href*="logout.php"]');
    
    logoutLinks.forEach(link => {
        link.onclick = function(e) {
            e.preventDefault();
            confirmLogout();
        };
        
        // Change cursor to pointer to show it's clickable
        link.style.cursor = 'pointer';
    });
    
    // Also check for logout buttons
    const logoutButtons = document.querySelectorAll('button[onclick*="logout"]');
    logoutButtons.forEach(button => {
        button.onclick = function(e) {
            e.preventDefault();
            confirmLogout();
        };
    });
});
