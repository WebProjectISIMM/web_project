<?php
session_start();
include "test.php"; // Database connection

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Veuillez remplir tous les champs.']);
        exit;
    }

    $stmt = $conn->prepare("SELECT id, name, email, password, role, establishment, sector FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['establishment'] = $user['establishment'];
            $_SESSION['sector'] = $user['sector'];
            $_SESSION['view_mode'] = ($user['role'] === 'client') ? 'client' : 'admin';

            $redirect = '';
            switch ($user['role']) {
                case 'main_admin':
                    $redirect = 'admin/main-admin-dashboard.php';
                    break;
                case 'admin':
                    $redirect = 'agent-dashboard/agent-dashboard.php';
                    break;
                default:
                    $redirect = 'profilClient/ProfilClient.php';
            }


            echo json_encode(['success' => true, 'redirect' => $redirect]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Mot de passe incorrect.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Email non trouvé.']);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Méthode invalide.']);
}

$conn->close();
?>
