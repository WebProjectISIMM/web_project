<?php
session_start();
$logged_in = isset($_SESSION['user_id']);
$dashboard_link = '#';
if ($logged_in) {
    if ($_SESSION['user_role'] === 'main_admin')
        $dashboard_link = 'admin/main-admin-dashboard.php';
    else if ($_SESSION['user_role'] === 'admin')
        $dashboard_link = 'agent-dashboard/agent-dashboard.php';
    else
        $dashboard_link = 'profilClient/ProfilClient.php';
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartQueue - Accueil</title>
    <meta name="description"
        content="SmartQueue — La solution intelligente pour gérer les files d'attente. Rejoignez la file, gagnez du temps.">
    <link rel="stylesheet" href="index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

    <!-- Header / Navbar -->
    <nav class="navbar" id="navbar">
        <div class="logo" onclick="window.location.href='index.php'" style="cursor:pointer;">
            <i class="fas fa-layer-group"></i> SmartQueue
        </div>
        <div class="nav-links">
            <i class="fas fa-moon theme-toggle-icon" style="cursor:pointer; font-size: 24px; color: #B2A14E;"
                onclick="toggleTheme()"></i>
            <?php if ($logged_in): ?>
                <a href="<?php echo $dashboard_link; ?>" class="btn-signin"
                    style="background: var(--primary-color); color: #1A1A1A;">Tableau de Bord</a>
                <a href="logout.php" class="btn-signin"
                    style="border: 1px solid var(--border-color); margin-left:10px;">Déconnexion</a>
            <?php else: ?>
                <a href="signin/signin.html" class="btn-signin">Connexion</a>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="hero-content">
            <h1>Bienvenue sur SmartQueue</h1>
            <p class="hero-subtitle">La solution intelligente pour gérer l'attente. Rejoignez la file, gagnez du temps
                et profitez de vos services en toute simplicité.</p>
            <div class="hero-buttons">
                <?php if ($logged_in): ?>
                    <a href="<?php echo $dashboard_link; ?>" class="btn-primary-large"
                        style="text-decoration: none;">Accéder à mon espace</a>
                <?php else: ?>
                    <a href="signup/signup.html" class="btn-primary-large" style="text-decoration: none;">Commencer
                        maintenant</a>
                <?php endif; ?>
                <a href="#features" class="btn-secondary-large">En savoir plus</a>
            </div>
        </div>
        <div class="hero-image">
            <i class="fas fa-clock fa-10x hero-icon float-animation"></i>
        </div>
    </div>

    <!-- Features Section -->
    <div class="features-section" id="features">
        <h2>Pourquoi choisir SmartQueue ?</h2>
        <div class="features-grid">
            <div class="feature-card reveal">
                <div class="feature-icon"><i class="fas fa-stopwatch"></i></div>
                <h3>Gagnez du temps</h3>
                <p>Fini les longues files d'attente. Suivez votre position en temps réel de façon sereine.</p>
            </div>
            <div class="feature-card reveal" style="transition-delay: 0.1s;">
                <div class="feature-icon"><i class="fas fa-mobile-alt"></i></div>
                <h3>100% Mobile</h3>
                <p>Accédez à tous vos services depuis votre smartphone, où que vous soyez et à tout moment.</p>
            </div>
            <div class="feature-card reveal" style="transition-delay: 0.2s;">
                <div class="feature-icon"><i class="fas fa-shield-alt"></i></div>
                <h3>Sécurisé & Fiable</h3>
                <p>Vos données sont protégées. Profitez d'une gestion transparente et entièrement sécurisée.</p>
            </div>
        </div>

        <div class="two-tracks reveal" style="transition-delay:0.3s; margin-top: 40px; text-align: center;">
            <div
                style="background: var(--glass-bg); padding: 40px; border-radius: 30px; border: 1px solid var(--border-color);">
                <i class="fas fa-rocket fa-3x" style="color: #B2A14E; margin-bottom: 20px;"></i>
                <h3>Prêt à simplifier votre attente ?</h3>
                <p style="color: var(--text-muted); margin-bottom: 30px;">Inscrivez-vous en quelques secondes et
                    commencez à utiliser SmartQueue dès aujourd'hui.</p>
                <?php if (!$logged_in): ?>
                    <a href="signup/signup.html" class="btn-primary-large"
                        style="text-decoration: none; display: inline-block;">Créer mon compte gratuit</a>
                <?php else: ?>
                    <a href="<?php echo $dashboard_link; ?>" class="btn-primary-large"
                        style="text-decoration: none; display: inline-block;">Mon Tableau de Bord</a>
                <?php endif; ?>
            </div>
        </div>
    </div>


    <footer>
        <p>&copy; 2026 SmartQueue. Tous droits réservés.</p>
    </footer>

    <script src="theme.js"></script>
    <script>
        // Scroll reveal effect and Navbar style change on scroll
        window.addEventListener('scroll', () => {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }

            const reveals = document.querySelectorAll('.reveal');
            for (var i = 0; i < reveals.length; i++) {
                var windowheight = window.innerHeight;
                var revealtop = reveals[i].getBoundingClientRect().top;
                var revealpoint = 100;
                if (revealtop < windowheight - revealpoint) {
                    reveals[i].classList.add('active');
                }
            }
        });

        window.dispatchEvent(new Event('scroll'));
    </script>
</body>

</html>