<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'main_admin') {
    header("Location: ../signin/signin.html");
    exit;
}
include "../test.php";

// Director's specific establishment — FIXED from session
$MY_ESTABLISHMENT = $_SESSION['establishment'];
$MY_EST_KEY       = strtolower(preg_replace('/[^a-z0-9]/i', '_', $MY_ESTABLISHMENT));
$MY_SECTOR        = $_SESSION['sector'];
$MY_SECTOR_LABEL  = ucfirst($MY_SECTOR);

$message = "";

// Handle Admin Creation — forced to director's establishment
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_admin'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $pass  = password_hash('admin123', PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, establishment, sector) VALUES (?, ?, ?, 'admin', ?, ?)");
    $stmt->bind_param("sssss", $name, $email, $pass, $MY_ESTABLISHMENT, $MY_SECTOR);
    if ($stmt->execute()) {
        $message = "Nouvel agent créé pour <b>{$MY_ESTABLISHMENT}</b> ! (Pass: admin123)";
    } else {
        $message = "Erreur : L'email existe déjà.";
    }
    $stmt->close();
}

// Handle Admin Promotion — forced to director's establishment
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['promote_user_id'])) {
    $uid = (int)$_POST['promote_user_id'];

    $stmt = $conn->prepare("UPDATE users SET role='admin', establishment=?, sector=? WHERE id=?");
    $stmt->bind_param("ssi", $MY_ESTABLISHMENT, $MY_SECTOR, $uid);
    $stmt->execute();
    $stmt->close();
    $message = "Utilisateur promu agent pour {$MY_ESTABLISHMENT}.";
}

// Handle Revocation — only revoke admins within this establishment
if (isset($_GET['revoke'])) {
    $uid = (int)$_GET['revoke'];
    $stmt = $conn->prepare("UPDATE users SET role='client', establishment=NULL, sector=NULL WHERE id=? AND establishment=?");
    $stmt->bind_param("is", $uid, $MY_ESTABLISHMENT);
    $stmt->execute();
    $stmt->close();
    $message = "Accès agent révoqué.";
}

// Fetch all clients (for promotion)
$result  = $conn->query("SELECT id, name, email FROM users WHERE role='client'");
$clients = $result->fetch_all(MYSQLI_ASSOC);

