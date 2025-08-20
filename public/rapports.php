<?php
// Inclure la configuration sécurisée
require_once 'config_sqlite.php';

// Traitement du changement de devise
if (isset($_POST['changer_devise'])) {
    setDeviseActuelle($_POST['devise']);
    header("Location: rapports.php");
    exit;
}

// Connexion à la base de données
$db = connectDB();

// Traitement des exports
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $type = $_POST['type'] ?? '';
    $format = $_POST['format'] ?? '';
    
    if ($type && $format) {
        switch ($type) {
            case 'animaux':
                if ($format === 'csv') {
                    exportAnimauxCSV($db);
                } elseif ($format === 'pdf') {
                    exportAnimauxPDF($db);
                }
                break;
            case 'stocks':
                if ($format === 'csv') {
                    exportStocksCSV($db);
                } elseif ($format === 'pdf') {
                    exportStocksPDF($db);
                }
                break;
            case 'activites':
                if ($format === 'csv') {
                    exportActivitesCSV($db);
                } elseif ($format === 'pdf') {
                    exportActivitesPDF($db);
                }
                break;
            case 'employes':
                if ($format === 'csv') {
                    exportEmployesCSV($db);
                } elseif ($format === 'pdf') {
                    exportEmployesPDF($db);
                }
                break;
            case 'alertes':
                if ($format === 'csv') {
                    exportAlertesCSV($db);
                } elseif ($format === 'pdf') {
                    exportAlertesPDF($db);
                }
                break;
        }
    }
}

