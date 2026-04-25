<?php
/**
 * Global Error Handler for SmartQueue
 * Provides consistent error messages throughout the application
 */

function handleAppError($title, $message, $details = '') {
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($title); ?> - SmartQueue</title>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body {
                font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                justify-content: center;
                align-items: center;
                padding: 20px;
            }
            
            .error-container {
                background: white;
                border-radius: 12px;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                max-width: 500px;
                padding: 50px 40px;
                text-align: center;
            }
            
            .error-icon {
                font-size: 60px;
                margin-bottom: 20px;
                display: block;
            }
            
            .error-title {
                font-size: 24px;
                font-weight: 700;
                color: #1a1a1a;
                margin-bottom: 12px;
            }
            
            .error-message {
                font-size: 16px;
                color: #555;
                margin-bottom: 20px;
                line-height: 1.6;
            }
            
            .error-details {
                background: #f5f5f5;
                border-left: 4px solid #ff6b6b;
                padding: 15px;
                margin: 20px 0;
                border-radius: 4px;
                font-size: 14px;
                color: #666;
                text-align: left;
                display: <?php echo $details ? 'block' : 'none'; ?>;
            }
            
            .error-buttons {
                display: flex;
                gap: 10px;
                margin-top: 30px;
                justify-content: center;
                flex-wrap: wrap;
            }
            
            .btn {
                padding: 12px 24px;
                border-radius: 8px;
                font-weight: 600;
                text-decoration: none;
                border: none;
                cursor: pointer;
                font-size: 14px;
                transition: all 0.3s ease;
            }
            
            .btn-primary {
                background: #667eea;
                color: white;
            }
            
            .btn-primary:hover {
                background: #5568d3;
                transform: translateY(-2px);
            }
            
            .btn-secondary {
                background: #f0f0f0;
                color: #333;
            }
            
            .btn-secondary:hover {
                background: #e0e0e0;
            }
            
            .logo-section {
                margin-bottom: 30px;
            }
            
            .logo-section i {
                font-size: 40px;
                color: #667eea;
            }
        </style>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    </head>
    <body>
        <div class="error-container">
            <div class="logo-section">
                <i class="fas fa-layer-group"></i>
                <p style="color: #667eea; font-weight: 600; margin-top: 10px;">SmartQueue</p>
            </div>
            
            <span class="error-icon">⚠️</span>
            <h1 class="error-title"><?php echo htmlspecialchars($title); ?></h1>
            <p class="error-message"><?php echo htmlspecialchars($message); ?></p>
            
            <?php if ($details): ?>
                <div class="error-details">
                    <strong>Détails:</strong><br>
                    <?php echo htmlspecialchars($details); ?>
                </div>
            <?php endif; ?>
            
            <div class="error-buttons">
                <button class="btn btn-primary" onclick="window.location.href='/';">
                    <i class="fas fa-home"></i> Accueil
                </button>
                <button class="btn btn-secondary" onclick="window.history.back();">
                    <i class="fas fa-arrow-left"></i> Retour
                </button>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Common error scenarios
function errorNotFound($resource = 'Ressource') {
    handleAppError(
        '404 - Non trouvé',
        $resource . ' que vous recherchez n\'existe pas ou a été supprimé.',
        'Vérifiez l\'URL et réessayez.'
    );
}

function errorUnauthorized() {
    handleAppError(
        '401 - Non autorisé',
        'Vous devez être connecté pour accéder à cette page.',
        'Veuillez vous connecter avec vos identifiants.'
    );
}

function errorForbidden() {
    handleAppError(
        '403 - Accès refusé',
        'Vous n\'avez pas les permissions nécessaires pour accéder à cette ressource.',
        'Contactez un administrateur si vous pensez que c\'est une erreur.'
    );
}

function errorServerError($message = '') {
    handleAppError(
        '500 - Erreur serveur',
        'Une erreur est survenue. Veuillez réessayer plus tard.',
        $message
    );
}

function errorBadRequest($message = '') {
    handleAppError(
        '400 - Requête invalide',
        'Les données envoyées ne sont pas valides.',
        $message
    );
}
?>
