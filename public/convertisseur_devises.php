<?php
// Inclure la configuration sécurisée
require_once 'config_infinityfree.php';

// Traitement du changement de devise
if (isset($_POST['changer_devise'])) {
    setDeviseActuelle($_POST['devise']);
    header("Location: convertisseur_devises.php");
    exit;
}

// Traitement de la conversion
$resultat = '';
$montant_original = '';
$devise_source = '';
$devise_cible = '';

if (isset($_POST['convertir']) && isset($_POST['montant'])) {
    $montant = floatval($_POST['montant']);
    $devise_source = $_POST['devise_source'];
    $devise_cible = $_POST['devise_cible'];
    
    if ($montant > 0) {
        $montant_converti = convertirDevise($montant, $devise_source, $devise_cible);
        $montant_original = $montant;
        $resultat = formaterMontant($montant_converti, $devise_cible);
    }
}

$devise_actuelle = getDeviseActuelle();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Convertisseur de Devises - Ferme d'Élevage</title>
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
            max-width: 800px;
        }
        .converter-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border-left: 5px solid #667eea;
        }
        .result-card {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="display-5 text-primary">
                    <i class="fas fa-exchange-alt"></i> Convertisseur de Devises
                </h1>
                <p class="text-muted">Convertissez vos montants entre FCFA, Euro et Dollar</p>
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

        <!-- Convertisseur -->
        <div class="converter-card">
            <form method="POST">
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Montant</label>
                            <input type="number" step="0.01" class="form-control" name="montant" value="<?= $montant_original ?>" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">De</label>
                            <select class="form-select" name="devise_source" required>
                                <option value="">Choisir...</option>
                                <option value="FCFA" <?= $devise_source === 'FCFA' ? 'selected' : '' ?>>FCFA (Franc CFA)</option>
                                <option value="EUR" <?= $devise_source === 'EUR' ? 'selected' : '' ?>>€ Euro</option>
                                <option value="USD" <?= $devise_source === 'USD' ? 'selected' : '' ?>>$ Dollar US</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Vers</label>
                            <select class="form-select" name="devise_cible" required>
                                <option value="">Choisir...</option>
                                <option value="FCFA" <?= $devise_cible === 'FCFA' ? 'selected' : '' ?>>FCFA (Franc CFA)</option>
                                <option value="EUR" <?= $devise_cible === 'EUR' ? 'selected' : '' ?>>€ Euro</option>
                                <option value="USD" <?= $devise_cible === 'USD' ? 'selected' : '' ?>>$ Dollar US</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="text-center">
                    <button type="submit" name="convertir" class="btn btn-primary btn-lg">
                        <i class="fas fa-exchange-alt"></i> Convertir
                    </button>
                </div>
            </form>
        </div>

        <!-- Résultat -->
        <?php if ($resultat): ?>
            <div class="result-card mt-4">
                <h3><i class="fas fa-calculator"></i> Résultat de la conversion</h3>
                <div class="display-4 mb-3">
                    <?= formaterMontant($montant_original, $devise_source) ?> = <?= $resultat ?>
                </div>
                <p class="mb-0">
                    <small>
                        Taux de change : 1 <?= $devise_source ?> = 
                        <?= formaterMontant(convertirDevise(1, $devise_source, $devise_cible), $devise_cible) ?>
                    </small>
                </p>
            </div>
        <?php endif; ?>

        <!-- Informations sur les taux -->
        <div class="converter-card mt-4">
            <h4><i class="fas fa-info-circle"></i> Taux de change actuels</h4>
            <div class="row">
                <div class="col-md-4">
                    <div class="text-center p-3 border rounded">
                        <h5>1 € Euro</h5>
                        <p class="mb-0">= <?= formaterMontant(655.957, 'FCFA') ?></p>
                        <small class="text-muted">= <?= formaterMontant(1.09, 'USD') ?></small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center p-3 border rounded">
                        <h5>1 FCFA</h5>
                        <p class="mb-0">= <?= formaterMontant(0.0015, 'EUR') ?></p>
                        <small class="text-muted">= <?= formaterMontant(0.0017, 'USD') ?></small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center p-3 border rounded">
                        <h5>1 $ Dollar</h5>
                        <p class="mb-0">= <?= formaterMontant(0.92, 'EUR') ?></p>
                        <small class="text-muted">= <?= formaterMontant(588.95, 'FCFA') ?></small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Exemple de conversion automatique -->
        <div class="converter-card mt-4">
            <h4><i class="fas fa-magic"></i> Exemple de conversion automatique</h4>
            <p class="text-muted">Les montants dans l'application sont automatiquement convertis selon la devise sélectionnée.</p>
            <div class="row">
                <div class="col-md-6">
                    <div class="p-3 border rounded">
                        <h6>Prix d'un produit : 5000 FCFA</h6>
                        <ul class="list-unstyled">
                            <li>FCFA : <?= formaterMontant(5000, 'FCFA') ?></li>
                            <li>EUR : <?= formaterMontant(convertirDevise(5000, 'FCFA', 'EUR'), 'EUR') ?></li>
                            <li>USD : <?= formaterMontant(convertirDevise(5000, 'FCFA', 'USD'), 'USD') ?></li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-3 border rounded">
                        <h6>Valeur du stock : 150000 FCFA</h6>
                        <ul class="list-unstyled">
                            <li>FCFA : <?= formaterMontant(150000, 'FCFA') ?></li>
                            <li>EUR : <?= formaterMontant(convertirDevise(150000, 'FCFA', 'EUR'), 'EUR') ?></li>
                            <li>USD : <?= formaterMontant(convertirDevise(150000, 'FCFA', 'USD'), 'USD') ?></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Liens rapides -->
        <div class="text-center mt-4">
            <div class="d-flex flex-wrap justify-content-center gap-2">
                <a href="stocks_improved.php" class="btn btn-outline-primary">
                    <i class="fas fa-boxes"></i> Gestion des stocks
                </a>
                <a href="rapports.php" class="btn btn-outline-success">
                    <i class="fas fa-chart-bar"></i> Rapports
                </a>
                <a href="index_final.php" class="btn btn-outline-secondary">
                    <i class="fas fa-home"></i> Menu principal
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 