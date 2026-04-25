<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] === 'client') {
    header("Location: ../signin/signin.html");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartQueue Agent - Historique</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="agent-dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script>
        window.userProfile = {
            name: "<?php echo addslashes($_SESSION['user_name']); ?>",
            role: "<?php echo addslashes($_SESSION['sector']) ?: 'Agent de Service'; ?>",
            establishment: "<?php echo addslashes($_SESSION['establishment']); ?>",
            roleType: "<?php echo $_SESSION['user_role']; ?>"
        };
    </script>
</head>
<body class="dark-theme">
    <!-- Sidebar -->
    <aside class="sidebar">
        <a href="../index.php" class="logo">
            <i class="fas fa-layer-group"></i> <span>SmartQueue</span>
        </a>
        <nav class="nav-links">
            <div class="nav-item">
                <a href="agent-dashboard.php" class="nav-link">
                    <i class="fas fa-chart-pie"></i> <span>Vue d'ensemble</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="agent-history.php" class="nav-link active">
                    <i class="fas fa-history"></i> <span>Historique</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="agent-settings.php" class="nav-link">
                    <i class="fas fa-cog"></i> <span>Paramètres</span>
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
                    <p><?php echo ucfirst($_SESSION['sector']) ?: 'Agent de Service'; ?></p>
                </div>
            </div>
        </div>
    </aside>


    <!-- Main Content -->
    <main class="main-content">
        <header>
            <div class="header-title">
                <h1>Historique des tickets</h1>
                <p>Consultez les clients servis et les tickets annulés pour aujourd'hui.</p>
            </div>
            <div class="header-actions">
                <i class="fas fa-moon theme-toggle-icon" style="cursor:pointer; font-size: 24px; color: #B2A14E;" onclick="toggleTheme()"></i>
                <button class="btn-action btn-secondary-white" onclick="exportHistory()">
                    <i class="fas fa-download"></i> Exporter CSV
                </button>
            </div>
        </header>

        <div class="history-card">
            <div class="table-container">
                <table id="history-table">
                    <thead>
                        <tr>
                            <th>ID Ticket</th>
                            <th>Nom du Client</th>
                            <th>Heure</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="history-body">
                        <!-- Loaded via JS -->
                    </tbody>
                </table>
            </div>
            <div id="no-history" style="display:none; text-align: center; padding: 40px; color: var(--text-muted);">
                <i class="fas fa-folder-open fa-3x" style="margin-bottom: 16px; opacity: 0.5;"></i>
                <p>Aucun historique pour le moment.</p>
            </div>
        </div>
    </main>

    <script src="../theme.js"></script>
    <script src="dashboard-core.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const history = getHistory();
            const body = document.getElementById('history-body');
            const emptyState = document.getElementById('no-history');
            
            if (history.length === 0) {
                emptyState.style.display = 'block';
                return;
            }
            
            history.forEach(entry => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${entry.id}</td>
                    <td>${entry.name}</td>
                    <td>${entry.time}</td>
                    <td>${entry.date}</td>
                    <td><span class="badge badge-${entry.status}">${entry.status}</span></td>
                `;
                body.appendChild(tr);
            });
        });

        function exportHistory() {
            alert("Exportation des données en cours...");
        }
    </script>
</body>
</html>