// Fonctions d'export CSV
function exportAnimauxCSV($db) {
    if (!$db) return;
    
    $stmt = $db->query("
        SELECT nom, espece, race, date_naissance, sexe, poids, statut, 
               DATE_FORMAT(created_at, '%d/%m/%Y') as date_ajout
        FROM animaux 
        ORDER BY espece, nom
    ");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="animaux_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8
    
    // En-têtes
    fputcsv($output, ['Nom', 'Espèce', 'Race', 'Date de naissance', 'Sexe', 'Poids (kg)', 'Statut', 'Date d\'ajout']);
    
    // Données
    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit;
}

function exportStocksCSV($db) {
    if (!$db) return;
    
    global $devise_actuelle;
    
    $stmt = $db->query("
        SELECT produit, categorie, quantite, unite, prix_unitaire, 
               (quantite * prix_unitaire) as valeur_totale,
               fournisseur, DATE_FORMAT(date_entree, '%d/%m/%Y') as date_entree,
               DATE_FORMAT(date_peremption, '%d/%m/%Y') as date_peremption
        FROM stocks 
        ORDER BY categorie, produit
    ");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="stocks_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    fputcsv($output, ['Produit', 'Catégorie', 'Quantité', 'Unité', 'Prix unitaire (' . $devise_actuelle . ')', 'Valeur totale (' . $devise_actuelle . ')', 'Fournisseur', 'Date d\'entrée', 'Date de péremption']);
    
    foreach ($data as $row) {
        // Formater les montants avec conversion automatique
        $row['prix_unitaire'] = formaterMontant(convertirDevise($row['prix_unitaire'], 'FCFA', $devise_actuelle), $devise_actuelle);
        $row['valeur_totale'] = formaterMontant(convertirDevise($row['valeur_totale'], 'FCFA', $devise_actuelle), $devise_actuelle);
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit;
}

function exportActivitesCSV($db) {
    if (!$db) return;
    
    $stmt = $db->query("
        SELECT a.titre, a.type, a.description, DATE_FORMAT(a.date, '%d/%m/%Y') as date,
               a.heure_debut, a.heure_fin, a.statut,
               CONCAT(e.nom, ' ', e.prenom) as employe,
               CONCAT(an.nom, ' (', an.espece, ')') as animal
        FROM activites a
        LEFT JOIN employes e ON a.employe_id = e.id
        LEFT JOIN animaux an ON a.animal_id = an.id
        ORDER BY a.date DESC, a.heure_debut
    ");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="activites_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    fputcsv($output, ['Titre', 'Type', 'Description', 'Date', 'Heure début', 'Heure fin', 'Statut', 'Employé', 'Animal']);
    
    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit;
}

function exportEmployesCSV($db) {
    if (!$db) return;
    
    $stmt = $db->query("
        SELECT nom, prenom, email, telephone, poste, responsabilites, statut,
               DATE_FORMAT(date_embauche, '%d/%m/%Y') as date_embauche
        FROM employes 
        ORDER BY nom, prenom
    ");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="employes_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    fputcsv($output, ['Nom', 'Prénom', 'Email', 'Téléphone', 'Poste', 'Responsabilités', 'Statut', 'Date d\'embauche']);
    
    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit;
}

function exportAlertesCSV($db) {
    if (!$db) return;
    
    $stmt = $db->query("
        SELECT titre, type, description, priorite, statut,
               DATE_FORMAT(date_creation, '%d/%m/%Y %H:%i') as date_creation,
               DATE_FORMAT(date_echeance, '%d/%m/%Y') as date_echeance
        FROM alertes 
        ORDER BY priorite, date_creation DESC
    ");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="alertes_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    fputcsv($output, ['Titre', 'Type', 'Description', 'Priorité', 'Statut', 'Date création', 'Date échéance']);
    
    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit;
}

// Fonctions d'export PDF (HTML simple)
function exportAnimauxPDF($db) {
    if (!$db) return;
    
    $stmt = $db->query("
        SELECT nom, espece, race, date_naissance, sexe, poids, statut
        FROM animaux 
        ORDER BY espece, nom
    ");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    generatePDF('Rapport des Animaux', $data, ['Nom', 'Espèce', 'Race', 'Date de naissance', 'Sexe', 'Poids (kg)', 'Statut']);
}

function exportStocksPDF($db) {
    if (!$db) return;
    
    global $devise_actuelle;
    
    $stmt = $db->query("
        SELECT produit, categorie, quantite, unite, prix_unitaire, 
               (quantite * prix_unitaire) as valeur_totale, fournisseur
        FROM stocks 
        ORDER BY categorie, produit
    ");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formater les montants avec conversion automatique
    foreach ($data as &$row) {
        $row['prix_unitaire'] = formaterMontant(convertirDevise($row['prix_unitaire'], 'FCFA', $devise_actuelle), $devise_actuelle);
        $row['valeur_totale'] = formaterMontant(convertirDevise($row['valeur_totale'], 'FCFA', $devise_actuelle), $devise_actuelle);
    }
    
    generatePDF('Rapport des Stocks (' . $devise_actuelle . ')', $data, ['Produit', 'Catégorie', 'Quantité', 'Unité', 'Prix unitaire (' . $devise_actuelle . ')', 'Valeur totale (' . $devise_actuelle . ')', 'Fournisseur']);
}

function exportActivitesPDF($db) {
    if (!$db) return;
    
    $stmt = $db->query("
        SELECT a.titre, a.type, a.date, a.heure_debut, a.heure_fin, a.statut,
               CONCAT(e.nom, ' ', e.prenom) as employe,
               CONCAT(an.nom, ' (', an.espece, ')') as animal
        FROM activites a
        LEFT JOIN employes e ON a.employe_id = e.id
        LEFT JOIN animaux an ON a.animal_id = an.id
        ORDER BY a.date DESC
    ");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    generatePDF('Rapport des Activités', $data, ['Titre', 'Type', 'Date', 'Heure début', 'Heure fin', 'Statut', 'Employé', 'Animal']);
}

function exportEmployesPDF($db) {
    if (!$db) return;
    
    $stmt = $db->query("
        SELECT nom, prenom, email, telephone, poste, responsabilites, statut
        FROM employes 
        ORDER BY nom, prenom
    ");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    generatePDF('Rapport des Employés', $data, ['Nom', 'Prénom', 'Email', 'Téléphone', 'Poste', 'Responsabilités', 'Statut']);
}

function exportAlertesPDF($db) {
    if (!$db) return;
    
    $stmt = $db->query("
        SELECT titre, type, priorite, statut, date_creation, date_echeance
        FROM alertes 
        ORDER BY priorite, date_creation DESC
    ");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    generatePDF('Rapport des Alertes', $data, ['Titre', 'Type', 'Priorité', 'Statut', 'Date création', 'Date échéance']);
}

function generatePDF($title, $data, $headers) {
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . strtolower(str_replace(' ', '_', $title)) . '_' . date('Y-m-d') . '.html"');
    
    echo '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>' . $title . '</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            h1 { color: #333; text-align: center; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; font-weight: bold; }
            tr:nth-child(even) { background-color: #f9f9f9; }
            .header { text-align: center; margin-bottom: 30px; }
            .date { color: #666; font-size: 14px; }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>' . $title . '</h1>
            <p class="date">Généré le ' . date('d/m/Y à H:i') . '</p>
        </div>
        <table>
            <thead>
                <tr>';
    
    foreach ($headers as $header) {
        echo '<th>' . $header . '</th>';
    }
    
    echo '</tr></thead><tbody>';
    
    foreach ($data as $row) {
        echo '<tr>';
        foreach ($row as $value) {
            echo '<td>' . htmlspecialchars($value) . '</td>';
        }
        echo '</tr>';
    }
    
    echo '</tbody></table></body></html>';
    exit;
}

// Récupération des statistiques
function getRapportStats($db) {
    if (!$db) return [];
    
    $stats = [];
    
    // Animaux
    $stmt = $db->query("SELECT COUNT(*) as total FROM animaux");
    $stats['animaux'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Stocks
    $stmt = $db->query("SELECT COUNT(*) as total FROM stocks");
    $stats['stocks'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Activités
    $stmt = $db->query("SELECT COUNT(*) as total FROM activites");
    $stats['activites'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Employés
    $stmt = $db->query("SELECT COUNT(*) as total FROM employes");
    $stats['employes'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Alertes
    $stmt = $db->query("SELECT COUNT(*) as total FROM alertes");
    $stats['alertes'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    return $stats;
}

$stats = getRapportStats($db);
$dbStatus = $db ? '✅ Connecté' : '❌ Non connecté';
$devise_actuelle = getDeviseActuelle();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapports et Exports - Ferme d'Élevage</title>
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
            max-width: 1200px;
        }
        .report-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border-left: 4px solid #667eea;
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
                    <i class="fas fa-chart-bar"></i> Rapports et Exports
                </h1>
                <p class="text-muted">Générez des rapports détaillés et exportez vos données</p>
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

        <!-- Affichage des Montants avec Conversion -->
        <div class="alert alert-success">
            <h5><i class="fas fa-money-bill-wave"></i> Conversion des Montants</h5>
            <p><strong>Devise actuelle :</strong> <?= $devise_actuelle ?></p>
            <?php
            // Récupérer quelques exemples de montants pour démonstration
            if ($db) {
                try {
                    // Exemple de salaire d'employé
                    $stmt = $db->query("SELECT salaire FROM employes LIMIT 1");
                    $employe = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($employe) {
                        $salaire_fcfa = $employe['salaire'];
                        echo "<p><strong>Exemple de salaire :</strong> " . formaterMontant($salaire_fcfa, 'FCFA') . " = " . formaterMontant(convertirDevise($salaire_fcfa, 'FCFA', 'EUR'), 'EUR') . " = " . formaterMontant(convertirDevise($salaire_fcfa, 'FCFA', 'USD'), 'USD') . "</p>";
                    }
                    
                    // Exemple de prix de stock
                    $stmt = $db->query("SELECT prix_unitaire FROM stocks LIMIT 1");
                    $stock = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($stock) {
                        $prix_fcfa = $stock['prix_unitaire'];
                        echo "<p><strong>Exemple de prix unitaire :</strong> " . formaterMontant($prix_fcfa, 'FCFA') . " = " . formaterMontant(convertirDevise($prix_fcfa, 'FCFA', 'EUR'), 'EUR') . " = " . formaterMontant(convertirDevise($prix_fcfa, 'FCFA', 'USD'), 'USD') . "</p>";
                    }
                } catch (Exception $e) {
                    echo "<p style='color: orange;'>⚠️ Impossible de récupérer les exemples de montants</p>";
                }
            }
            ?>
        </div>

        <!-- Statistiques -->
        <div class="row mb-4">
            <div class="col-md-2">
                <div class="stat-card text-center">
                    <i class="fas fa-cow fa-2x mb-2"></i>
                    <h4><?= $stats['animaux'] ?? 0 ?></h4>
                    <p class="mb-0">Animaux</p>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card text-center">
                    <i class="fas fa-boxes fa-2x mb-2"></i>
                    <h4><?= $stats['stocks'] ?? 0 ?></h4>
                    <p class="mb-0">Stocks</p>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card text-center">
                    <i class="fas fa-tasks fa-2x mb-2"></i>
                    <h4><?= $stats['activites'] ?? 0 ?></h4>
                    <p class="mb-0">Activités</p>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card text-center">
                    <i class="fas fa-users fa-2x mb-2"></i>
                    <h4><?= $stats['employes'] ?? 0 ?></h4>
                    <p class="mb-0">Employés</p>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card text-center">
                    <i class="fas fa-bell fa-2x mb-2"></i>
                    <h4><?= $stats['alertes'] ?? 0 ?></h4>
                    <p class="mb-0">Alertes</p>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card text-center">
                    <i class="fas fa-calendar-day fa-2x mb-2"></i>
                    <h4><?= date('d/m') ?></h4>
                    <p class="mb-0">Aujourd'hui</p>
                </div>
            </div>
        </div>

        <!-- Rapports disponibles -->
        <div class="row">
            <!-- Animaux -->
            <div class="col-md-6">
                <div class="report-card">
                    <h4><i class="fas fa-cow text-primary"></i> Rapport des Animaux</h4>
                    <p class="text-muted">Liste complète des animaux avec leurs informations</p>
                    <div class="d-flex gap-2">
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="export">
                            <input type="hidden" name="type" value="animaux">
                            <input type="hidden" name="format" value="csv">
                            <button type="submit" class="btn btn-outline-success">
                                <i class="fas fa-file-csv"></i> Export CSV
                            </button>
                        </form>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="export">
                            <input type="hidden" name="type" value="animaux">
                            <input type="hidden" name="format" value="pdf">
                            <button type="submit" class="btn btn-outline-danger">
                                <i class="fas fa-file-pdf"></i> Export PDF
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Stocks -->
            <div class="col-md-6">
                <div class="report-card">
                    <h4><i class="fas fa-boxes text-primary"></i> Rapport des Stocks</h4>
                    <p class="text-muted">Inventaire complet avec valeurs et dates de péremption</p>
                    <div class="d-flex gap-2">
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="export">
                            <input type="hidden" name="type" value="stocks">
                            <input type="hidden" name="format" value="csv">
                            <button type="submit" class="btn btn-outline-success">
                                <i class="fas fa-file-csv"></i> Export CSV
                            </button>
                        </form>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="export">
                            <input type="hidden" name="type" value="stocks">
                            <input type="hidden" name="format" value="pdf">
                            <button type="submit" class="btn btn-outline-danger">
                                <i class="fas fa-file-pdf"></i> Export PDF
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Activités -->
            <div class="col-md-6">
                <div class="report-card">
                    <h4><i class="fas fa-tasks text-primary"></i> Rapport des Activités</h4>
                    <p class="text-muted">Planning et suivi des activités avec employés assignés</p>
                    <div class="d-flex gap-2">
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="export">
                            <input type="hidden" name="type" value="activites">
                            <input type="hidden" name="format" value="csv">
                            <button type="submit" class="btn btn-outline-success">
                                <i class="fas fa-file-csv"></i> Export CSV
                            </button>
                        </form>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="export">
                            <input type="hidden" name="type" value="activites">
                            <input type="hidden" name="format" value="pdf">
                            <button type="submit" class="btn btn-outline-danger">
                                <i class="fas fa-file-pdf"></i> Export PDF
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Employés -->
            <div class="col-md-6">
                <div class="report-card">
                    <h4><i class="fas fa-users text-primary"></i> Rapport des Employés</h4>
                    <p class="text-muted">Liste du personnel avec postes et responsabilités</p>
                    <div class="d-flex gap-2">
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="export">
                            <input type="hidden" name="type" value="employes">
                            <input type="hidden" name="format" value="csv">
                            <button type="submit" class="btn btn-outline-success">
                                <i class="fas fa-file-csv"></i> Export CSV
                            </button>
                        </form>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="export">
                            <input type="hidden" name="type" value="employes">
                            <input type="hidden" name="format" value="pdf">
                            <button type="submit" class="btn btn-outline-danger">
                                <i class="fas fa-file-pdf"></i> Export PDF
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Alertes -->
            <div class="col-md-6">
                <div class="report-card">
                    <h4><i class="fas fa-bell text-primary"></i> Rapport des Alertes</h4>
                    <p class="text-muted">Historique des alertes avec priorités et statuts</p>
                    <div class="d-flex gap-2">
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="export">
                            <input type="hidden" name="type" value="alertes">
                            <input type="hidden" name="format" value="csv">
                            <button type="submit" class="btn btn-outline-success">
                                <i class="fas fa-file-csv"></i> Export CSV
                            </button>
                        </form>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="export">
                            <input type="hidden" name="type" value="alertes">
                            <input type="hidden" name="format" value="pdf">
                            <button type="submit" class="btn btn-outline-danger">
                                <i class="fas fa-file-pdf"></i> Export PDF
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Instructions -->
        <div class="alert alert-info mt-4">
            <h5><i class="fas fa-info-circle"></i> Instructions d'utilisation :</h5>
            <ul class="mb-0">
                <li><strong>Export CSV :</strong> Télécharge un fichier Excel compatible avec tous les logiciels</li>
                <li><strong>Export PDF :</strong> Génère un rapport HTML formaté pour impression</li>
                <li>Les fichiers sont nommés automatiquement avec la date du jour</li>
                <li>Les données sont exportées dans l'ordre alphabétique ou chronologique</li>
            </ul>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 