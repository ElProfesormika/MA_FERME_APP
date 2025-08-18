<?php
// Inclure la configuration des devises et de l'app
require_once 'config_devises.php';
require_once 'config_app.php';

// Traitement du changement de devise
if (isset($_POST['changer_devise'])) {
    setDeviseActuelle($_POST['devise']);
    header("Location: index_final.php");
    exit;
}

// Configuration de la base de données
$config = [
    'host' => 'sql204.infinityfree.com',
    'database' => 'if0_39665291_ferme_ya',
    'username' => 'if0_39665291',
    'password' => 'JPrsDcoxt6DWQ0X',
    'charset' => 'utf8mb4'
];

// Fonction pour se connecter à la base de données
function connectDB($config) {
    try {
        $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
        $pdo = new PDO($dsn, $config['username'], $config['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        return false;
    }
}

$db = connectDB($config);

// Récupération des statistiques rapides
function getQuickStats($db) {
    if (!$db) return [];
    
    $stats = [];
    
    // Animaux
    $stmt = $db->query("SELECT COUNT(*) as total FROM animaux");
    $stats['animaux'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Stocks en rupture
    $stmt = $db->query("SELECT COUNT(*) as total FROM stocks WHERE quantite <= 10");
    $stats['rupture'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Activités aujourd'hui
    $stmt = $db->query("SELECT COUNT(*) as total FROM activites WHERE date = CURDATE()");
    $stats['activites_aujourdhui'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Alertes actives
    $stmt = $db->query("SELECT COUNT(*) as total FROM alertes WHERE statut = 'active'");
    $stats['alertes_actives'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    return $stats;
}

$stats = getQuickStats($db);
$dbStatus = $db ? '✅ Connecté' : '❌ Non connecté';
$devise_actuelle = getDeviseActuelle();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ferme d'Élevage - Tableau de Bord</title>
    
    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#764ba2">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Ferme Élevage">
    <meta name="msapplication-TileColor" content="#764ba2">
    <meta name="msapplication-config" content="/browserconfig.xml">
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="/manifest.json">

    <!-- Apple Touch Icons (dynamiques) -->
    <link rel="apple-touch-icon" href="/icons/icon.php?size=152">
    <link rel="apple-touch-icon" sizes="152x152" href="/icons/icon.php?size=152">
    <link rel="apple-touch-icon" sizes="180x180" href="/icons/icon.php?size=180">
    <link rel="apple-touch-icon" sizes="167x167" href="/icons/icon.php?size=167">

    <!-- Favicon (dynamiques) -->
    <link rel="icon" type="image/png" sizes="32x32" href="/icons/icon.php?size=32">
    <link rel="icon" type="image/png" sizes="16x16" href="/icons/icon.php?size=16">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .main-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            margin: 2rem auto;
            padding: 2rem;
            max-width: 1400px;
        }
        .module-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-left: 5px solid #667eea;
            position: relative;
            overflow: hidden;
        }
        .module-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }
        .module-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        .stat-card {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            text-align: center;
            transition: transform 0.2s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        .stat-card:hover {
            transform: scale(1.05);
        }
        .btn-module {
            border-radius: 10px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .btn-module::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        .btn-module:hover::before {
            left: 100%;
        }
        .btn-module:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        .module-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            display: block;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .module-card:hover .module-icon {
            transform: scale(1.1) rotate(5deg);
        }
        .stat-card i {
            transition: transform 0.3s ease;
        }
        .stat-card:hover i {
            transform: scale(1.2);
        }
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .module-card {
            animation: fadeInUp 0.6s ease-out;
        }
        .stat-card {
            animation: fadeInUp 0.6s ease-out;
        }
        .stat-card:nth-child(1) { animation-delay: 0.1s; }
        .stat-card:nth-child(2) { animation-delay: 0.2s; }
        .stat-card:nth-child(3) { animation-delay: 0.3s; }
        .stat-card:nth-child(4) { animation-delay: 0.4s; }
        .stat-card:nth-child(5) { animation-delay: 0.5s; }
        .stat-card:nth-child(6) { animation-delay: 0.6s; }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="text-center">
                <h1 class="display-2 fw-bold text-primary mb-3" style="text-shadow: 2px 2px 4px rgba(0,0,0,0.1);">
                    <i class="fas fa-tractor me-3"></i><?= htmlspecialchars(app_name()) ?>
                </h1>
                <p class="lead text-muted fs-4"><?= htmlspecialchars(app_slogan()) ?></p>
                <div class="alert alert-info d-inline-block">
                    <strong>Statut de la base de données :</strong> <?= $dbStatus ?>
                </div>
            </div>
            <div class="d-flex gap-2">
                <!-- Sélecteur de devise -->
                <form method="POST" class="d-flex align-items-center">
                    <label class="form-label me-2 mb-0">Devise :</label>
                    <select name="devise" class="form-select form-select-sm me-2" style="width: auto;">
                        <option value="FCFA" <?= $devise_actuelle === 'FCFA' ? 'selected' : '' ?>>FCFA</option>
                        <option value="EUR" <?= $devise_actuelle === 'EUR' ? 'selected' : '' ?>>€ Euro</option>
                        <option value="USD" <?= $devise_actuelle === 'USD' ? 'selected' : '' ?>>$ Dollar</option>
                    </select>
                    <button type="submit" name="changer_devise" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-sync"></i>
                    </button>
                </form>
                
                <!-- Bouton d'installation PWA -->
                <button id="pwa-install-btn" class="btn btn-outline-success btn-sm" style="display: none;">
                    <i class="fas fa-download"></i> Installer
                </button>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="row mb-4">
            <div class="col-md-2">
                <div class="stat-card">
                    <i class="fas fa-cow fa-2x mb-2"></i>
                    <h3 class="mb-1"><?= $stats['animaux'] ?? 0 ?></h3>
                    <p class="mb-0 fw-bold">Animaux</p>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card">
                    <i class="fas fa-boxes fa-2x mb-2"></i>
                    <h3 class="mb-1"><?= $stats['rupture'] ?? 0 ?></h3>
                    <p class="mb-0 fw-bold">Stocks en rupture</p>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card">
                    <i class="fas fa-tasks fa-2x mb-2"></i>
                    <h3 class="mb-1"><?= $stats['activites_aujourdhui'] ?? 0 ?></h3>
                    <p class="mb-0 fw-bold">Aujourd'hui</p>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card">
                    <i class="fas fa-bell fa-2x mb-2"></i>
                    <h3 class="mb-1"><?= $stats['alertes_actives'] ?? 0 ?></h3>
                    <p class="mb-0 fw-bold">Alertes actives</p>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card">
                    <i class="fas fa-calendar-day fa-2x mb-2"></i>
                    <h3 class="mb-1"><?= date('d/m') ?></h3>
                    <p class="mb-0 fw-bold">Aujourd'hui</p>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card">
                    <i class="fas fa-money-bill-wave fa-2x mb-2"></i>
                    <h3 class="mb-1"><?= $devise_actuelle ?></h3>
                    <p class="mb-0 fw-bold">Devise actuelle</p>
                </div>
            </div>
        </div>

        <!-- Modules principaux -->
        <div class="row">
            <!-- Gestion des Animaux -->
            <div class="col-lg-6">
                <div class="module-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <i class="fas fa-cow module-icon text-primary"></i>
                            <h3 class="text-primary">Gestion des Animaux</h3>
                        </div>
                        <span class="badge bg-primary fs-6"><?= $stats['animaux'] ?? 0 ?> animaux</span>
                    </div>
                    <p class="text-muted mb-4">Gérez votre cheptel : ajout, modification, suivi sanitaire et reproduction</p>
                    <div class="d-flex gap-2">
                        <a href="animaux_improved.php" class="btn btn-primary btn-module">
                            <i class="fas fa-list"></i> Voir les animaux
                        </a>
                        <a href="animaux_improved.php" class="btn btn-outline-primary btn-module">
                            <i class="fas fa-plus"></i> Ajouter
                        </a>
                    </div>
                </div>
            </div>

            <!-- Gestion des Stocks -->
            <div class="col-lg-6">
                <div class="module-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <i class="fas fa-boxes module-icon text-success"></i>
                            <h3 class="text-success">Gestion des Stocks</h3>
                        </div>
                        <span class="badge bg-danger fs-6"><?= $stats['rupture'] ?? 0 ?> ruptures</span>
                    </div>
                    <p class="text-muted mb-4">Inventaire, alertes de rupture et péremption, calcul des valeurs</p>
                    <div class="d-flex gap-2">
                        <a href="stocks_improved.php" class="btn btn-success btn-module">
                            <i class="fas fa-list"></i> Voir les stocks
                        </a>
                        <a href="stocks_improved.php" class="btn btn-outline-success btn-module">
                            <i class="fas fa-plus"></i> Ajouter
                        </a>
                    </div>
                </div>
            </div>

            <!-- Gestion des Activités -->
            <div class="col-lg-6">
                <div class="module-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <i class="fas fa-tasks module-icon text-warning"></i>
                            <h3 class="text-warning">Gestion des Activités</h3>
                        </div>
                        <span class="badge bg-warning fs-6"><?= $stats['activites_aujourdhui'] ?? 0 ?> aujourd'hui</span>
                    </div>
                    <p class="text-muted mb-4">Planning des tâches, assignation d'employés, suivi des activités</p>
                    <div class="d-flex gap-2">
                        <a href="activites_improved.php" class="btn btn-warning btn-module">
                            <i class="fas fa-list"></i> Voir les activités
                        </a>
                        <a href="activites_improved.php" class="btn btn-outline-warning btn-module">
                            <i class="fas fa-plus"></i> Ajouter
                        </a>
                    </div>
                </div>
            </div>

            <!-- Gestion des Employés -->
            <div class="col-lg-6">
                <div class="module-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <i class="fas fa-users module-icon text-info"></i>
                            <h3 class="text-info">Gestion des Employés</h3>
                        </div>
                        <span class="badge bg-info fs-6">Personnel</span>
                    </div>
                    <p class="text-muted mb-4">Gestion du personnel, postes, responsabilités et planning</p>
                    <div class="d-flex gap-2">
                        <a href="employes_improved.php" class="btn btn-info btn-module">
                            <i class="fas fa-list"></i> Voir les employés
                        </a>
                        <a href="employes_improved.php" class="btn btn-outline-info btn-module">
                            <i class="fas fa-plus"></i> Ajouter
                        </a>
                    </div>
                </div>
            </div>

            <!-- Système d'Alertes -->
            <div class="col-lg-6">
                <div class="module-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <i class="fas fa-bell module-icon text-danger"></i>
                            <h3 class="text-danger">Système d'Alertes</h3>
                        </div>
                        <span class="badge bg-danger fs-6"><?= $stats['alertes_actives'] ?? 0 ?> actives</span>
                    </div>
                    <p class="text-muted mb-4">Surveillance automatique, détection de problèmes, gestion des priorités</p>
                    <div class="d-flex gap-2">
                        <a href="alertes_improved.php" class="btn btn-danger btn-module">
                            <i class="fas fa-exclamation-triangle"></i> Voir les alertes
                        </a>
                        <a href="alertes_improved.php" class="btn btn-outline-danger btn-module">
                            <i class="fas fa-plus"></i> Créer
                        </a>
                    </div>
                </div>
            </div>

            <!-- Rapports et Exports -->
            <div class="col-lg-6">
                <div class="module-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <i class="fas fa-chart-bar module-icon text-secondary"></i>
                            <h3 class="text-secondary">Rapports et Exports</h3>
                        </div>
                        <span class="badge bg-secondary fs-6">PDF/CSV</span>
                    </div>
                    <p class="text-muted mb-4">Génération de rapports détaillés, exports PDF et CSV</p>
                    <div class="d-flex gap-2">
                        <a href="rapports.php" class="btn btn-secondary btn-module">
                            <i class="fas fa-file-alt"></i> Voir les rapports
                        </a>
                        <a href="rapports.php" class="btn btn-outline-secondary btn-module">
                            <i class="fas fa-download"></i> Exporter
                        </a>
                    </div>
                </div>
            </div>

            <!-- Gestion d'Équipe -->
            <div class="col-lg-6">
                <div class="module-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <i class="fas fa-users-cog module-icon text-dark"></i>
                            <h3 class="text-dark">Gestion d'Équipe</h3>
                        </div>
                        <span class="badge bg-dark fs-6">Admin</span>
                    </div>
                    <p class="text-muted mb-4">Gérez les accès, rôles et permissions de votre équipe</p>
                    <div class="d-flex gap-2">
                        <a href="gestion_equipe.php" class="btn btn-dark btn-module">
                            <i class="fas fa-users-cog"></i> Gérer l'équipe
                        </a>
                        <a href="gestion_equipe.php" class="btn btn-outline-dark btn-module">
                            <i class="fas fa-user-plus"></i> Ajouter membre
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Liens rapides -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="module-card">
                    <h4><i class="fas fa-tachometer-alt text-primary me-2"></i> Accès rapides</h4>
                    <div class="row">
                        <div class="col-md-3">
                            <a href="dashboard_fixed.php" class="btn btn-outline-primary w-100 mb-2 btn-module">
                                <i class="fas fa-chart-line me-2"></i> Dashboard complet
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="convertisseur_devises.php" class="btn btn-outline-success w-100 mb-2 btn-module">
                                <i class="fas fa-exchange-alt me-2"></i> Convertisseur devises
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="test_system.php" class="btn btn-outline-info w-100 mb-2 btn-module">
                                <i class="fas fa-clipboard-check me-2"></i> Test du système
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="repair_database.php" class="btn btn-outline-warning w-100 mb-2 btn-module">
                                <i class="fas fa-tools me-2"></i> Réparation DB
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-5 pt-4 border-top">
            <div class="row">
                <?php foreach (app_footer_columns() as $col): ?>
                    <div class="col-md-4">
                        <h6 class="text-muted"><?= htmlspecialchars($col['title']) ?></h6>
                        <?php foreach ($col['lines'] as $line): ?>
                            <p class="small text-muted mb-1"><?= htmlspecialchars($line) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <hr class="my-3">
            <p class="small text-muted mb-0">
                <?= htmlspecialchars(app_legal_notice()) ?>
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- PWA Scripts -->
    <script src="/pwa-manager.js"></script>
    <script>
        // Initialiser le gestionnaire PWA
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof pwaManager !== 'undefined') {
                console.log('PWA Manager initialisé');
                
                // Afficher les statistiques PWA dans la console
                const stats = pwaManager.getStats();
                console.log('Statistiques PWA:', stats);
            }
        });
        
        // Fonction pour afficher les informations PWA
        function showPWAInfo() {
            if (typeof pwaManager !== 'undefined') {
                const stats = pwaManager.getStats();
                alert(`Statistiques PWA:
• En ligne: ${stats.isOnline ? 'Oui' : 'Non'}
• Installée: ${stats.isInstalled ? 'Oui' : 'Non'}
• Données en attente: ${stats.pendingDataCount}
• Cache supporté: ${stats.cacheSupported ? 'Oui' : 'Non'}
• Service Worker: ${stats.serviceWorkerSupported ? 'Oui' : 'Non'}`);
            }
        }
        
        // Fonction pour vider le cache
        function clearPWACache() {
            if (typeof pwaManager !== 'undefined') {
                pwaManager.clearCache();
                alert('Cache PWA vidé avec succès !');
            }
        }
    </script>
</body>
</html> 