// Fetch only admins belonging to THIS specific establishment
$stmt  = $conn->prepare("SELECT id, name, email, establishment, sector FROM users WHERE role='admin' AND establishment=?");
$stmt->bind_param("s", $MY_ESTABLISHMENT);
$stmt->execute();
$admins = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartQueue - Direction <?php echo $MY_SECTOR_LABEL; ?></title>
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

        /* ── Daily Reset Card ── */
        .reset-card {
            background: linear-gradient(135deg, rgba(255,82,82,0.08) 0%, rgba(178,161,78,0.08) 100%);
            border: 1px solid rgba(255, 82, 82, 0.3);
            border-radius: 20px;
            padding: 30px;
            margin-top: 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 30px;
            flex-wrap: wrap;
        }
        .reset-card-info h3 { margin: 0 0 8px; font-size: 1.15rem; }
        .reset-card-info p  { margin: 0; color: var(--text-muted); font-size: 0.9rem; line-height: 1.5; }
        .reset-counter-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 12px;
            background: rgba(255,255,255,0.06);
            border: 1px solid var(--border-color);
            border-radius: 50px;
            padding: 6px 16px;
            font-size: 0.85rem;
            color: var(--text-muted);
        }
        .reset-counter-badge span { font-weight: 700; color: var(--primary-color); font-size: 1rem; }
        .btn-reset-day {
            background: linear-gradient(135deg, #FF5252, #ff1744);
            color: #fff;
            border: none;
            padding: 14px 28px;
            border-radius: 12px;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 20px rgba(255,82,82,0.35);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .btn-reset-day:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(255,82,82,0.5);
        }
        .btn-reset-day:active { transform: translateY(0); }
        .reset-success-toast {
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%) translateY(80px);
            background: #4CAF50;
            color: #fff;
            padding: 14px 28px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.95rem;
            box-shadow: 0 8px 30px rgba(76,175,80,0.4);
            z-index: 9999;
            transition: transform 0.4s cubic-bezier(0.34,1.56,0.64,1), opacity 0.4s ease;
            opacity: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .reset-success-toast.show {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
        }

        /* ── Per-Service Reset Grid ── */
        .service-reset-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 16px;
        }
        .service-reset-card {
            background: var(--glass-bg);
            border: 1px solid var(--border-color);
            border-radius: 18px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 14px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .service-reset-card:hover {
            border-color: rgba(255,82,82,0.4);
            box-shadow: 0 4px 20px rgba(255,82,82,0.1);
        }
        .src-icon {
            width: 46px; height: 46px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
        }
        .src-icon-banque        { background: rgba(178,161,78,0.15); color: #B2A14E; }
        .src-icon-cinema        { background: rgba(126,87,194,0.15); color: #9c6fe4; }
        .src-icon-resto         { background: rgba(76,175,80,0.15);  color: #4CAF50; }
        .src-icon-admin         { background: rgba(33,150,243,0.15); color: #2196F3; }
        .src-info               { flex: 1; }
        .src-info h4            { margin: 0 0 4px; font-size: 0.95rem; }
        .src-badge {
            font-size: 0.78rem;
            color: var(--text-muted);
        }
        .src-badge span         { font-weight: 700; color: var(--primary-color); }
        .btn-src-reset {
            background: linear-gradient(135deg, #FF5252, #ff1744);
            color: #fff;
            border: none;
            padding: 9px 16px;
            border-radius: 10px;
            font-size: 0.82rem;
            font-weight: 700;
            cursor: pointer;
            display: flex; align-items: center; gap: 6px;
            box-shadow: 0 3px 12px rgba(255,82,82,0.3);
            transition: transform 0.15s, box-shadow 0.15s;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .btn-src-reset:hover  { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(255,82,82,0.45); }
        .btn-src-reset:active { transform: translateY(0); }
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
                    <p>Directeur - <?php echo htmlspecialchars($MY_ESTABLISHMENT); ?></p>
                </div>
            </div>
        </div>
    </aside>

    <main class="main-content">
        <header>
            <div class="header-title">
                <h1>Direction <?php echo htmlspecialchars($MY_ESTABLISHMENT); ?> 👋</h1>
                <p>Espace de gestion pour <b><?php echo htmlspecialchars($MY_ESTABLISHMENT); ?></b> (<?php echo $MY_SECTOR_LABEL; ?>).</p>
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
            <h3>Agents — <?php echo htmlspecialchars($MY_ESTABLISHMENT); ?></h3>
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
            <h3>Promouvoir un Client pour <?php echo htmlspecialchars($MY_ESTABLISHMENT); ?></h3>
            <p class="subtitle">Sélectionnez un utilisateur enregistré pour lui donner accès au guichet local.</p>
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

        <!-- Daily Reset Panel -->
        <div style="margin-top: 40px;">
            <h3 style="margin-bottom: 6px;"><i class="fas fa-calendar-day" style="color:#FF5252; margin-right:10px;"></i>Réinitialisation Journalière</h3>
            <p style="color: var(--text-muted); margin-bottom: 20px; font-size: 0.9rem;">Remettez le compteur de tickets à <b>A-001</b> pour une nouvelle journée de travail à <?php echo htmlspecialchars($MY_ESTABLISHMENT); ?>.</p>

            <div class="service-reset-card" id="card-<?php echo $MY_EST_KEY; ?>" style="max-width: 500px;">
                <div class="src-icon src-icon-<?php echo ($MY_SECTOR == 'cinéma' ? 'cinema' : ($MY_SECTOR == 'administration' ? 'admin' : $MY_SECTOR)); ?>">
                    <i class="fas <?php 
                        if($MY_SECTOR == 'banque') echo 'fa-university';
                        elseif($MY_SECTOR == 'cinéma' || $MY_SECTOR == 'cinema') echo 'fa-film';
                        elseif($MY_SECTOR == 'resto') echo 'fa-utensils';
                        else echo 'fa-file-invoice';
                    ?>"></i>
                </div>
                <div class="src-info">
                    <h4><?php echo htmlspecialchars($MY_ESTABLISHMENT); ?></h4>
                    <div class="src-badge">Dernier ticket émis : <span id="last-<?php echo $MY_EST_KEY; ?>">—</span></div>
                </div>
                <button class="btn-src-reset" onclick="resetService('<?php echo $MY_EST_KEY; ?>')">
                    <i class="fas fa-redo-alt"></i> Reset Journée
                </button>
            </div>
        </div>
    </main>

    <!-- Modal Promotion -->
    <div id="promoModal" class="modal">
        <div class="modal-content">
            <h2 id="modalTitle">Nommer Admin</h2>
            <form method="POST">
                <input type="hidden" name="promote_user_id" id="promote_user_id">
                <div class="input-group" style="opacity: 0.7;">
                    <label>Établissement</label>
                    <input type="text" value="<?php echo htmlspecialchars($MY_ESTABLISHMENT); ?>" disabled>
                </div>
                <div class="input-group" style="opacity: 0.7;">
                    <label>Secteur / Service</label>
                    <input type="text" value="<?php echo $MY_SECTOR_LABEL; ?>" disabled>
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
                <div class="input-group" style="opacity: 0.7;">
                    <label>Établissement</label>
                    <input type="text" value="<?php echo htmlspecialchars($MY_ESTABLISHMENT); ?>" disabled>
                </div>
                <div class="input-group" style="opacity: 0.7;">
                    <label>Secteur</label>
                    <input type="text" value="<?php echo $MY_SECTOR_LABEL; ?>" disabled>
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
        // ── Modals ─────────────────────────────────────────
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

        // ── Per-establishment counter display ─────────────────────
        const MY_SERVICE = '<?php echo $MY_EST_KEY; ?>';

        function refreshAllCounters() {
            const svc = MY_SERVICE;
            const c  = parseInt(localStorage.getItem(`ticketCounter_${svc}`) || '0');
            const el = document.getElementById(`last-${svc}`);
            if (!el) return;
            if (c === 0) {
                el.textContent  = 'Aucun';
                el.style.color  = 'var(--text-muted)';
            } else {
                el.textContent  = 'A-' + String(c).padStart(3, '0');
                el.style.color  = '';
            }
        }

        // ── Reset one service ──────────────────────────────────
        function resetService(serviceKey) {
            const label = serviceKey.charAt(0).toUpperCase() + serviceKey.slice(1);
            const confirmed = confirm(
                `⚠️ Réinitialiser le compteur de ${label} ?

Le prochain ticket sera A-001.
Les tickets actifs des clients pour ce service seront supprimés.`
            );
            if (!confirmed) return;

            // Reset ticket counter
            localStorage.setItem(`ticketCounter_${serviceKey}`, '0');

            // Reset agent queue for this service
            localStorage.removeItem(`agent_queue_state_${serviceKey}`);
            localStorage.removeItem(`agent_queue_history_${serviceKey}`);

            // Remove client tickets that belong to this service
            const toRemove = [];
            for (let i = 0; i < localStorage.length; i++) {
                const key = localStorage.key(i);
                if (key && key.startsWith('activeTickets')) {
                    try {
                        let tickets = JSON.parse(localStorage.getItem(key) || '[]');
                        tickets = tickets.filter(t => t.establishmentKey !== serviceKey);
                        if (tickets.length > 0) {
                            localStorage.setItem(key, JSON.stringify(tickets));
                        } else {
                            toRemove.push(key);
                        }
                    } catch(e) { toRemove.push(key); }
                }
            }
            toRemove.forEach(k => localStorage.removeItem(k));

            refreshAllCounters();
            showToast(`✅ Compteur ${label} remis à zéro !`);
        }

        // ── Toast ──────────────────────────────────────────────
        function showToast(msg) {
            const toast = document.getElementById('resetToast');
            toast.innerHTML = msg;
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 3200);
        }

        // Boot
        refreshAllCounters();
    </script>

    <!-- Toast notification -->
    <div class="reset-success-toast" id="resetToast">
        <i class="fas fa-check-circle"></i> Compteur remis à zéro — Bonne journée !
    </div>
</body>
</html>

