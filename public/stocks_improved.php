<?php
// Inclure la configuration sécurisée
require_once 'config_sqlite.php';

// Traitement du changement de devise
if (isset($_POST['changer_devise'])) {
    setDeviseActuelle($_POST['devise']);
    header("Location: stocks_improved.php");
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
                    // Convertir le prix saisi vers FCFA pour le stockage
                    $prix_saisi = isset($_POST['prix_unitaire']) ? (float)$_POST['prix_unitaire'] : 0.0;
                    $prix_fcfa = ($devise_actuelle === 'FCFA') ? $prix_saisi : convertirDevise($prix_saisi, $devise_actuelle, 'FCFA');
                    $stmt = $db->prepare("
                        INSERT INTO stocks (produit, quantite, unite, date_entree, date_peremption, prix_unitaire, fournisseur, categorie, created_at, updated_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                    ");
                    $success = $stmt->execute([
                        $_POST['produit'],
                        $_POST['quantite'],
                        $_POST['unite'],
                        $_POST['date_entree'],
                        $_POST['date_peremption'] ?: null,
                        $prix_fcfa,
                        $_POST['fournisseur'],
                        $_POST['categorie']
                    ]);
                    $message = $success ? "Produit ajouté au stock avec succès !" : "Erreur lors de l'ajout";
                }
                break;
                
            case 'edit':
                if ($db) {
                    // Convertir le prix saisi vers FCFA pour le stockage
                    $prix_saisi = isset($_POST['prix_unitaire']) ? (float)$_POST['prix_unitaire'] : 0.0;
                    $prix_fcfa = ($devise_actuelle === 'FCFA') ? $prix_saisi : convertirDevise($prix_saisi, $devise_actuelle, 'FCFA');
                    $stmt = $db->prepare("
                        UPDATE stocks 
                        SET produit = ?, quantite = ?, unite = ?, date_entree = ?, date_peremption = ?, prix_unitaire = ?, fournisseur = ?, categorie = ?, updated_at = NOW()
                        WHERE id = ?
                    ");
                    $success = $stmt->execute([
                        $_POST['produit'],
                        $_POST['quantite'],
                        $_POST['unite'],
                        $_POST['date_entree'],
                        $_POST['date_peremption'] ?: null,
                        $prix_fcfa,
                        $_POST['fournisseur'],
                        $_POST['categorie'],
                        $_POST['id']
                    ]);
                    $message = $success ? "Stock modifié avec succès !" : "Erreur lors de la modification";
                }
                break;
                
            case 'delete':
                if ($db) {
                    $stmt = $db->prepare("DELETE FROM stocks WHERE id = ?");
                    $success = $stmt->execute([$_POST['id']]);
                    $message = $success ? "Produit supprimé du stock avec succès !" : "Erreur lors de la suppression";
                }
                break;
        }
        
        // Redirection avec message
        $status = $success ? 'success' : 'error';
        header("Location: stocks_improved.php?status=$status&message=" . urlencode($message));
        exit;
    }
}

