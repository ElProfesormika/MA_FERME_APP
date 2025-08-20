<?php
// Inclure la configuration sécurisée
require_once 'config_sqlite.php';

// Traitement du changement de devise
if (isset($_POST['changer_devise'])) {
    setDeviseActuelle($_POST['devise']);
    header("Location: animaux_improved.php");
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
                        INSERT INTO animaux (nom, espece, race, date_naissance, poids, sexe, employe_id, statut, created_at, updated_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?, 'actif', NOW(), NOW())
                    ");
                    $success = $stmt->execute([
                        $_POST['nom'],
                        $_POST['espece'],
                        $_POST['race'],
                        $_POST['date_naissance'],
                        $_POST['poids'],
                        $_POST['sexe'],
                        $_POST['employe_id'] ?: null
                    ]);
                    $message = $success ? "Animal ajouté avec succès !" : "Erreur lors de l'ajout";
                }
                break;
                
            case 'edit':
                if ($db) {
                    $stmt = $db->prepare("
                        UPDATE animaux 
                        SET nom = ?, espece = ?, race = ?, date_naissance = ?, poids = ?, sexe = ?, employe_id = ?, updated_at = NOW()
                        WHERE id = ?
                    ");
                    $success = $stmt->execute([
                        $_POST['nom'],
                        $_POST['espece'],
                        $_POST['race'],
                        $_POST['date_naissance'],
                        $_POST['poids'],
                        $_POST['sexe'],
                        $_POST['employe_id'] ?: null,
                        $_POST['id']
                    ]);
                    $message = $success ? "Animal modifié avec succès !" : "Erreur lors de la modification";
                }
                break;
                
            case 'delete':
                if ($db) {
                    $stmt = $db->prepare("DELETE FROM animaux WHERE id = ?");
                    $success = $stmt->execute([$_POST['id']]);
                    $message = $success ? "Animal supprimé avec succès !" : "Erreur lors de la suppression";
                }
                break;
        }
        
        // Redirection avec message
        $status = $success ? 'success' : 'error';
        header("Location: animaux_improved.php?status=$status&message=" . urlencode($message));
        exit;
    }
}

