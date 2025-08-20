<?php
// Inclure la configuration sécurisée
require_once 'config_sqlite.php';

// Traitement du changement de devise
if (isset($_POST['changer_devise'])) {
    setDeviseActuelle($_POST['devise']);
    header("Location: employes_improved.php");
    exit;
}

// Connexion à la base de données
$db = connectDB();
$devise_actuelle = getDeviseActuelle();

// Traitement des actions avec redirection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $success = false;
        $message = '';
        
        switch ($_POST['action']) {
            case 'add':
                if ($db) {
                    // Convertir le salaire saisi vers FCFA pour stockage (source: devise actuelle)
                    $salaire_saisi = isset($_POST['salaire']) ? (float)$_POST['salaire'] : 0.0;
                    $salaire_fcfa = ($devise_actuelle === 'FCFA') ? $salaire_saisi : convertirDevise($salaire_saisi, $devise_actuelle, 'FCFA');
                    $stmt = $db->prepare("
                        INSERT INTO employes (nom, prenom, poste, date_embauche, salaire, telephone, email, adresse, statut, created_at, updated_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'actif', NOW(), NOW())
                    ");
                    $success = $stmt->execute([
                        $_POST['nom'],
                        $_POST['prenom'],
                        $_POST['poste'],
                        $_POST['date_embauche'],
                        $salaire_fcfa,
                        $_POST['telephone'],
                        $_POST['email'],
                        $_POST['adresse']
                    ]);
                    $message = $success ? "Employé ajouté avec succès !" : "Erreur lors de l'ajout";
                }
                break;
                
            case 'edit':
                if ($db) {
                    // Convertir le salaire saisi vers FCFA pour stockage (source: devise actuelle)
                    $salaire_saisi = isset($_POST['salaire']) ? (float)$_POST['salaire'] : 0.0;
                    $salaire_fcfa = ($devise_actuelle === 'FCFA') ? $salaire_saisi : convertirDevise($salaire_saisi, $devise_actuelle, 'FCFA');
                    $stmt = $db->prepare("
                        UPDATE employes 
                        SET nom = ?, prenom = ?, poste = ?, date_embauche = ?, salaire = ?, telephone = ?, email = ?, adresse = ?, statut = ?, updated_at = NOW()
                        WHERE id = ?
                    ");
                    $success = $stmt->execute([
                        $_POST['nom'],
                        $_POST['prenom'],
                        $_POST['poste'],
                        $_POST['date_embauche'],
                        $salaire_fcfa,
                        $_POST['telephone'],
                        $_POST['email'],
                        $_POST['adresse'],
                        $_POST['statut'],
                        $_POST['id']
                    ]);
                    $message = $success ? "Employé modifié avec succès !" : "Erreur lors de la modification";
                }
                break;
                
            case 'delete':
                if ($db) {
                    $stmt = $db->prepare("DELETE FROM employes WHERE id = ?");
                    $success = $stmt->execute([$_POST['id']]);
                    $message = $success ? "Employé supprimé avec succès !" : "Erreur lors de la suppression";
                }
                break;
        }
        
        // Redirection avec message
        $status = $success ? 'success' : 'error';
        header("Location: employes_improved.php?status=$status&message=" . urlencode($message));
        exit;
    }
}

