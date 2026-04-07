function togglePass(id) {
    const input = document.getElementById(id);
    input.type = input.type === "password" ? "text" : "password";
}

function handleSignup(event) {
    event.preventDefault();
    // Simulate signup and redirect to Client Profile
    window.location.href = '../profilClient/ProfilClient.html';
}