// Récupération des animaux
function getAnimaux($db) {
    if (!$db) return [];
    
    $stmt = $db->query("
        SELECT a.*, e.nom as employe_nom, e.prenom as employe_prenom
        FROM animaux a
        LEFT JOIN employes e ON a.employe_id = e.id
        ORDER BY a.nom
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Récupération des employés pour le formulaire
function getEmployes($db) {
    if (!$db) return [];
    
    $stmt = $db->query("SELECT id, nom, prenom FROM employes WHERE statut = 'actif' ORDER BY nom");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$animaux = getAnimaux($db);
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
    <title>Gestion des Animaux - Ferme d'Élevage</title>
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
                    <i class="fas fa-cow"></i> Gestion des Animaux
                </h1>
                <p class="text-muted">Gérez votre cheptel et suivez vos animaux</p>
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
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addAnimalModal" onclick="resetAddAnimalForm()">
                <i class="fas fa-plus"></i> Ajouter un animal
            </button>
        </div>

        <!-- Tableau des animaux -->
        <div class="table-container">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="fas fa-list"></i> Liste des animaux (<?= count($animaux) ?>)</h4>
                <input type="search" class="form-control" id="searchAnimaux" placeholder="Rechercher..." style="max-width: 280px;">
            </div>
            
            <?php if (empty($animaux)): ?>
                <div class="text-center py-4">
                    <i class="fas fa-cow fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Aucun animal enregistré</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Nom</th>
                                <th>Espèce</th>
                                <th>Race</th>
                                <th>Date de naissance</th>
                                <th>Poids (kg)</th>
                                <th>Sexe</th>
                                <th>Responsable</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($animaux as $animal): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($animal['nom']) ?></strong></td>
                                    <td><?= htmlspecialchars($animal['espece']) ?></td>
                                    <td><?= htmlspecialchars($animal['race']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($animal['date_naissance'])) ?></td>
                                    <td><?= $animal['poids'] ? number_format($animal['poids'], 1) : '-' ?></td>
                                    <td>
                                        <span class="badge bg-<?= $animal['sexe'] === 'male' ? 'primary' : 'pink' ?>">
                                            <?= ucfirst($animal['sexe']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?= $animal['employe_nom'] ? htmlspecialchars($animal['employe_nom'] . ' ' . $animal['employe_prenom']) : '-' ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $animal['statut'] === 'actif' ? 'success' : ($animal['statut'] === 'malade' ? 'warning' : 'secondary') ?>">
                                            <?= ucfirst($animal['statut']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="editAnimal(<?= htmlspecialchars(json_encode($animal)) ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteAnimal(<?= $animal['id'] ?>, '<?= htmlspecialchars($animal['nom']) ?>')">
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

    <!-- Modal Ajouter Animal -->
    <div class="modal fade" id="addAnimalModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus"></i> Ajouter un animal</h5>
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
                                    <label class="form-label">Espèce *</label>
                                    <select class="form-select" name="espece" required>
                                        <option value="">Choisir...</option>
                                        <option value="Vache">Vache</option>
                                        <option value="Taureau">Taureau</option>
                                        <option value="Veau">Veau</option>
                                        <option value="Cochon">Cochon</option>
                                        <option value="Poule">Poule</option>
                                        <option value="Mouton">Mouton</option>
                                        <option value="Chèvre">Chèvre</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Race</label>
                                    <input type="text" class="form-control" name="race">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Date de naissance *</label>
                                    <input type="date" class="form-control" name="date_naissance" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Poids (kg)</label>
                                    <input type="number" step="0.1" class="form-control" name="poids">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Sexe *</label>
                                    <select class="form-select" name="sexe" required>
                                        <option value="">Choisir...</option>
                                        <option value="male">Mâle</option>
                                        <option value="femelle">Femelle</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
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

    <!-- Modal Éditer Animal -->
    <div class="modal fade" id="editAnimalModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit"></i> Modifier l'animal</h5>
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
                                    <label class="form-label">Espèce *</label>
                                    <select class="form-select" name="espece" id="edit_espece" required>
                                        <option value="">Choisir...</option>
                                        <option value="Vache">Vache</option>
                                        <option value="Taureau">Taureau</option>
                                        <option value="Veau">Veau</option>
                                        <option value="Cochon">Cochon</option>
                                        <option value="Poule">Poule</option>
                                        <option value="Mouton">Mouton</option>
                                        <option value="Chèvre">Chèvre</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Race</label>
                                    <input type="text" class="form-control" name="race" id="edit_race">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Date de naissance *</label>
                                    <input type="date" class="form-control" name="date_naissance" id="edit_date_naissance" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Poids (kg)</label>
                                    <input type="number" step="0.1" class="form-control" name="poids" id="edit_poids">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Sexe *</label>
                                    <select class="form-select" name="sexe" id="edit_sexe" required>
                                        <option value="">Choisir...</option>
                                        <option value="male">Mâle</option>
                                        <option value="femelle">Femelle</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
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

    <!-- Modal Supprimer Animal -->
    <div class="modal fade" id="deleteAnimalModal" tabindex="-1">
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
                        <p>Êtes-vous sûr de vouloir supprimer l'animal <strong id="delete_nom"></strong> ?</p>
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
            const input = document.getElementById('searchAnimaux');
            if (!input) return;
            input.addEventListener('input', () => {
                const q = input.value.toLowerCase();
                document.querySelectorAll('table tbody tr').forEach(tr => {
                    tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
                });
            });
        })();
        function resetAddAnimalForm() {
            const form = document.querySelector('#addAnimalModal form');
            if (form) form.reset();
        }
        function editAnimal(animal) {
            document.getElementById('edit_id').value = animal.id;
            document.getElementById('edit_nom').value = animal.nom;
            document.getElementById('edit_espece').value = animal.espece;
            document.getElementById('edit_race').value = animal.race;
            document.getElementById('edit_date_naissance').value = animal.date_naissance;
            document.getElementById('edit_poids').value = animal.poids;
            document.getElementById('edit_sexe').value = animal.sexe;
            document.getElementById('edit_employe_id').value = animal.employe_id || '';
            
            new bootstrap.Modal(document.getElementById('editAnimalModal')).show();
        }
        
        function deleteAnimal(id, nom) {
            document.getElementById('delete_id').value = id;
            document.getElementById('delete_nom').textContent = nom;
            new bootstrap.Modal(document.getElementById('deleteAnimalModal')).show();
        }
    </script>
</body>
</html> 