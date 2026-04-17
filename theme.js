function toggleTheme() {
    document.body.classList.toggle('dark-theme');
    let isDark = document.body.classList.contains('dark-theme');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
    
    // Update icons if they exist
    let themeIcons = document.querySelectorAll('.theme-toggle-icon');
    themeIcons.forEach(icon => {
        if (isDark) {
            icon.classList.remove('fa-moon');
            icon.classList.add('fa-sun');
        } else {
            icon.classList.remove('fa-sun');
            icon.classList.add('fa-moon');
        }
    });
}

// Apply theme on load immediately to avoid flicker if possible, otherwise DOMContentLoaded
(function() {
    let savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
        document.body.classList.add('dark-theme');
    }
})();

document.addEventListener('DOMContentLoaded', () => {
    let savedTheme = localStorage.getItem('theme');
    let themeIcons = document.querySelectorAll('.theme-toggle-icon');
    themeIcons.forEach(icon => {
        if (savedTheme === 'dark') {
            icon.classList.remove('fa-moon');
            icon.classList.add('fa-sun');
        } else {
            icon.classList.remove('fa-sun');
            icon.classList.add('fa-moon');
        }
    });
});
