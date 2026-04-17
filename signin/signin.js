
function togglePass(id) {
    const input = document.getElementById(id);
    input.type = input.type === "password" ? "text" : "password";
}

function handleLogin(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);

    fetch('../login.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '../' + data.redirect;
        } else {
            alert(data.message || 'Erreur de connexion');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Une erreur est survenue lors de la connexion.');
    });
}

