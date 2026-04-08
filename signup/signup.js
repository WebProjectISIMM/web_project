function togglePass(id) {
    const input = document.getElementById(id);
    input.type = input.type === "password" ? "text" : "password";
}

function handleSignup(event) {
    event.preventDefault();
    const role = document.getElementById('user-role').value;
    
    // Simulate signup success
    if (role === 'agent') {
        window.location.href = '../agent-dashboard/agent-dashboard.html';
    } else {
        window.location.href = '../profilClient/ProfilClient.html';
    }
}
