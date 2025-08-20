<?php
// Inclure la configuration
require_once 'config_sqlite.php';
require_once 'auth_config.php';

// Vérifier les permissions d'administrateur
requirePermission('gestion_utilisateurs');

// Initialiser les variables
$message = '';
$error = '';

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $user_id = $_POST['user_id'] ?? '';
    
    if ($action && $user_id) {
        $db = connectDB();
        if ($db) {
            try {
                switch ($action) {
                    case 'promote':
                        $new_role = $_POST['new_role'] ?? '';
                        $current_role = $_POST['current_role'] ?? '';
                        
                        // Vérifications de sécurité
                        if (in_array($new_role, getAvailableRoles()) && 
                            canPromoteTo($current_role, $new_role) && 
                            hasPermission('promotion_roles')) {
                            
                            $stmt = $db->prepare("UPDATE utilisateurs SET role = ? WHERE id = ?");
                            $stmt->execute([$new_role, $user_id]);
                            $message = "Utilisateur promu avec succès vers le rôle : " . getRoleName($new_role);
                        } else {
                            $error = "Promotion non autorisée ou rôle invalide";
                        }
                        break;
                        
                    case 'activate':
                        if (hasPermission('activation_comptes')) {
                            $stmt = $db->prepare("UPDATE utilisateurs SET statut = 'actif' WHERE id = ?");
                            $stmt->execute([$user_id]);
                            $message = "Utilisateur activé avec succès";
                        } else {
                            $error = "Permission insuffisante pour activer des comptes";
                        }
                        break;
                        
                    case 'deactivate':
                        if (hasPermission('activation_comptes')) {
                            $stmt = $db->prepare("UPDATE utilisateurs SET statut = 'inactif' WHERE id = ?");
                            $stmt->execute([$user_id]);
                            $message = "Utilisateur désactivé avec succès";
                        } else {
                            $error = "Permission insuffisante pour désactiver des comptes";
                        }
                        break;
                        
                    case 'delete':
                        // Vérifications de sécurité
                        if ($user_id != $_SESSION['user_id'] && hasPermission('suppression_utilisateurs')) {
                            $stmt = $db->prepare("DELETE FROM utilisateurs WHERE id = ?");
                            $stmt->execute([$user_id]);
                            $message = "Utilisateur supprimé avec succès";
                        } else {
                            $error = "Vous ne pouvez pas supprimer votre propre compte ou permission insuffisante";
                        }
                        break;
                }
            } catch (Exception $e) {
                $error = "Erreur lors de l'opération : " . $e->getMessage();
            }
        }
    }
}

