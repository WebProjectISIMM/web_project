<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'main_admin') {
    header("Location: ../signin/signin.html");
    exit;
}
include "../test.php";

$message = "";

// Handle Admin Creation (New User)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_admin'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $est = $_POST['establishment'];
    $sec = $_POST['sector'];
    $pass = password_hash('admin123', PASSWORD_DEFAULT); // Default password
    
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, establishment, sector) VALUES (?, ?, ?, 'admin', ?, ?)");
    $stmt->bind_param("sssss", $name, $email, $pass, $est, $sec);
    if ($stmt->execute()) {
        $message = "Nouvel administrateur créé avec succès ! (Pass: admin123)";
    } else {
        $message = "Erreur : L'email existe déjà.";
    }
    $stmt->close();
}

// Handle Admin Promotion (Existing User)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['promote_user_id'])) {
    $uid = $_POST['promote_user_id'];
    $est = $_POST['establishment'];
    $sec = $_POST['sector'];
    
    $stmt = $conn->prepare("UPDATE users SET role='admin', establishment=?, sector=? WHERE id=?");
    $stmt->bind_param("ssi", $est, $sec, $uid);
    $stmt->execute();
    $stmt->close();
    $message = "Utilisateur promu admin.";
}

// Handle Revocation
if (isset($_GET['revoke'])) {
    $uid = $_GET['revoke'];
    $stmt = $conn->prepare("UPDATE users SET role='client', establishment=NULL, sector=NULL WHERE id=?");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $stmt->close();
    $message = "Accès admin révoqué.";
}

// Fetch all clients
$result = $conn->query("SELECT id, name, email FROM users WHERE role='client'");
$clients = $result->fetch_all(MYSQLI_ASSOC);

