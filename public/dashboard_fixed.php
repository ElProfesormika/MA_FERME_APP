<?php
// Inclure la configuration des devises
require_once 'config_devises.php';

// Traitement du changement de devise
if (isset($_POST['changer_devise'])) {
    setDeviseActuelle($_POST['devise']);
    header("Location: dashboard_fixed.php");
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

// Connexion à la base de données
$db = connectDB($config);

// Fonctions pour récupérer les données
function getStats($db) {
    if (!$db) return [];
    
    $stats = [];
    
    // Nombre d'animaux
    $stmt = $db->query("SELECT COUNT(*) as total FROM animaux WHERE statut = 'actif'");
    $stats['animaux'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Nombre d'employés
    $stmt = $db->query("SELECT COUNT(*) as total FROM employes WHERE statut = 'actif'");
    $stats['employes'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Nombre d'alertes actives
    $stmt = $db->query("SELECT COUNT(*) as total FROM alertes WHERE statut = 'active'");
    $stats['alertes'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Nombre de produits en stock
    $stmt = $db->query("SELECT COUNT(*) as total FROM stocks WHERE quantite > 0");
    $stats['stocks'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Valeur totale du stock
    $stmt = $db->query("SELECT SUM(quantite * prix_unitaire) as total FROM stocks");
    $stats['valeur_stock'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?: 0;
    
    return $stats;
}

function getRecentActivities($db, $limit = 5) {
    if (!$db) return [];
    
    $limit = (int)$limit; // Sécuriser la valeur
    $stmt = $db->query("
        SELECT a.*, e.nom as employe_nom, e.prenom as employe_prenom, an.nom as animal_nom
        FROM activites a
        LEFT JOIN employes e ON a.employe_id = e.id
        LEFT JOIN animaux an ON a.animal_id = an.id
        ORDER BY a.date DESC, a.heure_debut DESC
        LIMIT $limit
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getRecentAlerts($db, $limit = 5) {
    if (!$db) return [];
    
    $limit = (int)$limit; // Sécuriser la valeur
    $stmt = $db->query("
        SELECT a.*, an.nom as animal_nom
        FROM alertes a
        LEFT JOIN animaux an ON a.animal_id = an.id
        WHERE a.statut = 'active'
        ORDER BY a.created_at DESC
        LIMIT $limit
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAnimalsBySpecies($db) {
    if (!$db) return [];
    
    $stmt = $db->query("
        SELECT espece, COUNT(*) as total
        FROM animaux
        WHERE statut = 'actif'
        GROUP BY espece
        ORDER BY total DESC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Récupération des données
$stats = getStats($db);
$recentActivities = getRecentActivities($db);
$recentAlerts = getRecentAlerts($db);
$animalsBySpecies = getAnimalsBySpecies($db);

$dbStatus = $db ? '✅ Connecté' : '❌ Non connecté';
$devise_actuelle = getDeviseActuelle();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ferme d'Élevage - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        .stat-card {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .alert-card {
            border-left: 4px solid #dc3545;
            background: #f8f9fa;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 5px;
        }
        .activity-card {
            border-left: 4px solid #28a745;
            background: #f8f9fa;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 5px;
        }
        .chart-container {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="text-center">
                <h1 class="display-4 text-primary">
                    <i class="fas fa-cow"></i> Ferme d'Élevage
                </h1>
                <p class="lead text-muted">Système de gestion complet pour votre ferme d'élevage</p>
                
                <!-- Statut de la base de données -->
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
                <a href="index_final.php" class="btn btn-outline-primary">
                    <i class="fas fa-home"></i> Accueil
                </a>
            </div>
        </div>

        <!-- Statistiques principales -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card text-center">
                    <i class="fas fa-cow fa-2x mb-2"></i>
                    <h3><?= $stats['animaux'] ?? 0 ?></h3>
                    <p class="mb-0">Animaux</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card text-center">
                    <i class="fas fa-users fa-2x mb-2"></i>
                    <h3><?= $stats['employes'] ?? 0 ?></h3>
                    <p class="mb-0">Employés</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card text-center">
                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                    <h3><?= $stats['alertes'] ?? 0 ?></h3>
                    <p class="mb-0">Alertes</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card text-center">
                    <i class="fas fa-boxes fa-2x mb-2"></i>
                    <h3><?= $stats['stocks'] ?? 0 ?></h3>
                    <p class="mb-0">Produits en stock</p>
                </div>
            </div>
        </div>

        <!-- Statistiques financières -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="stat-card text-center">
                    <i class="fas fa-euro-sign fa-2x mb-2"></i>
                    <h3><?= formaterMontantAutomatique($stats['valeur_stock']) ?></h3>
                    <p class="mb-0">Valeur totale du stock</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="stat-card text-center">
                    <i class="fas fa-chart-line fa-2x mb-2"></i>
                    <h3><?= $devise_actuelle ?></h3>
                    <p class="mb-0">Devise actuelle</p>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Graphique des espèces d'animaux -->
            <div class="col-md-6">
                <div class="chart-container">
                    <h4><i class="fas fa-chart-pie"></i> Répartition par espèce</h4>
                    <canvas id="speciesChart" width="400" height="200"></canvas>
                </div>
            </div>
            
            <!-- Alertes récentes -->
            <div class="col-md-6">
                <div class="chart-container">
                    <h4><i class="fas fa-exclamation-triangle"></i> Alertes récentes</h4>
                    <?php if (empty($recentAlerts)): ?>
                        <p class="text-muted">Aucune alerte active</p>
                    <?php else: ?>
                        <?php foreach ($recentAlerts as $alerte): ?>
                            <div class="alert-card">
                                <strong><?= htmlspecialchars($alerte['type'] ?? '') ?></strong>
                                <p class="mb-1"><?= htmlspecialchars($alerte['message'] ?? ($alerte['description'] ?? ($alerte['titre'] ?? ''))) ?></p>
                                <small class="text-muted">
                                    <?= !empty($alerte['animal_nom']) ? 'Animal: ' . htmlspecialchars($alerte['animal_nom']) : '' ?>
                                    <?= !empty($alerte['critique']) ? ' - <span class="text-danger">CRITIQUE</span>' : '' ?>
                                </small>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Activités récentes -->
        <div class="row">
            <div class="col-12">
                <div class="chart-container">
                    <h4><i class="fas fa-tasks"></i> Activités récentes</h4>
                    <?php if (empty($recentActivities)): ?>
                        <p class="text-muted">Aucune activité récente</p>
                    <?php else: ?>
                        <?php foreach ($recentActivities as $activite): ?>
                            <div class="activity-card">
                                <div class="row">
                                    <div class="col-md-8">
                                        <strong><?= htmlspecialchars($activite['titre']) ?></strong>
                                        <p class="mb-1"><?= htmlspecialchars($activite['description']) ?></p>
                                        <small class="text-muted">
                                            <?= $activite['employe_nom'] ? 'Par: ' . htmlspecialchars($activite['employe_nom'] . ' ' . $activite['employe_prenom']) : '' ?>
                                            <?= $activite['animal_nom'] ? ' - Animal: ' . htmlspecialchars($activite['animal_nom']) : '' ?>
                                        </small>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <span class="badge bg-<?= $activite['statut'] === 'termine' ? 'success' : ($activite['statut'] === 'en_cours' ? 'warning' : 'primary') ?>">
                                            <?= ucfirst($activite['statut']) ?>
                                        </span>
                                        <br>
                                        <small class="text-muted">
                                            <?= date('d/m/Y', strtotime($activite['date'])) ?>
                                            <?= $activite['heure_debut'] ? ' - ' . substr($activite['heure_debut'], 0, 5) : '' ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Actions rapides -->
        <div class="row mt-4">
            <div class="col-12">
                <h3>Actions rapides</h3>
                <div class="d-flex flex-wrap gap-2">
                    <a href="animaux_improved.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Ajouter un animal
                    </a>
                    <a href="activites_improved.php" class="btn btn-success">
                        <i class="fas fa-plus"></i> Nouvelle activité
                    </a>
                    <a href="stocks_improved.php" class="btn btn-warning">
                        <i class="fas fa-box"></i> Gérer les stocks
                    </a>
                    <a href="rapports.php" class="btn btn-info">
                        <i class="fas fa-file-pdf"></i> Générer rapport
                    </a>
                    <a href="alertes_improved.php" class="btn btn-danger">
                        <i class="fas fa-bell"></i> Voir les alertes
                    </a>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-5 pt-4 border-top">
            <p class="text-muted">
                <i class="fas fa-code"></i> Développé avec PHP & Bootstrap
                <br>
                <small>Application de gestion de ferme d'élevage - Version 1.0</small>
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Graphique des espèces d'animaux
        const speciesData = <?= json_encode($animalsBySpecies) ?>;
        
        if (speciesData.length > 0) {
            const ctx = document.getElementById('speciesChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: speciesData.map(item => item.espece),
                    datasets: [{
                        data: speciesData.map(item => item.total),
                        backgroundColor: [
                            '#FF6384',
                            '#36A2EB',
                            '#FFCE56',
                            '#4BC0C0',
                            '#9966FF'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }
    </script>
</body>
</html> 