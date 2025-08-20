<?php
// Inclure la configuration sécurisée
require_once 'config_sqlite.php';

// Traitement du changement de devise
if (isset($_POST['changer_devise'])) {
    setDeviseActuelle($_POST['devise']);
    header("Location: activites_improved.php");
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
                        INSERT INTO activites (titre, description, date, heure_debut, heure_fin, type, statut, employe_id, animal_id, created_at, updated_at)
                        VALUES (?, ?, ?, ?, ?, ?, 'planifie', ?, ?, NOW(), NOW())
                    ");
                    $success = $stmt->execute([
                        $_POST['titre'],
                        $_POST['description'],
                        $_POST['date'],
                        $_POST['heure_debut'] ?: null,
                        $_POST['heure_fin'] ?: null,
                        $_POST['type'],
                        $_POST['employe_id'] ?: null,
                        $_POST['animal_id'] ?: null
                    ]);
                    $message = $success ? "Activité ajoutée avec succès !" : "Erreur lors de l'ajout";
                }
                break;
                
            case 'edit':
                if ($db) {
                    $stmt = $db->prepare("
                        UPDATE activites 
                        SET titre = ?, description = ?, date = ?, heure_debut = ?, heure_fin = ?, type = ?, statut = ?, employe_id = ?, animal_id = ?, updated_at = NOW()
                        WHERE id = ?
                    ");
                    $success = $stmt->execute([
                        $_POST['titre'],
                        $_POST['description'],
                        $_POST['date'],
                        $_POST['heure_debut'] ?: null,
                        $_POST['heure_fin'] ?: null,
                        $_POST['type'],
                        $_POST['statut'],
                        $_POST['employe_id'] ?: null,
                        $_POST['animal_id'] ?: null,
                        $_POST['id']
                    ]);
                    $message = $success ? "Activité modifiée avec succès !" : "Erreur lors de la modification";
                }
                break;
                
            case 'delete':
                if ($db) {
                    $stmt = $db->prepare("DELETE FROM activites WHERE id = ?");
                    $success = $stmt->execute([$_POST['id']]);
                    $message = $success ? "Activité supprimée avec succès !" : "Erreur lors de la suppression";
                }
                break;
        }
        
        // Redirection avec message
        $status = $success ? 'success' : 'error';
        header("Location: activites_improved.php?status=$status&message=" . urlencode($message));
        exit;
    }
}

