<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../signin/signin.html");
    exit;
}
$is_admin = ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'main_admin');
$admin_link = ($_SESSION['user_role'] === 'main_admin') ? '../admin/main-admin-dashboard.php' : '../agent-dashboard/agent-dashboard.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>SmartQueue - Mes files</title>
</head>

<body>

    <nav class="navbar">
        <div class="logo">
            <i class="fas fa-layer-group"></i> SmartQueue
        </div>
        <div class="nav-links">
            <?php if ($is_admin): ?>
            <button class="btn-signin" onclick="window.location.href='<?php echo $admin_link; ?>'" style="border:none; cursor:pointer; font-family: inherit; font-size: inherit; background: var(--primary-color); color: #1A1A1A; padding: 8px 15px; border-radius: 8px; font-weight: 600; margin-right: 15px;">
                <i class="fas fa-user-shield"></i> Administration
            </button>
            <?php endif; ?>
            <i class="fas fa-moon theme-toggle-icon" style="cursor:pointer; font-size: 24px; color: #B2A14E;" onclick="toggleTheme()"></i>
            <a href="../logout.php" class="btn-signin" style="border:none; cursor:pointer; font-family: inherit; font-size: inherit; text-decoration: none; padding: 8px 15px;">Déconnexion</a>
        </div>
    </nav>


    <div class="container-wide">
        <div class="header" style="margin-top: 50px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-end;">
                <div>
                    <h1>Bonjour, <?php echo explode(' ', $_SESSION['user_name'])[0]; ?> 👋</h1>
                    <p class="subtitle">Heureux de vous revoir.</p>
                </div>
            </div>
            <h2 style="margin-top: 30px;">Mes files en cours</h2>
            <p class="subtitle" id="ticket-count-text">Vous avez <b>0 ticket</b> actif en ce moment</p>
        </div>


        <div id="ticket-container" class="ticket-container">
            <!-- Tickets will be injected here via JS -->
            <p class="empty-state">Aucun ticket actif. Veuillez prendre un nouveau ticket.</p>
        </div>

        <button class="btn-primary" style="margin-top: 30px; max-width: 300px;" onclick="window.location.href='../services/services.php'">+ Nouveau Ticket</button>

    </div>

    <footer>
        <p>2026 SmartQueue Tunisia - Système de gestion de file intelligent</p>
        <nav class="footer-links">
            <a href="#">Aide</a>
            <a href="#">Confidentialité</a>
            <a href="#">Contact</a>
        </nav>
    </footer>

    <script>
        const USER_ID = "<?php echo $_SESSION['user_id']; ?>";
    </script>
    <script src="ProfilClient.js"></script>

    <script src="../theme.js"></script>
</body>
</html>