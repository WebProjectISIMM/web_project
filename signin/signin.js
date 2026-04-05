function switchForm() {
    const login = document.getElementById('login-form');
    const signup = document.getElementById('signup-form');
    if (login.style.display === 'none') {
        login.style.display = 'block';
        signup.style.display = 'none';
    } else {
        login.style.display = 'none';
        signup.style.display = 'block';
    }
}

function togglePass(id) {
    const input = document.getElementById(id);
    input.type = input.type === "password" ? "text" : "password";
}

function handleLogin(event) {
    event.preventDefault();
    // Simulate login and redirect to Client Profile
    window.location.href = '../profilClient/ProfilClient.html';
}

function handleSignup(event) {
    event.preventDefault();
    // Simulate signup and redirect to Client Profile
    window.location.href = '../profilClient/ProfilClient.html';
}