function togglePass(id) {
    const input = document.getElementById(id);
    input.type = input.type === "password" ? "text" : "password";
}

// Removed handleSignup simulation logic to allow standard PHP form submission
