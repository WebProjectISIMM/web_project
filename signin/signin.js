
function togglePass(id) {
    const input = document.getElementById(id);
    input.type = input.type === "password" ? "text" : "password";
}

function handleLogin(event) {
    event.preventDefault();
    const role = document.getElementById('user-role').value;
    
    // Simulate login and redirect
    if (role === 'agent') {
        window.location.href = '../agent-dashboard/agent-dashboard.html';
    } else {
        window.location.href = '../profilClient/ProfilClient.html';
    }
}
