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
    <title>SmartQueue - Choisir l'Administration</title>
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
            <h2>Administrations</h2>
            <p class="subtitle">Trouvez le service administratif le plus proche de vous</p>
        </div>

        <div class="filter-section">
            <div class="input-wrapper search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Rechercher une administration..." onkeyup="filterList()">
            </div>

            <div class="input-wrapper filter-box">
                <i class="fas fa-location-dot"></i>
                <select id="locationFilter" onchange="filterList()">
                    <option value="">Toutes les localisations</option>
                    <option value="Tunis">Tunis</option>
                    <option value="Sousse">Sousse</option>
                </select>
            </div>
        </div>

        <div class="list-container" id="agencyList">
            <div class="list-item card" data-name="Poste" data-location="Tunis">
                <div class="item-info">
                    <div class="item-logo"><i class="fas fa-file-invoice"></i></div>
                    <div class="item-details">
                        <h3>La Poste Tunisienne</h3>
                        <p><i class="fas fa-map-marker-alt"></i> Tunis, Centre Ville</p>
                    </div>
                </div>
                <button class="btn-select" onclick="confirmBooking('La Poste - Tunis Centre', 'Tunis', 'administration')">Choisir</button>
            </div>

            <div class="list-item card" data-name="Municipalité" data-location="Sousse">
                <div class="item-info">
                    <div class="item-logo"><i class="fas fa-building"></i></div>
                    <div class="item-details">
                        <h3>Municipalité de Sousse</h3>
                        <p><i class="fas fa-map-marker-alt"></i> Sousse, Médina</p>
                    </div>
                </div>
                <button class="btn-select" onclick="confirmBooking('Municipalité de Sousse', 'Sousse', 'administration')">Choisir</button>
            </div>
        </div>

        <div class="footer-actions">
            <button class="btn-secondary" onclick="window.history.back()">Retour aux catégories</button>
        </div>
    </div>
    
    <script>
        const USER_ID = "<?php echo $_SESSION['user_id']; ?>";
    </script>
    <script src="../etablissement/etablissement.js?v=4"></script>
    <script src="../theme.js"></script>

</body>
</html>
