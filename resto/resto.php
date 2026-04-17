<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../signin/signin.html");
    exit;
}
?>
<!DOCTYPE html>

<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartQueue - Choisir le Restaurant Universitaire</title>
    <link rel="stylesheet" href="../signin/signin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container-wide">
        <div class="header">
            <div style="position: absolute; top: 20px; right: 20px;">
                <i class="fas fa-moon theme-toggle-icon" style="cursor:pointer; font-size: 24px; color: #B2A14E;" onclick="toggleTheme()"></i>
            </div>
            <div class="logo-mini">
                <i class="fas fa-layer-group"></i> SmartQueue
            </div>
            <h2>Restaurants U disponibles</h2>
            <p class="subtitle">Trouvez le RU le plus proche de vous</p>
        </div>

        <div class="filter-section">
            <div class="input-wrapper search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Rechercher un Resto U..." onkeyup="filterList()">
            </div>

            <div class="input-wrapper filter-box">
                <i class="fas fa-location-dot"></i>
                <select id="locationFilter" onchange="filterList()">
                    <option value="">Toutes les localisations</option>
                    <option value="Monastir">Monastir</option>
                    <option value="Sousse">Sousse</option>
                </select>
            </div>
        </div>

        <div class="list-container" id="agencyList">
            <div class="list-item card" data-name="Campus" data-location="Monastir">
                <div class="item-info">
                    <div class="item-logo"><i class="fas fa-utensils"></i></div>
                    <div class="item-details">
                        <h3>RU Campus</h3>
                        <p><i class="fas fa-map-marker-alt"></i> Monastir, Zone Univ</p>
                    </div>
                </div>
                <button class="btn-select" onclick="confirmBooking('RU Campus Monastir')">Choisir</button>
            </div>

            <div class="list-item card" data-name="Sahloul" data-location="Sousse">
                <div class="item-info">
                    <div class="item-logo"><i class="fas fa-utensils"></i></div>
                    <div class="item-details">
                        <h3>RU Sahloul</h3>
                        <p><i class="fas fa-map-marker-alt"></i> Sousse, Cité Sahloul</p>
                    </div>
                </div>
                <button class="btn-select" onclick="confirmBooking('RU Sahloul Sousse')">Choisir</button>
            </div>
        </div>

        <div class="footer-actions">
            <button class="btn-secondary" onclick="window.history.back()">Retour aux catégories</button>
        </div>
    </div>
    
    <script>
        const USER_ID = "<?php echo $_SESSION['user_id']; ?>";
    </script>
    <script src="../etablissement/etablissement.js?v=2"></script>
    <script src="../theme.js"></script>

</body>
</html>