// Fetch all admins
$result = $conn->query("SELECT id, name, email, establishment, sector FROM users WHERE role='admin'");
$admins = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartQueue - Administration Directeur</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../agent-dashboard/agent-dashboard.css">
    <style>
        .admin-card { background: var(--glass-bg); padding: 25px; border-radius: 20px; border: 1px solid var(--border-color); margin-top: 20px; }
        .user-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .user-table th { text-align: left; padding: 15px; border-bottom: 1px solid var(--border-color); color: var(--text-muted); }
        .user-table td { padding: 15px; border-bottom: 1px solid var(--border-color); }
        .btn-promote { background: var(--primary-color); color: #1A1A1A; border: none; padding: 8px 15px; border-radius: 8px; font-weight: 600; cursor: pointer; }
        .btn-revoke { background: #FF5252; color: #fff; border: none; padding: 8px 15px; border-radius: 8px; font-weight: 600; cursor: pointer; text-decoration: none; font-size: 13px; }
        .btn-add { background: #4CAF50; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; }
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); backdrop-filter: blur(5px); }
        .modal-content { background: var(--bg-main); margin: 5% auto; padding: 30px; border-radius: 20px; width: 450px; border: 1px solid var(--border-color); }
        .input-group { margin-bottom: 15px; }
        .input-group label { display: block; margin-bottom: 8px; color: var(--text-muted); }
        .input-group select, .input-group input { width: 100%; padding: 12px; border-radius: 10px; border: 1px solid var(--border-color); background: var(--input-bg); color: var(--text-main); }
        .alert { padding: 15px; border-radius: 10px; margin-bottom: 20px; background: rgba(178,161,78,0.2); border: 1px solid var(--primary-color); color: var(--primary-color); }
    </style>
</head>
<body class="dark-theme">
    <aside class="sidebar">
        <a href="../index.php" class="logo">
            <i class="fas fa-layer-group"></i> <span>SmartQueue</span>
        </a>
        <nav class="nav-links">
            <div class="nav-item">
                <a href="main-admin-dashboard.php" class="nav-link active">
                    <i class="fas fa-users-cog"></i> <span>Gestion Admins</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="../profilClient/ProfilClient.php" class="nav-link">
                    <i class="fas fa-user"></i> <span>Vue Client</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="../logout.php" class="nav-link" style="color: #FF5252;">
                    <i class="fas fa-sign-out-alt"></i> <span>Déconnexion</span>
                </a>
            </div>
        </nav>
        <div class="sidebar-footer">
            <div class="user-profile">
                <div class="avatar"><?php echo substr($_SESSION['user_name'], 0, 1); ?></div>
                <div class="user-info">
                    <h4><?php echo $_SESSION['user_name']; ?></h4>
                    <p>Directeur Général</p>
                </div>
            </div>
        </div>
    </aside>

    <main class="main-content">
        <header>
            <div class="header-title">
                <h1>Espace Direction 👋</h1>
                <p>Gérez vos services et affectez des administrateurs pour SmartQueue.</p>
            </div>
            <div class="header-actions">
                <button class="btn-add" onclick="openCreateModal()">
                    <i class="fas fa-plus"></i> Ajouter Admin
                </button>
                <i class="fas fa-moon theme-toggle-icon" style="cursor:pointer; font-size: 24px; color: #B2A14E;" onclick="toggleTheme()"></i>
            </div>
        </header>

        <?php if ($message): ?>
            <div class="alert"><?php echo $message; ?></div>
        <?php endif; ?>

        <!-- Section Admins Actuels -->
        <div class="admin-card">
            <h3>Administrateurs de Services</h3>
            <div class="table-container">
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Établissement</th>
                            <th>Secteur</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($admins as $admin): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($admin['name']); ?></td>
                            <td><?php echo htmlspecialchars($admin['email']); ?></td>
                            <td><?php echo htmlspecialchars($admin['establishment']); ?></td>
                            <td><span class="badge badge-servi"><?php echo ucfirst($admin['sector']); ?></span></td>
                            <td>
                                <a href="?revoke=<?php echo $admin['id']; ?>" class="btn-revoke" onclick="return confirm('Révoquer les accès admin ?')">Révoquer</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($admins)): ?>
                            <tr><td colspan="5" style="text-align:center; color: var(--text-muted);">Aucun administrateur configuré.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Section Promotion Clients -->
        <div class="admin-card" style="margin-top: 40px;">
            <h3>Promouvoir un Client</h3>
            <p class="subtitle">Sélectionnez un utilisateur enregistré pour lui donner des accès administratifs.</p>
            <table class="user-table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clients as $client): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($client['name']); ?></td>
                        <td><?php echo htmlspecialchars($client['email']); ?></td>
                        <td>
                            <button class="btn-promote" onclick="openPromotionModal(<?php echo $client['id']; ?>, '<?php echo htmlspecialchars($client['name']); ?>')">
                                <i class="fas fa-user-plus"></i> Nommer Admin
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Modal Promotion -->
    <div id="promoModal" class="modal">
        <div class="modal-content">
            <h2 id="modalTitle">Nommer Admin</h2>
            <form method="POST">
                <input type="hidden" name="promote_user_id" id="promote_user_id">
                <div class="input-group">
                    <label>Établissement</label>
                    <input type="text" name="establishment" placeholder="Ex: Agence Tunis Centre" required>
                </div>
                <div class="input-group">
                    <label>Secteur / Service</label>
                    <select name="sector" required>
                        <option value="banque">Banque / Finance</option>
                        <option value="administration">Administration Publique</option>
                        <option value="sante">Santé / Clinique</option>
                        <option value="retail">Commerce / Magasin</option>
                    </select>
                </div>
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" class="btn-promote" style="flex: 1;">Confirmer</button>
                    <button type="button" class="btn-action btn-secondary-white" style="flex: 1;" onclick="closeAllModals()">Annuler</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Create New Admin -->
    <div id="createModal" class="modal">
        <div class="modal-content">
            <h2>Nouvel Administrateur</h2>
            <form method="POST">
                <input type="hidden" name="create_admin" value="1">
                <div class="input-group">
                    <label>Nom Complet</label>
                    <input type="text" name="name" placeholder="Nom de l'agent" required>
                </div>
                <div class="input-group">
                    <label>Email Professionnel</label>
                    <input type="email" name="email" placeholder="email@smartqueue.com" required>
                </div>
                <div class="input-group">
                    <label>Établissement</label>
                    <input type="text" name="establishment" placeholder="Ex: Bureau de Poste Ariana" required>
                </div>
                <div class="input-group">
                    <label>Secteur</label>
                    <select name="sector" required>
                        <option value="banque">Banque</option>
                        <option value="administration">Administration</option>
                        <option value="sante">Santé</option>
                        <option value="cinéma">Cinéma</option>
                        <option value="resto">Restauration</option>
                    </select>
                </div>
                <p style="font-size: 12px; color: var(--text-muted);">* Le mot de passe par défaut sera <b>admin123</b></p>
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" class="btn-promote" style="flex: 1; background: #4CAF50;">Créer le compte</button>
                    <button type="button" class="btn-action btn-secondary-white" style="flex: 1;" onclick="closeAllModals()">Annuler</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../theme.js"></script>
    <script>
        function openPromotionModal(id, name) {
            document.getElementById('promote_user_id').value = id;
            document.getElementById('modalTitle').innerText = "Promouvoir " + name;
            document.getElementById('promoModal').style.display = 'block';
        }
        function openCreateModal() {
            document.getElementById('createModal').style.display = 'block';
        }
        function closeAllModals() {
            document.getElementById('promoModal').style.display = 'none';
            document.getElementById('createModal').style.display = 'none';
        }
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) closeAllModals();
        }
    </script>
</body>
</html>

