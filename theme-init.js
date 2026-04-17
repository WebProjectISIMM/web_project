// Theme Init - Must run in <head> BEFORE body renders
// Applies dark/light class to <html> immediately to prevent flash
(function () {
    try {
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark-theme');
        }
    } catch (e) {}
})();