// Récupération des stocks avec alertes
function getStocks($db) {
    if (!$db) return [];
    
    $stmt = $db->query("
        SELECT s.*, 
               CASE 
                   WHEN s.quantite <= 10 THEN 'faible'
                   WHEN s.date_peremption IS NOT NULL AND s.date_peremption <= date('now', '+30 days') THEN 'perime'
                   ELSE 'normal'
               END as statut_alerte
        FROM stocks s
        ORDER BY s.categorie, s.produit
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Récupération des statistiques
function getStockStats($db) {
    if (!$db) return [];
    
    $stats = [];
    
    // Total des produits
    $stmt = $db->query("SELECT COUNT(*) as total FROM stocks");
    $stats['total'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Produits en rupture
    $stmt = $db->query("SELECT COUNT(*) as total FROM stocks WHERE quantite <= 10");
    $stats['rupture'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Produits périmes
    $stmt = $db->query("SELECT COUNT(*) as total FROM stocks WHERE date_peremption IS NOT NULL AND date_peremption <= date('now')");
    $stats['perime'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Valeur totale du stock
    $stmt = $db->query("SELECT SUM(quantite * prix_unitaire) as total FROM stocks");
    $stats['valeur'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?: 0;
    
    return $stats;
}

$stocks = getStocks($db);
$stats = getStockStats($db);
$dbStatus = $db ? '✅ Connecté' : '❌ Non connecté';
$devise_actuelle = $devise_actuelle;

// Récupération des messages
$status = $_GET['status'] ?? '';
$message = $_GET['message'] ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Stocks - Ferme d'Élevage</title>
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
        .alert-rupture {
            border-left: 4px solid #dc3545;
            background: #f8d7da;
        }
        .alert-perime {
            border-left: 4px solid #ffc107;
            background: #fff3cd;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="display-5 text-primary">
                    <i class="fas fa-boxes"></i> Gestion des Stocks
                </h1>
                <p class="text-muted">Gérez vos stocks et inventaires</p>
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
                    <i class="fas fa-boxes fa-2x mb-2"></i>
                    <h3><?= $stats['total'] ?? 0 ?></h3>
                    <p class="mb-0">Produits en stock</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card text-center">
                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                    <h3><?= $stats['rupture'] ?? 0 ?></h3>
                    <p class="mb-0">En rupture</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card text-center">
                    <i class="fas fa-clock fa-2x mb-2"></i>
                    <h3><?= $stats['perime'] ?? 0 ?></h3>
                    <p class="mb-0">Périmés</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card text-center">
                    <i class="fas fa-euro-sign fa-2x mb-2"></i>
                                            <h3><?= formaterMontant(convertirDevise($stats['valeur'], 'FCFA', $devise_actuelle), $devise_actuelle) ?></h3>
                    <p class="mb-0">Valeur totale</p>
                </div>
            </div>
        </div>

        <!-- Bouton Ajouter -->
        <div class="mb-4">
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addStockModal">
                <i class="fas fa-plus"></i> Ajouter un produit
            </button>
        </div>

        <!-- Tableau des stocks -->
        <div class="table-container">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="fas fa-list"></i> Inventaire des stocks (<?= count($stocks) ?>)</h4>
                <input type="search" class="form-control" id="searchStocks" placeholder="Rechercher..." style="max-width: 280px;">
            </div>
            
            <?php if (empty($stocks)): ?>
                <div class="text-center py-4">
                    <i class="fas fa-boxes fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Aucun produit en stock</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Produit</th>
                                <th>Quantité</th>
                                <th>Prix unitaire</th>
                                <th>Valeur</th>
                                <th>Catégorie</th>
                                <th>Fournisseur</th>
                                <th>Date d'entrée</th>
                                <th>Date de péremption</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stocks as $stock): ?>
                                <tr class="<?= $stock['statut_alerte'] === 'faible' ? 'alert-rupture' : ($stock['statut_alerte'] === 'perime' ? 'alert-perime' : '') ?>">
                                    <td><strong><?= htmlspecialchars($stock['produit']) ?></strong></td>
                                    <td>
                                        <?= number_format($stock['quantite'], 2) ?> <?= htmlspecialchars($stock['unite']) ?>
                                        <?php if ($stock['quantite'] <= 10): ?>
                                            <span class="badge bg-danger ms-1">Faible</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= formaterMontant(convertirDevise($stock['prix_unitaire'], 'FCFA', $devise_actuelle), $devise_actuelle) ?></td>
                                    <td><strong><?= formaterMontant(convertirDevise($stock['quantite'] * $stock['prix_unitaire'], 'FCFA', $devise_actuelle), $devise_actuelle) ?></strong></td>
                                    <td><?= htmlspecialchars($stock['categorie']) ?></td>
                                    <td><?= htmlspecialchars($stock['fournisseur']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($stock['date_entree'])) ?></td>
                                    <td>
                                        <?php if ($stock['date_peremption']): ?>
                                            <?= date('d/m/Y', strtotime($stock['date_peremption'])) ?>
                                            <?php if (strtotime($stock['date_peremption']) <= time()): ?>
                                                <span class="badge bg-warning ms-1">Périmé</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($stock['statut_alerte'] === 'faible'): ?>
                                            <span class="badge bg-danger">Rupture</span>
                                        <?php elseif ($stock['statut_alerte'] === 'perime'): ?>
                                            <span class="badge bg-warning">Périmé</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Normal</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                            $stockForJs = $stock; 
                                            $stockForJs['prix_unitaire_affiche'] = convertirDevise($stock['prix_unitaire'], 'FCFA', $devise_actuelle);
                                        ?>
                                        <button class="btn btn-sm btn-outline-primary" onclick='editStock(<?= json_encode($stockForJs, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT) ?>)'>
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteStock(<?= $stock['id'] ?>, '<?= htmlspecialchars($stock['produit']) ?>')">
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

    <!-- Modal Ajouter Stock -->
    <div class="modal fade" id="addStockModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus"></i> Ajouter un produit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Produit *</label>
                                    <input type="text" class="form-control" name="produit" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Catégorie *</label>
                                    <select class="form-select" name="categorie" required>
                                        <option value="">Choisir...</option>
                                        <option value="Alimentation">Alimentation</option>
                                        <option value="Médicament">Médicament</option>
                                        <option value="Complément">Complément</option>
                                        <option value="Équipement">Équipement</option>
                                        <option value="Outillage">Outillage</option>
                                        <option value="Autre">Autre</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Quantité *</label>
                                    <input type="number" step="0.01" class="form-control" name="quantite" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Unité *</label>
                                    <select class="form-select" name="unite" required>
                                        <option value="">Choisir...</option>
                                        <option value="kg">Kilogrammes</option>
                                        <option value="g">Grammes</option>
                                        <option value="L">Litres</option>
                                        <option value="ml">Millilitres</option>
                                        <option value="unités">Unités</option>
                                        <option value="doses">Doses</option>
                                        <option value="paquets">Paquets</option>
                                        <option value="m">Mètres</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                    <label class="form-label">Prix unitaire (<?= htmlspecialchars($devise_actuelle) ?>) *</label>
                                    <input type="number" step="0.01" class="form-control" name="prix_unitaire" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Date d'entrée *</label>
                                    <input type="date" class="form-control" name="date_entree" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Date de péremption</label>
                                    <input type="date" class="form-control" name="date_peremption">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fournisseur</label>
                            <input type="text" class="form-control" name="fournisseur">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="resetAddStockForm()">Annuler</button>
                        <button type="submit" class="btn btn-success">Ajouter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Éditer Stock -->
    <div class="modal fade" id="editStockModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit"></i> Modifier le stock</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Produit *</label>
                                    <input type="text" class="form-control" name="produit" id="edit_produit" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Catégorie *</label>
                                    <select class="form-select" name="categorie" id="edit_categorie" required>
                                        <option value="">Choisir...</option>
                                        <option value="Alimentation">Alimentation</option>
                                        <option value="Médicament">Médicament</option>
                                        <option value="Complément">Complément</option>
                                        <option value="Équipement">Équipement</option>
                                        <option value="Outillage">Outillage</option>
                                        <option value="Autre">Autre</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Quantité *</label>
                                    <input type="number" step="0.01" class="form-control" name="quantite" id="edit_quantite" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Unité *</label>
                                    <select class="form-select" name="unite" id="edit_unite" required>
                                        <option value="">Choisir...</option>
                                        <option value="kg">Kilogrammes</option>
                                        <option value="g">Grammes</option>
                                        <option value="L">Litres</option>
                                        <option value="ml">Millilitres</option>
                                        <option value="unités">Unités</option>
                                        <option value="doses">Doses</option>
                                        <option value="paquets">Paquets</option>
                                        <option value="m">Mètres</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Prix unitaire (<?= htmlspecialchars($devise_actuelle) ?>) *</label>
                                    <input type="number" step="0.01" class="form-control" name="prix_unitaire" id="edit_prix_unitaire" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Date d'entrée *</label>
                                    <input type="date" class="form-control" name="date_entree" id="edit_date_entree" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Date de péremption</label>
                                    <input type="date" class="form-control" name="date_peremption" id="edit_date_peremption">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fournisseur</label>
                            <input type="text" class="form-control" name="fournisseur" id="edit_fournisseur">
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

    <!-- Modal Supprimer Stock -->
    <div class="modal fade" id="deleteStockModal" tabindex="-1">
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
                        <p>Êtes-vous sûr de vouloir supprimer le produit <strong id="delete_nom"></strong> ?</p>
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
            const input = document.getElementById('searchStocks');
            if (!input) return;
            input.addEventListener('input', () => {
                const q = input.value.toLowerCase();
                document.querySelectorAll('table tbody tr').forEach(tr => {
                    tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
                });
            });
        })();
        function resetAddStockForm() {
            const form = document.querySelector('#addStockModal form');
            if (form) form.reset();
        }
        function editStock(stock) {
            document.getElementById('edit_id').value = stock.id;
            document.getElementById('edit_produit').value = stock.produit;
            document.getElementById('edit_categorie').value = stock.categorie;
            document.getElementById('edit_quantite').value = stock.quantite;
            document.getElementById('edit_unite').value = stock.unite;
            // Pré-remplir le prix dans la devise actuelle
            if (typeof stock.prix_unitaire_affiche !== 'undefined') {
                document.getElementById('edit_prix_unitaire').value = stock.prix_unitaire_affiche;
            } else {
                document.getElementById('edit_prix_unitaire').value = stock.prix_unitaire;
            }
            document.getElementById('edit_date_entree').value = stock.date_entree;
            document.getElementById('edit_date_peremption').value = stock.date_peremption || '';
            document.getElementById('edit_fournisseur').value = stock.fournisseur || '';
            
            new bootstrap.Modal(document.getElementById('editStockModal')).show();
        }
        
        function deleteStock(id, nom) {
            document.getElementById('delete_id').value = id;
            document.getElementById('delete_nom').textContent = nom;
            new bootstrap.Modal(document.getElementById('deleteStockModal')).show();
        }
    </script>
</body>
</html> 