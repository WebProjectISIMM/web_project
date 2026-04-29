<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "test.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if the basic required fields are present
    if (isset($_POST['username'], $_POST['email'], $_POST['password'])) {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        
        // Everyone starts as a client
        $role = 'client';

        // Use Prepared Statements for security
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $email, $password, $role);

        if ($stmt->execute()) {
            // Success: Show message and redirect after 2 seconds
            echo "
            <!DOCTYPE html>
            <html lang='fr'>
            <head>
                <meta charset='UTF-8'>
                <title>Inscription Réussie</title>
                <style>
                    body {
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        height: 100vh;
                        font-family: Arial, sans-serif;
                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                        margin: 0;
                    }
                    .success-box {
                        background: white;
                        padding: 40px;
                        border-radius: 10px;
                        text-align: center;
                        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
                    }
                    .success-box h1 {
                        color: #4CAF50;
                        margin: 0 0 10px 0;
                    }
                    .success-box p {
                        color: #666;
                        margin: 10px 0;
                    }
                    .countdown {
                        font-size: 14px;
                        color: #999;
                        margin-top: 20px;
                    }
                </style>
            </head>
            <body>
                <div class='success-box'>
                    <h1>✅ Inscription Réussie !</h1>
                    <p>Votre compte a été créé avec succès.</p>
                    <p>Redirection vers la page de connexion...</p>
                    <div class='countdown'>Redirection dans <span id='countdown'>2</span> secondes</div>
                </div>
                <script>
                    let seconds = 2;
                    setInterval(() => {
                        seconds--;
                        document.getElementById('countdown').innerText = seconds;
                        if (seconds <= 0) {
                            window.location.href = 'signin/signin.html';
                        }
                    }, 1000);
                </script>
            </body>
            </html>
            ";
        } else {
            // Check for duplicate email error
            if ($conn->errno == 1062) {
                echo "Erreur : Cet email est déjà utilisé.";
            } else {
                echo "Erreur : " . $stmt->error;
            }
        }
        $stmt->close();
    } else {
        echo "Veuillez remplir tous les champs obligatoires.";
    }
} else {
    echo "Méthode de requête invalide.";
}


$conn->close();
?>