// Récupération des employés
function getEmployes($db) {
    if (!$db) return [];
    
    $stmt = $db->query("
        SELECT e.*, 
               COUNT(DISTINCT a.id) as nb_animaux,
               COUNT(DISTINCT act.id) as nb_activites
        FROM employes e
        LEFT JOIN animaux a ON e.id = a.employe_id
        LEFT JOIN activites act ON e.id = act.employe_id
        GROUP BY e.id
        ORDER BY e.nom, e.prenom
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$employes = getEmployes($db);
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
    <title>Gestion des Employés - Ferme d'Élevage</title>
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
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="display-5 text-primary">
                    <i class="fas fa-users"></i> Gestion des Employés
                </h1>
                <p class="text-muted">Gérez votre personnel et leurs responsabilités</p>
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

        <!-- Bouton Ajouter -->
        <div class="mb-4">
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addEmployeModal">
                <i class="fas fa-plus"></i> Ajouter un employé
            </button>
        </div>

        <!-- Tableau des employés -->
        <div class="table-container">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="fas fa-list"></i> Liste des employés (<?= count($employes) ?>)</h4>
                <input type="search" class="form-control" id="searchEmployes" placeholder="Rechercher..." style="max-width: 280px;">
            </div>
            
            <?php if (empty($employes)): ?>
                <div class="text-center py-4">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Aucun employé enregistré</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Nom complet</th>
                                <th>Poste</th>
                                <th>Date d'embauche</th>
                                <th>Salaire</th>
                                <th>Téléphone</th>
                                <th>Email</th>
                                <th>Statut</th>
                                <th>Responsabilités</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($employes as $employe): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($employe['nom'] . ' ' . $employe['prenom']) ?></strong>
                                    </td>
                                    <td><?= htmlspecialchars($employe['poste']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($employe['date_embauche'])) ?></td>
                                    <td><?= formaterMontant(convertirDevise($employe['salaire'], 'FCFA', $devise_actuelle), $devise_actuelle) ?></td>
                                    <td><?= htmlspecialchars($employe['telephone']) ?: '-' ?></td>
                                    <td><?= htmlspecialchars($employe['email']) ?: '-' ?></td>
                                    <td>
                                        <span class="badge bg-<?= $employe['statut'] === 'actif' ? 'success' : ($employe['statut'] === 'vacances' ? 'warning' : 'secondary') ?>">
                                            <?= ucfirst($employe['statut']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?= $employe['nb_animaux'] ?> animal<?= $employe['nb_animaux'] > 1 ? 'aux' : '' ?>, 
                                            <?= $employe['nb_activites'] ?> activité<?= $employe['nb_activites'] > 1 ? 's' : '' ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php 
                                            $employeForJs = $employes ? $employe : [];
                                            if (!empty($employeForJs)) {
                                                $employeForJs['salaire_affiche'] = convertirDevise($employe['salaire'], 'FCFA', $devise_actuelle);
                                            }
                                        ?>
                                        <button class="btn btn-sm btn-outline-primary" onclick='editEmploye(<?= json_encode($employeForJs, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT) ?>)'>
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteEmploye(<?= $employe['id'] ?>, '<?= htmlspecialchars($employe['nom'] . ' ' . $employe['prenom']) ?>')">
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

    <!-- Modal Ajouter Employé -->
    <div class="modal fade" id="addEmployeModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus"></i> Ajouter un employé</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nom *</label>
                                    <input type="text" class="form-control" name="nom" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Prénom *</label>
                                    <input type="text" class="form-control" name="prenom" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Poste *</label>
                                    <select class="form-select" name="poste" required>
                                        <option value="">Choisir...</option>
                                        <option value="Vétérinaire">Vétérinaire</option>
                                        <option value="Soigneur">Soigneur</option>
                                        <option value="Éleveur">Éleveur</option>
                                        <option value="Ouvrier agricole">Ouvrier agricole</option>
                                        <option value="Chef d'équipe">Chef d'équipe</option>
                                        <option value="Administratif">Administratif</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Date d'embauche *</label>
                                    <input type="date" class="form-control" name="date_embauche" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Salaire (<?= htmlspecialchars($devise_actuelle) ?>) *</label>
                                    <input type="number" step="0.01" class="form-control" name="salaire" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Téléphone</label>
                                    <input type="tel" class="form-control" name="telephone">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Adresse</label>
                                    <textarea class="form-control" name="adresse" rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="resetAddEmployeForm()">Annuler</button>
                        <button type="submit" class="btn btn-success">Ajouter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Éditer Employé -->
    <div class="modal fade" id="editEmployeModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit"></i> Modifier l'employé</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nom *</label>
                                    <input type="text" class="form-control" name="nom" id="edit_nom" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Prénom *</label>
                                    <input type="text" class="form-control" name="prenom" id="edit_prenom" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Poste *</label>
                                    <select class="form-select" name="poste" id="edit_poste" required>
                                        <option value="">Choisir...</option>
                                        <option value="Vétérinaire">Vétérinaire</option>
                                        <option value="Soigneur">Soigneur</option>
                                        <option value="Éleveur">Éleveur</option>
                                        <option value="Ouvrier agricole">Ouvrier agricole</option>
                                        <option value="Chef d'équipe">Chef d'équipe</option>
                                        <option value="Administratif">Administratif</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Date d'embauche *</label>
                                    <input type="date" class="form-control" name="date_embauche" id="edit_date_embauche" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Salaire (<?= htmlspecialchars($devise_actuelle) ?>) *</label>
                                    <input type="number" step="0.01" class="form-control" name="salaire" id="edit_salaire" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Statut *</label>
                                    <select class="form-select" name="statut" id="edit_statut" required>
                                        <option value="actif">Actif</option>
                                        <option value="inactif">Inactif</option>
                                        <option value="vacances">En vacances</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Téléphone</label>
                                    <input type="tel" class="form-control" name="telephone" id="edit_telephone">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" id="edit_email">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Adresse</label>
                            <textarea class="form-control" name="adresse" id="edit_adresse" rows="2"></textarea>
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

    <!-- Modal Supprimer Employé -->
    <div class="modal fade" id="deleteEmployeModal" tabindex="-1">
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
                        <p>Êtes-vous sûr de vouloir supprimer l'employé <strong id="delete_nom"></strong> ?</p>
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
            const input = document.getElementById('searchEmployes');
            if (!input) return;
            input.addEventListener('input', () => {
                const q = input.value.toLowerCase();
                document.querySelectorAll('table tbody tr').forEach(tr => {
                    tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
                });
            });
        })();
        function resetAddEmployeForm() {
            const form = document.querySelector('#addEmployeModal form');
            if (form) form.reset();
        }
        function editEmploye(employe) {
            document.getElementById('edit_id').value = employe.id;
            document.getElementById('edit_nom').value = employe.nom;
            document.getElementById('edit_prenom').value = employe.prenom;
            document.getElementById('edit_poste').value = employe.poste;
            document.getElementById('edit_date_embauche').value = employe.date_embauche;
            // Pré-remplir le salaire dans la devise actuelle
            if (typeof employe.salaire_affiche !== 'undefined') {
                document.getElementById('edit_salaire').value = employe.salaire_affiche;
            } else {
                document.getElementById('edit_salaire').value = employe.salaire;
            }
            document.getElementById('edit_telephone').value = employe.telephone || '';
            document.getElementById('edit_email').value = employe.email || '';
            document.getElementById('edit_adresse').value = employe.adresse || '';
            document.getElementById('edit_statut').value = employe.statut;
            
            new bootstrap.Modal(document.getElementById('editEmployeModal')).show();
        }
        
        function deleteEmploye(id, nom) {
            document.getElementById('delete_id').value = id;
            document.getElementById('delete_nom').textContent = nom;
            new bootstrap.Modal(document.getElementById('deleteEmployeModal')).show();
        }
    </script>
</body>
</html> 