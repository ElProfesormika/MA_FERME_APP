<?php
// Tableau de bord principal - Ferme d'Élevage
// Utilise la configuration sécurisée pour InfinityFree

// Inclure la configuration sécurisée
require_once 'config_infinityfree_secure.php';

// Vérifier le statut de la base de données
$db_status = getDBStatus();

// Obtenir les statistiques rapides
$stats = getQuickStats();

// Définir le titre de la page
$page_title = "Tableau de Bord - " . $APP_CONFIG['name'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .navbar {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px);
        }
        .main-container {
            padding: 20px;
            margin-top: 20px;
        }
        .stats-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 25px;
            margin-bottom: 20px;
            transition: transform 0.3s ease;
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
        .stats-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }
        .stats-number {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
        }
        .module-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 25px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
            border: none;
        }
        .module-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }
        .module-icon {
            font-size: 3rem;
            margin-bottom: 15px;
        }
        .status-badge {
            position: absolute;
            top: 15px;
            right: 15px;
        }
        .quick-actions {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 25px;
            margin-bottom: 20px;
        }
        .action-btn {
            display: block;
            width: 100%;
            padding: 15px;
            margin: 10px 0;
            border-radius: 10px;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        .action-btn:hover {
            transform: translateY(-2px);
            text-decoration: none;
        }
        .action-btn.primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .action-btn.secondary {
            background: #f8f9fa;
            color: #495057;
            border-color: #dee2e6;
        }
        .action-btn.danger {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
        }
        .action-btn.success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }
        .footer {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 20px 0;
            margin-top: 40px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-home"></i> <?= htmlspecialchars($APP_CONFIG['name']) ?>
            </a>
            
            <div class="navbar-nav ms-auto">
                <span class="navbar-text">
                    <i class="fas fa-clock"></i> <?= date('d/m/Y H:i') ?>
                </span>
            </div>
        </div>
    </nav>

    <div class="main-container">
        <div class="container">
            <!-- En-tête -->
            <div class="row mb-4">
                <div class="col-12">
                    <h1 class="text-white text-center mb-3">
                        <i class="fas fa-tachometer-alt"></i> Tableau de Bord
                    </h1>
                    <p class="text-white text-center lead">
                        <?= htmlspecialchars($APP_CONFIG['slogan']) ?>
                    </p>
                </div>
            </div>

            <!-- Statut de la base de données -->
            <?php if ($db_status['status'] !== 'ok'): ?>
                <div class="alert alert-<?= $db_status['status'] === 'error' ? 'danger' : 'warning' ?> alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Attention :</strong> <?= htmlspecialchars($db_status['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Statistiques rapides -->
            <?php if ($stats): ?>
                <div class="row mb-4">
                    <div class="col-md-3 col-sm-6">
                        <div class="stats-card text-center">
                            <div class="stats-icon text-primary">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stats-number"><?= $stats['employes'] ?></div>
                            <div class="text-muted">Employés actifs</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="stats-card text-center">
                            <div class="stats-icon text-success">
                                <i class="fas fa-cow"></i>
                            </div>
                            <div class="stats-number"><?= $stats['animaux'] ?></div>
                            <div class="text-muted">Animaux actifs</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="stats-card text-center">
                            <div class="stats-icon text-info">
                                <i class="fas fa-boxes"></i>
                            </div>
                            <div class="stats-number"><?= $stats['stocks'] ?></div>
                            <div class="text-muted">Produits en stock</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="stats-card text-center">
                            <div class="stats-icon text-warning">
                                <i class="fas fa-bell"></i>
                            </div>
                            <div class="stats-number"><?= $stats['alertes'] ?></div>
                            <div class="text-muted">Alertes actives</div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Modules principaux -->
            <div class="row">
                <div class="col-lg-8">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="module-card position-relative">
                                <div class="module-icon text-primary">
                                    <i class="fas fa-cow"></i>
                                </div>
                                <h4>Gestion des Animaux</h4>
                                <p class="text-muted">Suivi sanitaire, reproduction, et gestion du cheptel</p>
                                <a href="animaux_improved.php" class="btn btn-primary">
                                    <i class="fas fa-arrow-right"></i> Accéder
                                </a>
                                <span class="status-badge">
                                    <span class="badge bg-success">Actif</span>
                                </span>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="module-card position-relative">
                                <div class="module-icon text-success">
                                    <i class="fas fa-users"></i>
                                </div>
                                <h4>Gestion des Employés</h4>
                                <p class="text-muted">Suivi du personnel, planning et affectations</p>
                                <a href="employes_improved.php" class="btn btn-success">
                                    <i class="fas fa-arrow-right"></i> Accéder
                                </a>
                                <span class="status-badge">
                                    <span class="badge bg-success">Actif</span>
                                </span>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="module-card position-relative">
                                <div class="module-icon text-info">
                                    <i class="fas fa-boxes"></i>
                                </div>
                                <h4>Gestion des Stocks</h4>
                                <p class="text-muted">Inventaire, approvisionnements et alertes</p>
                                <a href="stocks_improved.php" class="btn btn-info">
                                    <i class="fas fa-arrow-right"></i> Accéder
                                </a>
                                <span class="status-badge">
                                    <span class="badge bg-success">Actif</span>
                                </span>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="module-card position-relative">
                                <div class="module-icon text-warning">
                                    <i class="fas fa-calendar-check"></i>
                                </div>
                                <h4>Activités & Planning</h4>
                                <p class="text-muted">Planification et suivi des tâches quotidiennes</p>
                                <a href="activites_improved.php" class="btn btn-warning">
                                    <i class="fas fa-arrow-right"></i> Accéder
                                </a>
                                <span class="status-badge">
                                    <span class="badge bg-success">Actif</span>
                                </span>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="module-card position-relative">
                                <div class="module-icon text-danger">
                                    <i class="fas fa-bell"></i>
                                </div>
                                <h4>Système d'Alertes</h4>
                                <p class="text-muted">Notifications et alertes en temps réel</p>
                                <a href="alertes_improved.php" class="btn btn-danger">
                                    <i class="fas fa-arrow-right"></i> Accéder
                                </a>
                                <span class="status-badge">
                                    <span class="badge bg-success">Actif</span>
                                </span>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="module-card position-relative">
                                <div class="module-icon text-secondary">
                                    <i class="fas fa-chart-bar"></i>
                                </div>
                                <h4>Rapports & Statistiques</h4>
                                <p class="text-muted">Analyses, rapports et indicateurs de performance</p>
                                <a href="rapports.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-right"></i> Accéder
                                </a>
                                <span class="status-badge">
                                    <span class="badge bg-success">Actif</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Actions rapides -->
                <div class="col-lg-4">
                    <div class="quick-actions">
                        <h4><i class="fas fa-bolt"></i> Actions Rapides</h4>
                        
                        <a href="gestion_equipe.php" class="action-btn primary">
                            <i class="fas fa-users-cog"></i> Gestion d'Équipe
                        </a>
                        
                        <a href="convertisseur_devises.php" class="action-btn secondary">
                            <i class="fas fa-exchange-alt"></i> Convertisseur de Devises
                        </a>
                        
                        <a href="navigation.php" class="action-btn secondary">
                            <i class="fas fa-compass"></i> Navigation Complète
                        </a>
                        
                        <hr>
                        
                        <h6><i class="fas fa-cog"></i> Configuration</h6>
                        
                        <a href="config_app.php" class="action-btn secondary">
                            <i class="fas fa-sliders-h"></i> Paramètres App
                        </a>
                        
                        <a href="config_devises.php" class="action-btn secondary">
                            <i class="fas fa-coins"></i> Configuration Devises
                        </a>
                        
                        <hr>
                        
                        <h6><i class="fas fa-info-circle"></i> Informations</h6>
                        
                        <div class="text-center">
                            <small class="text-muted">
                                Version : <?= htmlspecialchars($APP_CONFIG['version']) ?><br>
                                Devise : <?= htmlspecialchars($APP_CONFIG['currency']) ?><br>
                                Statut DB : 
                                <span class="badge bg-<?= $db_status['status'] === 'ok' ? 'success' : ($db_status['status'] === 'warning' ? 'warning' : 'danger') ?>">
                                    <?= ucfirst($db_status['status']) ?>
                                </span>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h6><?= htmlspecialchars($APP_CONFIG['name']) ?></h6>
                    <p class="text-muted mb-0"><?= htmlspecialchars($APP_CONFIG['slogan']) ?></p>
                </div>
                <div class="col-md-6 text-end">
                    <p class="text-muted mb-0">
                        <i class="fas fa-clock"></i> 
                        Dernière mise à jour : <?= date('d/m/Y H:i:s') ?>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-refresh des statistiques toutes les 30 secondes
        setInterval(function() {
            location.reload();
        }, 30000);
        
        // Animation des cartes au survol
        document.querySelectorAll('.module-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-10px)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    </script>
</body>
</html>
