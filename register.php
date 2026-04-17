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
            // Success: Redirect to login or show success message
            echo "Inscription réussie ! <a href='signin/signin.html'>Cliquez ici pour vous connecter</a>";
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