// Récupérer la liste des utilisateurs
$db = connectDB();
$utilisateurs = [];
if ($db) {
    try {
        $stmt = $db->query("SELECT * FROM utilisateurs ORDER BY date_creation DESC");
        $utilisateurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $error = "Erreur lors de la récupération des utilisateurs";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Utilisateurs - Ferme d'Élevage</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .main-container {
            padding: 2rem 0;
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            overflow: hidden;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }
        
        .user-card {
            background: white;
            border-left: 4px solid #007bff;
            position: relative;
            overflow: visible;
        }
        
        .user-card .card-body {
            position: relative;
            overflow: visible;
        }
        
        .role-badge, .status-badge {
            font-size: 0.8rem;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            transition: all 0.3s ease;
        }
        
        .btn-action {
            border-radius: 25px;
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            border: 2px solid;
        }
        
        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .dropdown {
            position: relative;
        }
        
        .dropdown-menu {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            animation: slideUp 0.3s ease;
            position: absolute;
            z-index: 9999;
            min-width: 280px;
            background: white;
            padding: 8px 0;

            /* affichage vers le haut */
            top: auto;
            bottom: 100%;
            left: 0;
            transform-origin: bottom center;

            display: none;
            opacity: 0;
            visibility: hidden;
            
            /* Assurer que le menu reste au-dessus */
            margin-bottom: 10px;
        }
        
        .dropdown-menu.show {
            display: block !important;
            opacity: 1;
            visibility: visible;
        }
        
        .dropdown-header {
            font-weight: 600;
            color: #495057;
            padding: 0.5rem 1rem;
            border-bottom: 1px solid #e9ecef;
            margin-bottom: 5px;
        }
        
        .dropdown-divider {
            margin: 5px 0;
            border-color: #e9ecef;
        }
        
        .dropdown-item {
            padding: 0.75rem 1rem;
            transition: all 0.2s ease;
            border-radius: 8px;
            margin: 2px 8px;
            cursor: pointer;
            border: none;
            background: transparent;
            text-align: left;
            width: 100%;
            font-size: 0.9rem;
        }
        
        .dropdown-item:hover {
            background-color: #f8f9fa;
            transform: translateX(5px);
            color: #007bff;
        }
        
        .dropdown-item:active {
            background-color: #e9ecef;
            transform: scale(0.98);
        }
        
        .dropdown-item:focus {
            background-color: #e9ecef;
            outline: none;
            box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
        }
        
        .dropdown-item small {
            font-size: 0.8rem;
            color: #6c757d;
            margin-top: 2px;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(10px);
                visibility: hidden;
            }
            to {
                opacity: 1;
                transform: translateY(0);
                visibility: visible;
            }
        }
        
        .alert {
            border: none;
            border-radius: 15px;
            animation: slideIn 0.5s ease;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .form-promote {
            transition: all 0.3s ease;
        }
        
        .form-promote:hover {
            background-color: #f8f9fa;
        }
        
        .btn-promote {
            background: linear-gradient(45deg, #007bff, #0056b3);
            border: none;
            color: white;
            transition: all 0.3s ease;
        }
        
        .btn-promote:hover {
            background: linear-gradient(45deg, #0056b3, #004085);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,123,255,0.3);
        }
        
        .btn-promote:active {
            transform: translateY(0);
        }
        
        .stats-card {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
            border: none;
        }
        
        .stats-card.warning {
            background: linear-gradient(45deg, #ffc107, #fd7e14);
        }
        
        .stats-card.info {
            background: linear-gradient(45deg, #17a2b8, #6f42c1);
        }
        
        .stats-card.danger {
            background: linear-gradient(45deg, #dc3545, #e83e8c);
        }
        
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }
        
        .success-animation {
            animation: successPulse 0.6s ease;
        }
        
        @keyframes successPulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body>
    <div class="container main-container">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="text-white mb-2">
                            <i class="fas fa-users-cog me-3"></i>Gestion des Utilisateurs
                        </h1>
                        <p class="text-white-50 mb-0">Administrez les accès et permissions de votre équipe</p>
                    </div>
                    <a href="index_final.php" class="btn btn-light">
                        <i class="fas fa-arrow-left me-2"></i>Retour au Dashboard
                    </a>
                </div>
            </div>
        </div>

        <!-- Messages -->
        <?php if ($message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Statistiques -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <h4><?= count($utilisateurs) ?></h4>
                        <p class="mb-0">Total Utilisateurs</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <h4><?= count(array_filter($utilisateurs, fn($u) => $u['statut'] === 'actif')) ?></h4>
                        <p class="mb-0">Utilisateurs Actifs</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card warning">
                    <div class="card-body text-center">
                        <h4><?= count(array_filter($utilisateurs, fn($u) => $u['role'] === 'admin')) ?></h4>
                        <p class="mb-0">Administrateurs</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card info">
                    <div class="card-body text-center">
                        <h4><?= count(array_filter($utilisateurs, fn($u) => $u['role'] === 'observateur')) ?></h4>
                        <p class="mb-0">Observateurs</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Liste des utilisateurs -->
        <div class="row">
            <?php foreach ($utilisateurs as $user): ?>
                <div class="col-lg-6 mb-3">
                    <div class="card user-card" id="user-<?= $user['id'] ?>">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="card-title mb-1">
                                        <?= htmlspecialchars($user['nom_complet']) ?>
                                        <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                            <span class="badge bg-secondary">Vous</span>
                                        <?php endif; ?>
                                    </h5>
                                    <p class="text-muted mb-1">
                                        <i class="fas fa-envelope"></i> <?= htmlspecialchars($user['email']) ?>
                                    </p>
                                    <?php if (isset($user['telephone']) && $user['telephone']): ?>
                                        <p class="text-muted mb-1">
                                            <i class="fas fa-phone"></i> <?= htmlspecialchars($user['telephone']) ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-<?= getRoleColor($user['role']) ?> role-badge">
                                        <?= getRoleName($user['role']) ?>
                                    </span>
                                    <br>
                                    <span class="badge bg-<?= $user['statut'] === 'actif' ? 'success' : 'danger' ?> status-badge">
                                        <?= ucfirst($user['statut']) ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="small text-muted mb-3">
                                <i class="fas fa-calendar"></i> Créé le <?= date('d/m/Y', strtotime($user['date_creation'])) ?>
                                <?php if (isset($user['derniere_connexion']) && $user['derniere_connexion']): ?>
                                    <br><i class="fas fa-clock"></i> Dernière connexion : <?= date('d/m/Y H:i', strtotime($user['derniere_connexion'])) ?>
                                <?php endif; ?>
                            </div>

                            <!-- Actions -->
                            <div class="d-flex gap-2 flex-wrap position-relative">
                                <!-- Promotion -->
                                <?php if (hasPermission('promotion_roles')): ?>
                                    <div class="dropdown dropup">
                                        <button class="btn btn-promote btn-action dropdown-toggle" type="button">
                                            <i class="fas fa-arrow-up"></i> Promouvoir
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><h6 class="dropdown-header">Changer le rôle</h6></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <?php foreach (getAvailableRoles() as $role): ?>
                                                <?php if ($role !== $user['role'] && canPromoteTo($user['role'], $role)): ?>
                                                    <li>
                                                        <form method="POST" class="form-promote" onsubmit="return confirmPromotion(this, '<?= getRoleName($role) ?>')">
                                                            <input type="hidden" name="action" value="promote">
                                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                            <input type="hidden" name="current_role" value="<?= $user['role'] ?>">
                                                            <input type="hidden" name="new_role" value="<?= $role ?>">
                                                            <button type="submit" class="dropdown-item">
                                                                <i class="fas fa-user-tag"></i> <?= getRoleName($role) ?>
                                                                <small class="text-muted d-block"><?= getRoleDescription($role) ?></small>
                                                            </button>
                                                        </form>
                                                    </li>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>

                                <!-- Activation/Désactivation -->
                                <?php if (hasPermission('activation_comptes')): ?>
                                    <?php if ($user['statut'] === 'actif'): ?>
                                        <form method="POST" style="display: inline;" onsubmit="return confirmAction('Désactiver cet utilisateur ?')">
                                            <input type="hidden" name="action" value="deactivate">
                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                            <button type="submit" class="btn btn-outline-warning btn-action">
                                                <i class="fas fa-pause"></i> Désactiver
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="activate">
                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                            <button type="submit" class="btn btn-outline-success btn-action">
                                                <i class="fas fa-play"></i> Activer
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <!-- Suppression -->
                                <?php if (hasPermission('suppression_utilisateurs') && $user['id'] != $_SESSION['user_id']): ?>
                                    <form method="POST" style="display: inline;" onsubmit="return confirmAction('Êtes-vous sûr de vouloir supprimer cet utilisateur ? Cette action est irréversible.')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                        <button type="submit" class="btn btn-outline-danger btn-action">
                                            <i class="fas fa-trash"></i> Supprimer
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Informations détaillées sur les rôles -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="fas fa-info-circle"></i> Guide des Rôles et Permissions</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach (getAvailableRoles() as $role): ?>
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <div class="border rounded p-3 h-100">
                                        <div class="d-flex align-items-center mb-2">
                                            <span class="badge bg-<?= getRoleColor($role) ?> me-2">
                                                <?= getRoleName($role) ?>
                                            </span>
                                        </div>
                                        <p class="small text-muted mb-2"><?= getRoleDescription($role) ?></p>
                                        <div class="small">
                                            <strong>Permissions :</strong>
                                            <ul class="list-unstyled mt-1">
                                                <?php 
                                                $permissions = $ROLES_CONFIG[$role]['permissions'];
                                                foreach ($permissions as $perm => $allowed):
                                                    if ($allowed):
                                                ?>
                                                    <li><i class="fas fa-check text-success"></i> <?= ucfirst(str_replace('_', ' ', $perm)) ?></li>
                                                <?php 
                                                    endif;
                                                endforeach; 
                                                ?>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="alert alert-warning mt-3">
                            <h6><i class="fas fa-exclamation-triangle"></i> Notes importantes :</h6>
                            <ul class="mb-0">
                                <li><strong>Nouveaux inscrits</strong> : Rôle "Observateur" par défaut (consultation uniquement)</li>
                                <li><strong>Promotion</strong> : Seul l'administrateur peut promouvoir les rôles</li>
                                <li><strong>Sécurité</strong> : Impossible de supprimer son propre compte</li>
                                <li><strong>Contrôle</strong> : Activation/désactivation des comptes par l'admin</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Fonction pour confirmer les promotions avec animation
        function confirmPromotion(form, roleName) {
            if (confirm(`Promouvoir cet utilisateur vers le rôle "${roleName}" ?`)) {
                // Ajouter une classe de chargement
                const card = form.closest('.user-card');
                card.classList.add('loading');
                
                // Soumettre le formulaire
                setTimeout(() => {
                    form.submit();
                }, 300);
                
                return true;
            }
            return false;
        }
        
        // Fonction pour confirmer les autres actions
        function confirmAction(message) {
            return confirm(message);
        }
        
        // Animation de succès après promotion
        document.addEventListener('DOMContentLoaded', function() {
            // Vérifier s'il y a un message de succès
            const successAlert = document.querySelector('.alert-success');
            if (successAlert) {
                // Ajouter une animation de succès
                const cards = document.querySelectorAll('.user-card');
                cards.forEach(card => {
                    card.classList.add('success-animation');
                });
                
                // Supprimer l'animation après un délai
                setTimeout(() => {
                    cards.forEach(card => {
                        card.classList.remove('success-animation');
                    });
                }, 600);
            }
        });
        
        // Comportement du dropdown
        document.addEventListener('DOMContentLoaded', function() {
            const toggles = document.querySelectorAll('.dropdown-toggle');

            toggles.forEach(toggle => {
                const menu = toggle.nextElementSibling;

                toggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    // Fermer les autres menus
                    document.querySelectorAll('.dropdown-menu.show').forEach(m => m.classList.remove('show'));

                    // Ouvrir celui cliqué
                    menu.classList.add('show');
                });
            });

            // Fermer si on clique ailleurs
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.dropdown')) {
                    document.querySelectorAll('.dropdown-menu.show').forEach(m => m.classList.remove('show'));
                }
            });
        });
    </script>
</body>
</html>
