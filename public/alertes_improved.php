<?php
// Inclure la configuration sécurisée
require_once 'config_infinityfree.php';

// Traitement du changement de devise
if (isset($_POST['changer_devise'])) {
    setDeviseActuelle($_POST['devise']);
    header("Location: alertes_improved.php");
    exit;
}

// Connexion à la base de données
$db = connectDB();

// Traitement des actions avec redirection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $success = false;
        $message = '';
        
        switch ($_POST['action']) {
            case 'add':
                if ($db) {
                    $stmt = $db->prepare("
                        INSERT INTO alertes (type, message, critique, statut, created_at, updated_at)
                        VALUES (?, ?, ?, 'active', NOW(), NOW())
                    ");
                    $success = $stmt->execute([
                        $_POST['type'],
                        $_POST['description'],
                        $_POST['priorite'] === 'critique' ? 1 : 0
                    ]);
                    $message = $success ? "Alerte créée avec succès !" : "Erreur lors de la création";
                }
                break;
                
            case 'edit':
                if ($db) {
                    $stmt = $db->prepare("
                        UPDATE alertes 
                        SET type = ?, message = ?, critique = ?, statut = ?, updated_at = NOW()
                        WHERE id = ?
                    ");
                    $success = $stmt->execute([
                        $_POST['type'],
                        $_POST['description'],
                        $_POST['priorite'] === 'critique' ? 1 : 0,
                        $_POST['statut'],
                        $_POST['id']
                    ]);
                    $message = $success ? "Alerte modifiée avec succès !" : "Erreur lors de la modification";
                }
                break;
                
            case 'delete':
                if ($db) {
                    $stmt = $db->prepare("DELETE FROM alertes WHERE id = ?");
                    $success = $stmt->execute([$_POST['id']]);
                    $message = $success ? "Alerte supprimée avec succès !" : "Erreur lors de la suppression";
                }
                break;
                
            case 'resolve':
                if ($db) {
                    $stmt = $db->prepare("UPDATE alertes SET statut = 'resolue', updated_at = NOW() WHERE id = ?");
                    $success = $stmt->execute([$_POST['id']]);
                    $message = $success ? "Alerte marquée comme résolue !" : "Erreur lors de la résolution";
                }
                break;
        }
        
        // Redirection avec message
        $status = $success ? 'success' : 'error';
        header("Location: alertes_improved.php?status=$status&message=" . urlencode($message));
        exit;
    }
}