// Récupération des activités
function getActivites($db) {
    if (!$db) return [];
    
    $stmt = $db->query("
        SELECT a.*, 
               e.nom as employe_nom, e.prenom as employe_prenom,
               an.nom as animal_nom, an.espece as animal_espece
        FROM activites a
        LEFT JOIN employes e ON a.employe_id = e.id
        LEFT JOIN animaux an ON a.animal_id = an.id
        ORDER BY a.date DESC, a.heure_debut DESC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Récupération des employés pour le formulaire
function getEmployes($db) {
    if (!$db) return [];
    
    $stmt = $db->query("SELECT id, nom, prenom FROM employes WHERE statut = 'actif' ORDER BY nom");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Récupération des animaux pour le formulaire
function getAnimaux($db) {
    if (!$db) return [];
    
    $stmt = $db->query("SELECT id, nom, espece FROM animaux WHERE statut = 'actif' ORDER BY nom");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Récupération des statistiques
function getActiviteStats($db) {
    if (!$db) return [];
    
    $stats = [];
    
    // Total des activités
    $stmt = $db->query("SELECT COUNT(*) as total FROM activites");
    $stats['total'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Activités aujourd'hui
    $stmt = $db->query("SELECT COUNT(*) as total FROM activites WHERE date = CURDATE()");
    $stats['aujourdhui'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Activités planifiées
    $stmt = $db->query("SELECT COUNT(*) as total FROM activites WHERE statut = 'planifie'");
    $stats['planifiees'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Activités terminées
    $stmt = $db->query("SELECT COUNT(*) as total FROM activites WHERE statut = 'termine'");
    $stats['terminees'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    return $stats;
}

$activites = getActivites($db);
$employes = getEmployes($db);
$animaux = getAnimaux($db);
$stats = getActiviteStats($db);
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
    <title>Gestion des Activités - Ferme d'Élevage</title>
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
        .activity-planifiee {
            border-left: 4px solid #007bff;
            background: #f8f9fa;
        }
        .activity-en-cours {
            border-left: 4px solid #ffc107;
            background: #fff3cd;
        }
        .activity-terminee {
            border-left: 4px solid #28a745;
            background: #d4edda;
        }
        .activity-annulee {
            border-left: 4px solid #dc3545;
            background: #f8d7da;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="display-5 text-primary">
                    <i class="fas fa-tasks"></i> Gestion des Activités
                </h1>
                <p class="text-muted">Planifiez et suivez les activités de la ferme</p>
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
                    <i class="fas fa-tasks fa-2x mb-2"></i>
                    <h3><?= $stats['total'] ?? 0 ?></h3>
                    <p class="mb-0">Total activités</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card text-center">
                    <i class="fas fa-calendar-day fa-2x mb-2"></i>
                    <h3><?= $stats['aujourdhui'] ?? 0 ?></h3>
                    <p class="mb-0">Aujourd'hui</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card text-center">
                    <i class="fas fa-clock fa-2x mb-2"></i>
                    <h3><?= $stats['planifiees'] ?? 0 ?></h3>
                    <p class="mb-0">Planifiées</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card text-center">
                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                    <h3><?= $stats['terminees'] ?? 0 ?></h3>
                    <p class="mb-0">Terminées</p>
                </div>
            </div>
        </div>

        <!-- Bouton Ajouter -->
        <div class="mb-4">
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addActiviteModal">
                <i class="fas fa-plus"></i> Ajouter une activité
            </button>
        </div>

        <!-- Tableau des activités -->
        <div class="table-container">
            <h4><i class="fas fa-list"></i> Planning des activités (<?= count($activites) ?>)</h4>
            
            <?php if (empty($activites)): ?>
                <div class="text-center py-4">
                    <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Aucune activité planifiée</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Titre</th>
                                <th>Type</th>
                                <th>Date</th>
                                <th>Heures</th>
                                <th>Responsable</th>
                                <th>Animal concerné</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($activites as $activite): ?>
                                <tr class="activity-<?= $activite['statut'] ?>">
                                    <td>
                                        <strong><?= htmlspecialchars($activite['titre']) ?></strong>
                                        <?php if ($activite['description']): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($activite['description']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $activite['type'] === 'soins' ? 'primary' : ($activite['type'] === 'alimentation' ? 'success' : ($activite['type'] === 'reproduction' ? 'warning' : 'secondary')) ?>">
                                            <?= ucfirst($activite['type']) ?>
                                        </span>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($activite['date'])) ?></td>
                                    <td>
                                        <?php if ($activite['heure_debut']): ?>
                                            <?= substr($activite['heure_debut'], 0, 5) ?>
                                            <?php if ($activite['heure_fin']): ?>
                                                - <?= substr($activite['heure_fin'], 0, 5) ?>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= $activite['employe_nom'] ? htmlspecialchars($activite['employe_nom'] . ' ' . $activite['employe_prenom']) : '-' ?>
                                    </td>
                                    <td>
                                        <?= $activite['animal_nom'] ? htmlspecialchars($activite['animal_nom'] . ' (' . $activite['animal_espece'] . ')') : '-' ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $activite['statut'] === 'termine' ? 'success' : ($activite['statut'] === 'en_cours' ? 'warning' : ($activite['statut'] === 'annule' ? 'danger' : 'primary')) ?>">
                                            <?= ucfirst($activite['statut']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="editActivite(<?= htmlspecialchars(json_encode($activite)) ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteActivite(<?= $activite['id'] ?>, '<?= htmlspecialchars($activite['titre']) ?>')">
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

    <!-- Modal Ajouter Activité -->
    <div class="modal fade" id="addActiviteModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus"></i> Ajouter une activité</h5>
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
                                        <option value="soins">Soins</option>
                                        <option value="alimentation">Alimentation</option>
                                        <option value="reproduction">Reproduction</option>
                                        <option value="maintenance">Maintenance</option>
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
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Date *</label>
                                    <input type="date" class="form-control" name="date" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Heure de début</label>
                                    <input type="time" class="form-control" name="heure_debut">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Heure de fin</label>
                                    <input type="time" class="form-control" name="heure_fin">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Responsable</label>
                                    <select class="form-select" name="employe_id">
                                        <option value="">Aucun</option>
                                        <?php foreach ($employes as $employe): ?>
                                            <option value="<?= $employe['id'] ?>">
                                                <?= htmlspecialchars($employe['nom'] . ' ' . $employe['prenom']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Animal concerné</label>
                                    <select class="form-select" name="animal_id">
                                        <option value="">Aucun</option>
                                        <?php foreach ($animaux as $animal): ?>
                                            <option value="<?= $animal['id'] ?>">
                                                <?= htmlspecialchars($animal['nom'] . ' (' . $animal['espece'] . ')') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-success">Ajouter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Éditer Activité -->
    <div class="modal fade" id="editActiviteModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit"></i> Modifier l'activité</h5>
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
                                        <option value="soins">Soins</option>
                                        <option value="alimentation">Alimentation</option>
                                        <option value="reproduction">Reproduction</option>
                                        <option value="maintenance">Maintenance</option>
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
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Date *</label>
                                    <input type="date" class="form-control" name="date" id="edit_date" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Heure de début</label>
                                    <input type="time" class="form-control" name="heure_debut" id="edit_heure_debut">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Heure de fin</label>
                                    <input type="time" class="form-control" name="heure_fin" id="edit_heure_fin">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Statut *</label>
                                    <select class="form-select" name="statut" id="edit_statut" required>
                                        <option value="planifie">Planifié</option>
                                        <option value="en_cours">En cours</option>
                                        <option value="termine">Terminé</option>
                                        <option value="annule">Annulé</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Responsable</label>
                                    <select class="form-select" name="employe_id" id="edit_employe_id">
                                        <option value="">Aucun</option>
                                        <?php foreach ($employes as $employe): ?>
                                            <option value="<?= $employe['id'] ?>">
                                                <?= htmlspecialchars($employe['nom'] . ' ' . $employe['prenom']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Animal concerné</label>
                                    <select class="form-select" name="animal_id" id="edit_animal_id">
                                        <option value="">Aucun</option>
                                        <?php foreach ($animaux as $animal): ?>
                                            <option value="<?= $animal['id'] ?>">
                                                <?= htmlspecialchars($animal['nom'] . ' (' . $animal['espece'] . ')') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
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

    <!-- Modal Supprimer Activité -->
    <div class="modal fade" id="deleteActiviteModal" tabindex="-1">
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
                        <p>Êtes-vous sûr de vouloir supprimer l'activité <strong id="delete_nom"></strong> ?</p>
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
        function editActivite(activite) {
            document.getElementById('edit_id').value = activite.id;
            document.getElementById('edit_titre').value = activite.titre;
            document.getElementById('edit_type').value = activite.type;
            document.getElementById('edit_description').value = activite.description || '';
            document.getElementById('edit_date').value = activite.date;
            document.getElementById('edit_heure_debut').value = activite.heure_debut || '';
            document.getElementById('edit_heure_fin').value = activite.heure_fin || '';
            document.getElementById('edit_statut').value = activite.statut;
            document.getElementById('edit_employe_id').value = activite.employe_id || '';
            document.getElementById('edit_animal_id').value = activite.animal_id || '';
            
            new bootstrap.Modal(document.getElementById('editActiviteModal')).show();
        }
        
        function deleteActivite(id, nom) {
            document.getElementById('delete_id').value = id;
            document.getElementById('delete_nom').textContent = nom;
            new bootstrap.Modal(document.getElementById('deleteActiviteModal')).show();
        }
    </script>
</body>
</html> 