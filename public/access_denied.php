<?php
// Inclure la configuration
require_once 'config_infinityfree.php';
require_once 'auth_config.php';

// Vérifier si l'utilisateur est connecté
if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accès Refusé - Ferme d'Élevage</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .access-denied-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 3rem;
            max-width: 500px;
            width: 100%;
            margin: 2rem;
            text-align: center;
        }
        .access-denied-icon {
            font-size: 5rem;
            color: #dc3545;
            margin-bottom: 1rem;
        }
        .btn-custom {
            border-radius: 10px;
            padding: 12px 24px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-custom:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="access-denied-container">
        <div class="access-denied-icon">
            <i class="fas fa-ban"></i>
        </div>
        
        <h1 class="text-danger mb-3">Accès Refusé</h1>
        
        <p class="text-muted mb-4">
            Vous n'avez pas les permissions nécessaires pour accéder à cette page.
        </p>
        
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>Rôle actuel :</strong> 
            <?php if (isset($_SESSION['user_role'])): ?>
                <span class="badge bg-<?= getRoleColor($_SESSION['user_role']) ?>">
                    <?= getRoleName($_SESSION['user_role']) ?>
                </span>
            <?php else: ?>
                <span class="badge bg-secondary">Non défini</span>
            <?php endif; ?>
        </div>
        
        <div class="row mt-4">
            <div class="col-md-6">
                <a href="index_final.php" class="btn btn-primary btn-custom w-100">
                    <i class="fas fa-home"></i> Tableau de Bord
                </a>
            </div>
            <div class="col-md-6">
                <a href="logout.php" class="btn btn-outline-danger btn-custom w-100">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </div>
        </div>
        
        <div class="mt-4">
            <p class="text-muted small">
                Si vous pensez qu'il s'agit d'une erreur, contactez votre administrateur.
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