// Détection automatique des alertes
function detecterAlertesAutomatiques($db) {
    if (!$db) return;
    
    try {
        // Vérifier si les colonnes nécessaires existent
        $stmt = $db->query("SHOW COLUMNS FROM alertes LIKE 'reference_id'");
        $referenceIdExists = $stmt->rowCount() > 0;
        
        $stmt = $db->query("SHOW COLUMNS FROM alertes LIKE 'titre'");
        $titreExists = $stmt->rowCount() > 0;
        
        if (!$referenceIdExists || !$titreExists) {
            // Si les colonnes n'existent pas, on ne peut pas créer d'alertes automatiques
            return;
        }
        
        // Alertes de stock en rupture
        $stmt = $db->query("
            SELECT id, produit, quantite FROM stocks 
            WHERE quantite <= 10 
            AND id NOT IN (SELECT reference_id FROM alertes WHERE type = 'stock_rupture' AND statut = 'active')
        ");
        $ruptures = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($ruptures as $rupture) {
            $stmt = $db->prepare("
                INSERT INTO alertes (titre, description, type, priorite, statut, reference_id, date_creation, created_at, updated_at)
                VALUES (?, ?, 'stock_rupture', 'haute', 'active', ?, NOW(), NOW(), NOW())
            ");
            $stmt->execute([
                "Rupture de stock : " . $rupture['produit'],
                "Le produit {$rupture['produit']} est en rupture (quantité: {$rupture['quantite']})",
                $rupture['id']
            ]);
        }
        
        // Alertes de péremption
        $stmt = $db->query("
            SELECT id, produit, date_peremption FROM stocks 
            WHERE date_peremption IS NOT NULL 
            AND date_peremption <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
            AND date_peremption > CURDATE()
            AND id NOT IN (SELECT reference_id FROM alertes WHERE type = 'stock_peremption' AND statut = 'active')
        ");
        $peremptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($peremptions as $peremption) {
            $jours = ceil((strtotime($peremption['date_peremption']) - time()) / (24 * 3600));
            $stmt = $db->prepare("
                INSERT INTO alertes (titre, description, type, priorite, statut, reference_id, date_creation, date_echeance, created_at, updated_at)
                VALUES (?, ?, 'stock_peremption', 'moyenne', 'active', ?, NOW(), ?, NOW(), NOW())
            ");
            $stmt->execute([
                "Péremption proche : " . $peremption['produit'],
                "Le produit {$peremption['produit']} expire dans {$jours} jour(s)",
                $peremption['id'],
                $peremption['date_peremption']
            ]);
        }
        
        // Alertes d'activités en retard
        $stmt = $db->query("
            SELECT id, titre, date FROM activites 
            WHERE date < CURDATE() 
            AND statut = 'planifie'
            AND id NOT IN (SELECT reference_id FROM alertes WHERE type = 'activite_retard' AND statut = 'active')
        ");
        $retards = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($retards as $retard) {
            $stmt = $db->prepare("
                INSERT INTO alertes (titre, description, type, priorite, statut, reference_id, date_creation, created_at, updated_at)
                VALUES (?, ?, 'activite_retard', 'haute', 'active', ?, NOW(), NOW(), NOW())
            ");
            $stmt->execute([
                "Activité en retard : " . $retard['titre'],
                "L'activité {$retard['titre']} était prévue le " . date('d/m/Y', strtotime($retard['date'])),
                $retard['id']
            ]);
        }
    } catch (PDOException $e) {
        // En cas d'erreur, on log mais on ne fait pas planter l'application
        error_log("Erreur lors de la détection automatique des alertes: " . $e->getMessage());
    }
}

// Exécuter la détection automatique
if ($db) {
    // Vérifier si les colonnes nécessaires existent avant d'exécuter
    try {
        $stmt = $db->query("SHOW COLUMNS FROM alertes LIKE 'reference_id'");
        if ($stmt->rowCount() > 0) {
            detecterAlertesAutomatiques($db);
        } else {
            // Si les colonnes n'existent pas, on ne fait rien pour éviter l'erreur
            error_log("Colonnes manquantes dans la table alertes - détection automatique désactivée");
        }
    } catch (PDOException $e) {
        error_log("Erreur lors de la vérification des colonnes alertes: " . $e->getMessage());
    }
}

// Récupération des alertes
function getAlertes($db) {
    if (!$db) return [];
    
    try {
        // Vérifier si les colonnes nécessaires existent
        $stmt = $db->query("SHOW COLUMNS FROM alertes LIKE 'reference_id'");
        $referenceIdExists = $stmt->rowCount() > 0;
        
        $stmt = $db->query("SHOW COLUMNS FROM alertes LIKE 'titre'");
        $titreExists = $stmt->rowCount() > 0;
        
        if ($referenceIdExists && $titreExists) {
            // Requête complète avec toutes les colonnes
            $stmt = $db->query("
                SELECT a.*, 
                       CASE 
                           WHEN a.type = 'stock_rupture' THEN s.produit
                           WHEN a.type = 'stock_peremption' THEN s.produit
                           WHEN a.type = 'activite_retard' THEN act.titre
                           ELSE NULL
                       END as reference_nom
                FROM alertes a
                LEFT JOIN stocks s ON a.reference_id = s.id AND a.type IN ('stock_rupture', 'stock_peremption')
                LEFT JOIN activites act ON a.reference_id = act.id AND a.type = 'activite_retard'
                ORDER BY 
                    CASE a.priorite 
                        WHEN 'critique' THEN 1 
                        WHEN 'haute' THEN 2 
                        WHEN 'moyenne' THEN 3 
                        WHEN 'basse' THEN 4 
                    END,
                    a.date_creation DESC
            ");
        } else {
            // Requête simplifiée sans les nouvelles colonnes
            $stmt = $db->query("
                SELECT * FROM alertes 
                ORDER BY created_at DESC
            ");
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur lors de la récupération des alertes: " . $e->getMessage());
        return [];
    }
}

// Récupération des statistiques
function getAlerteStats($db) {
    if (!$db) return [];
    
    $stats = [];
    
    // Total des alertes
    $stmt = $db->query("SELECT COUNT(*) as total FROM alertes");
    $stats['total'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Alertes actives
    $stmt = $db->query("SELECT COUNT(*) as total FROM alertes WHERE statut = 'active'");
    $stats['actives'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Alertes critiques
    $stmt = $db->query("SELECT COUNT(*) as total FROM alertes WHERE priorite = 'critique' AND statut = 'active'");
    $stats['critiques'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Alertes aujourd'hui
    $stmt = $db->query("SELECT COUNT(*) as total FROM alertes WHERE DATE(date_creation) = CURDATE()");
    $stats['aujourdhui'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    return $stats;
}

$alertes = getAlertes($db);
$stats = getAlerteStats($db);
$dbStatus = $db ? '✅ Connecté' : '❌ Non connecté';
$devise_actuelle = getDeviseActuelle();

// Récupération des messages
$status = $_GET['status'] ?? '';
$message = $_GET['message'] ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Système d'Alertes - Ferme d'Élevage</title>
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
        .table-container {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        .stat-card {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        .alerte-critique {
            border-left: 4px solid #dc3545;
            background: #f8d7da;
        }
        .alerte-haute {
            border-left: 4px solid #fd7e14;
            background: #fff3cd;
        }
        .alerte-moyenne {
            border-left: 4px solid #ffc107;
            background: #fff8e1;
        }
        .alerte-basse {
            border-left: 4px solid #28a745;
            background: #d4edda;
        }
        .alerte-resolue {
            border-left: 4px solid #6c757d;
            background: #f8f9fa;
            opacity: 0.7;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="display-5 text-primary">
                    <i class="fas fa-exclamation-triangle"></i> Système d'Alertes
                </h1>
                <p class="text-muted">Surveillance et gestion des alertes automatiques</p>
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

        <!-- Statut DB -->
        <div class="alert alert-info">
            <strong>Statut de la base de données :</strong> <?= $dbStatus ?>
            <br><small>Les alertes automatiques sont détectées à chaque chargement de la page</small>
        </div>

        <!-- Messages -->
        <?php if ($message): ?>
            <div class="alert alert-<?= $status === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show">
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Statistiques -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card text-center">
                    <i class="fas fa-bell fa-2x mb-2"></i>
                    <h3><?= $stats['total'] ?? 0 ?></h3>
                    <p class="mb-0">Total alertes</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card text-center">
                    <i class="fas fa-exclamation-circle fa-2x mb-2"></i>
                    <h3><?= $stats['actives'] ?? 0 ?></h3>
                    <p class="mb-0">Alertes actives</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card text-center">
                    <i class="fas fa-fire fa-2x mb-2"></i>
                    <h3><?= $stats['critiques'] ?? 0 ?></h3>
                    <p class="mb-0">Critiques</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card text-center">
                    <i class="fas fa-calendar-day fa-2x mb-2"></i>
                    <h3><?= $stats['aujourdhui'] ?? 0 ?></h3>
                    <p class="mb-0">Aujourd'hui</p>
                </div>
            </div>
        </div>

        <!-- Bouton Ajouter -->
        <div class="mb-4">
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addAlerteModal" onclick="resetAddAlerteForm()">
                <i class="fas fa-plus"></i> Créer une alerte manuelle
            </button>
        </div>

        <!-- Tableau des alertes -->
        <div class="table-container">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="fas fa-list"></i> Liste des alertes (<?= count($alertes) ?>)</h4>
                <input type="search" class="form-control" id="searchAlertes" placeholder="Rechercher..." style="max-width: 280px;">
            </div>
            
            <?php if (empty($alertes)): ?>
                <div class="text-center py-4">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <p class="text-muted">Aucune alerte active</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Titre</th>
                                <th>Type</th>
                                <th>Priorité</th>
                                <th>Statut</th>
                                <th>Date création</th>
                                <th>Échéance</th>
                                <th>Référence</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($alertes as $alerte): ?>
                                <tr class="alerte-<?= $alerte['statut'] === 'resolue' ? 'resolue' : $alerte['priorite'] ?>">
                                    <td>
                                        <strong><?= htmlspecialchars($alerte['titre']) ?></strong>
                                        <?php if ($alerte['description']): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($alerte['description']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $alerte['type'] === 'stock_rupture' ? 'danger' : ($alerte['type'] === 'stock_peremption' ? 'warning' : ($alerte['type'] === 'activite_retard' ? 'info' : 'secondary')) ?>">
                                            <?= ucfirst(str_replace('_', ' ', $alerte['type'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $alerte['priorite'] === 'critique' ? 'danger' : ($alerte['priorite'] === 'haute' ? 'warning' : ($alerte['priorite'] === 'moyenne' ? 'info' : 'success')) ?>">
                                            <?= ucfirst($alerte['priorite']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $alerte['statut'] === 'active' ? 'primary' : 'success' ?>">
                                            <?= ucfirst($alerte['statut']) ?>
                                        </span>
                                    </td>
                                    <td><?= date('d/m/Y H:i', strtotime($alerte['date_creation'])) ?></td>
                                    <td>
                                        <?php if ($alerte['date_echeance']): ?>
                                            <?= date('d/m/Y', strtotime($alerte['date_echeance'])) ?>
                                            <?php if (strtotime($alerte['date_echeance']) <= time()): ?>
                                                <span class="badge bg-danger ms-1">En retard</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= $alerte['reference_nom'] ? htmlspecialchars($alerte['reference_nom']) : '-' ?>
                                    </td>
                                    <td>
                                        <?php if ($alerte['statut'] === 'active'): ?>
                                            <button class="btn btn-sm btn-outline-success" onclick="resolveAlerte(<?= $alerte['id'] ?>)">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        <?php endif; ?>
                                        <button class="btn btn-sm btn-outline-primary" onclick="editAlerte(<?= htmlspecialchars(json_encode($alerte)) ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteAlerte(<?= $alerte['id'] ?>, '<?= htmlspecialchars($alerte['titre']) ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal Ajouter Alerte -->
    <div class="modal fade" id="addAlerteModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus"></i> Créer une alerte manuelle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">Titre *</label>
                                    <input type="text" class="form-control" name="titre" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Type *</label>
                                    <select class="form-select" name="type" required>
                                        <option value="">Choisir...</option>
                                        <option value="maintenance">Maintenance</option>
                                        <option value="securite">Sécurité</option>
                                        <option value="sante">Santé animale</option>
                                        <option value="administratif">Administratif</option>
                                        <option value="autre">Autre</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Priorité *</label>
                                    <select class="form-select" name="priorite" required>
                                        <option value="">Choisir...</option>
                                        <option value="critique">Critique</option>
                                        <option value="haute">Haute</option>
                                        <option value="moyenne">Moyenne</option>
                                        <option value="basse">Basse</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Date d'échéance</label>
                                    <input type="date" class="form-control" name="date_echeance">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-success">Créer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Éditer Alerte -->
    <div class="modal fade" id="editAlerteModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit"></i> Modifier l'alerte</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">Titre *</label>
                                    <input type="text" class="form-control" name="titre" id="edit_titre" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Type *</label>
                                    <select class="form-select" name="type" id="edit_type" required>
                                        <option value="">Choisir...</option>
                                        <option value="maintenance">Maintenance</option>
                                        <option value="securite">Sécurité</option>
                                        <option value="sante">Santé animale</option>
                                        <option value="administratif">Administratif</option>
                                        <option value="autre">Autre</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="edit_description" rows="3"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Priorité *</label>
                                    <select class="form-select" name="priorite" id="edit_priorite" required>
                                        <option value="">Choisir...</option>
                                        <option value="critique">Critique</option>
                                        <option value="haute">Haute</option>
                                        <option value="moyenne">Moyenne</option>
                                        <option value="basse">Basse</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Statut *</label>
                                    <select class="form-select" name="statut" id="edit_statut" required>
                                        <option value="active">Active</option>
                                        <option value="resolue">Résolue</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Date d'échéance</label>
                                    <input type="date" class="form-control" name="date_echeance" id="edit_date_echeance">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Modifier</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Supprimer Alerte -->
    <div class="modal fade" id="deleteAlerteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-trash"></i> Confirmer la suppression</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="delete_id">
                    <div class="modal-body">
                        <p>Êtes-vous sûr de vouloir supprimer l'alerte <strong id="delete_nom"></strong> ?</p>
                        <p class="text-danger">Cette action est irréversible.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-danger">Supprimer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Filtre de recherche côté client
        (function(){
            const input = document.getElementById('searchAlertes');
            if (!input) return;
            input.addEventListener('input', () => {
                const q = input.value.toLowerCase();
                document.querySelectorAll('table tbody tr').forEach(tr => {
                    tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
                });
            });
        })();
        function resetAddAlerteForm() {
            const form = document.querySelector('#addAlerteModal form');
            if (form) form.reset();
        }
        function editAlerte(alerte) {
            document.getElementById('edit_id').value = alerte.id;
            document.getElementById('edit_titre').value = alerte.titre;
            document.getElementById('edit_type').value = alerte.type;
            document.getElementById('edit_description').value = alerte.description || '';
            document.getElementById('edit_priorite').value = alerte.priorite;
            document.getElementById('edit_statut').value = alerte.statut;
            document.getElementById('edit_date_echeance').value = alerte.date_echeance || '';
            
            new bootstrap.Modal(document.getElementById('editAlerteModal')).show();
        }
        
        function deleteAlerte(id, nom) {
            document.getElementById('delete_id').value = id;
            document.getElementById('delete_nom').textContent = nom;
            new bootstrap.Modal(document.getElementById('deleteAlerteModal')).show();
        }
        
        function resolveAlerte(id) {
            if (confirm('Marquer cette alerte comme résolue ?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="resolve">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html> 