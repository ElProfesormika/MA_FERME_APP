<?php
// Inclure la configuration sécurisée
require_once 'config_infinityfree.php';

// Traitement du changement de devise
if (isset($_POST['changer_devise'])) {
    setDeviseActuelle($_POST['devise']);
    header("Location: gestion_equipe.php");
    exit;
}

// Connexion à la base de données
$db = connectDB();

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $success = false;
        $message = '';
        
        switch ($_POST['action']) {
            case 'add_user':
                if ($db) {
                    $stmt = $db->prepare("
                        INSERT INTO users (name, email, password, role, permissions, created_at, updated_at)
                        VALUES (?, ?, ?, ?, ?, NOW(), NOW())
                    ");
                    $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    $permissions = json_encode($_POST['permissions'] ?? []);
                    $success = $stmt->execute([
                        $_POST['name'],
                        $_POST['email'],
                        $password_hash,
                        $_POST['role'],
                        $permissions
                    ]);
                    $message = $success ? "Utilisateur ajouté avec succès !" : "Erreur lors de l'ajout";
                }
                break;
                
            case 'update_permissions':
                if ($db) {
                    $stmt = $db->prepare("
                        UPDATE users 
                        SET role = ?, permissions = ?, updated_at = NOW()
                        WHERE id = ?
                    ");
                    $permissions = json_encode($_POST['permissions'] ?? []);
                    $success = $stmt->execute([
                        $_POST['role'],
                        $permissions,
                        $_POST['user_id']
                    ]);
                    $message = $success ? "Permissions mises à jour !" : "Erreur lors de la mise à jour";
                }
                break;

            case 'delete_user':
                if ($db) {
                    // Sécurité: empêcher la suppression du dernier administrateur
                    $userId = (int)($_POST['user_id'] ?? 0);
                    if ($userId > 0) {
                        try {
                            // Récupérer le rôle de l'utilisateur
                            $stmt = $db->prepare("SELECT role FROM users WHERE id = ?");
                            $stmt->execute([$userId]);
                            $user = $stmt->fetch(PDO::FETCH_ASSOC);

                            if ($user) {
                                if (($user['role'] ?? 'user') === 'admin') {
                                    // Compter le nombre total d'admins
                                    $countStmt = $db->query("SELECT COUNT(*) AS total FROM users WHERE role = 'admin'");
                                    $totalAdmins = (int)($countStmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
                                    if ($totalAdmins <= 1) {
                                        $success = false;
                                        $message = "Impossible de supprimer le dernier administrateur.";
                                        break;
                                    }
                                }

                                // Supprimer l'utilisateur
                                $delStmt = $db->prepare("DELETE FROM users WHERE id = ?");
                                $success = $delStmt->execute([$userId]);
                                $message = $success ? "Utilisateur supprimé avec succès !" : "Erreur lors de la suppression";
                            } else {
                                $success = false;
                                $message = "Utilisateur introuvable.";
                            }
                        } catch (PDOException $e) {
                            $success = false;
                            $message = "Erreur lors de la suppression: " . $e->getMessage();
                        }
                    } else {
                        $success = false;
                        $message = "Identifiant utilisateur invalide.";
                    }
                }
                break;
        }
        
        // Redirection avec message
        $status = $success ? 'success' : 'error';
        header("Location: gestion_equipe.php?status=$status&message=" . urlencode($message));
        exit;
    }
}

// Récupération des utilisateurs
function getUsers($db) {
    if (!$db) return [];
    
    try {
        // Vérifier si les colonnes existent
        $stmt = $db->query("SHOW COLUMNS FROM users LIKE 'role'");
        $roleExists = $stmt->rowCount() > 0;
        
        if ($roleExists) {
            $stmt = $db->query("
                SELECT id, name, email, role, permissions, created_at, last_login
                FROM users 
                ORDER BY created_at DESC
            ");
        } else {
            // Structure de base sans les nouvelles colonnes
            $stmt = $db->query("
                SELECT id, name, email, created_at, updated_at
                FROM users 
                ORDER BY created_at DESC
            ");
        }
        
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Ajouter les valeurs par défaut si les colonnes n'existent pas
        foreach ($users as &$user) {
            if (!isset($user['role'])) {
                $user['role'] = 'user';
            }
            if (!isset($user['permissions'])) {
                $user['permissions'] = '["animaux", "stocks", "activites"]';
            }
            if (!isset($user['last_login'])) {
                $user['last_login'] = null;
            }
        }
        
        return $users;
    } catch (PDOException $e) {
        error_log("Erreur lors de la récupération des utilisateurs: " . $e->getMessage());
        return [];
    }
}

// Récupération des statistiques
function getTeamStats($db) {
    if (!$db) return [];
    
    try {
        $stats = [];
        
        // Total des utilisateurs
        $stmt = $db->query("SELECT COUNT(*) as total FROM users");
        $stats['total_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Vérifier si les colonnes existent
        $stmt = $db->query("SHOW COLUMNS FROM users LIKE 'last_login'");
        $lastLoginExists = $stmt->rowCount() > 0;
        
        if ($lastLoginExists) {
            // Utilisateurs actifs
            $stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE last_login >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
            $stats['active_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        } else {
            $stats['active_users'] = $stats['total_users']; // Tous les utilisateurs sont considérés comme actifs
        }
        
        $stmt = $db->query("SHOW COLUMNS FROM users LIKE 'role'");
        $roleExists = $stmt->rowCount() > 0;
        
        if ($roleExists) {
            // Administrateurs
            $stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE role = 'admin'");
            $stats['admins'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Gestionnaires
            $stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE role = 'manager'");
            $stats['managers'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        } else {
            $stats['admins'] = 1; // L'utilisateur existant est considéré comme admin
            $stats['managers'] = 0;
        }
        
        return $stats;
    } catch (PDOException $e) {
        error_log("Erreur lors de la récupération des statistiques: " . $e->getMessage());
        return [
            'total_users' => 0,
            'active_users' => 0,
            'admins' => 0,
            'managers' => 0
        ];
    }
}

$users = getUsers($db);
$stats = getTeamStats($db);
$dbStatus = $db ? '✅ Connecté' : '❌ Non connecté';
$devise_actuelle = getDeviseActuelle();

// Récupération des messages
$status = $_GET['status'] ?? '';
$message = $_GET['message'] ?? '';

// Définition des rôles et permissions
$roles = [
    'admin' => [
        'nom' => 'Administrateur',
        'description' => 'Accès complet à toutes les fonctionnalités',
        'couleur' => 'danger'
    ],
    'manager' => [
        'nom' => 'Gestionnaire',
        'description' => 'Gestion complète des données',
        'couleur' => 'warning'
    ],
    'user' => [
        'nom' => 'Utilisateur',
        'description' => 'Accès limité en lecture',
        'couleur' => 'info'
    ]
];

$permissions = [
    'animaux' => 'Gestion des animaux',
    'stocks' => 'Gestion des stocks',
    'activites' => 'Gestion des activités',
    'employes' => 'Gestion des employés',
    'alertes' => 'Gestion des alertes',
    'rapports' => 'Génération de rapports',
    'equipe' => 'Gestion de l\'équipe',
    'systeme' => 'Configuration système'
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion d'Équipe - Ferme d'Élevage</title>
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
        .user-card {
            border-left: 4px solid #667eea;
            background: #f8f9fa;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="display-5 text-primary">
                    <i class="fas fa-users-cog"></i> Gestion d'Équipe
                </h1>
                <p class="text-muted">Gérez les accès et permissions de votre équipe</p>
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
                    <i class="fas fa-users fa-2x mb-2"></i>
                    <h3><?= $stats['total_users'] ?? 0 ?></h3>
                    <p class="mb-0">Total utilisateurs</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card text-center">
                    <i class="fas fa-user-check fa-2x mb-2"></i>
                    <h3><?= $stats['active_users'] ?? 0 ?></h3>
                    <p class="mb-0">Utilisateurs actifs</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card text-center">
                    <i class="fas fa-crown fa-2x mb-2"></i>
                    <h3><?= $stats['admins'] ?? 0 ?></h3>
                    <p class="mb-0">Administrateurs</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card text-center">
                    <i class="fas fa-user-tie fa-2x mb-2"></i>
                    <h3><?= $stats['managers'] ?? 0 ?></h3>
                    <p class="mb-0">Gestionnaires</p>
                </div>
            </div>
        </div>

        <!-- Bouton Ajouter -->
        <div class="mb-4">
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="fas fa-plus"></i> Ajouter un utilisateur
            </button>
        </div>

        <!-- Tableau des utilisateurs -->
        <div class="table-container">
            <h4><i class="fas fa-list"></i> Membres de l'équipe (<?= count($users) ?>)</h4>
            
            <?php if (empty($users)): ?>
                <div class="text-center py-4">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Aucun utilisateur enregistré</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Rôle</th>
                                <th>Permissions</th>
                                <th>Date création</th>
                                <th>Dernière connexion</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($user['name']) ?></strong></td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $roles[$user['role']]['couleur'] ?>">
                                            <?= $roles[$user['role']]['nom'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                        $user_permissions = json_decode($user['permissions'], true) ?: [];
                                        foreach ($user_permissions as $perm): ?>
                                            <span class="badge bg-light text-dark me-1"><?= $permissions[$perm] ?? $perm ?></span>
                                        <?php endforeach; ?>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
                                    <td>
                                        <?= $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : 'Jamais' ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="editUser(<?= htmlspecialchars(json_encode($user)) ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="confirmDeleteUser(<?= (int)$user['id'] ?>, '<?= htmlspecialchars($user['name']) ?>', '<?= htmlspecialchars($user['role']) ?>')">
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

    <!-- Modal Ajouter Utilisateur -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus"></i> Ajouter un utilisateur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="add_user">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nom complet *</label>
                                    <input type="text" class="form-control" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Email *</label>
                                    <input type="email" class="form-control" name="email" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Mot de passe *</label>
                                    <input type="password" class="form-control" name="password" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Rôle *</label>
                                    <select class="form-select" name="role" required>
                                        <option value="">Choisir...</option>
                                        <?php foreach ($roles as $key => $role): ?>
                                            <option value="<?= $key ?>"><?= $role['nom'] ?> - <?= $role['description'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Permissions spécifiques</label>
                            <div class="row">
                                <?php foreach ($permissions as $key => $permission): ?>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="permissions[]" value="<?= $key ?>" id="perm_<?= $key ?>">
                                            <label class="form-check-label" for="perm_<?= $key ?>">
                                                <?= $permission ?>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editUser(user) {
            // Implémenter l'édition d'utilisateur
            alert('Fonctionnalité d\'édition à implémenter pour : ' + user.name);
        }

        function confirmDeleteUser(id, name, role) {
            let msg = 'Supprimer l\'utilisateur ' + name + ' ?';
            if (role === 'admin') {
                msg += "\nATTENTION: C'est un administrateur. Le dernier admin ne peut pas être supprimé.";
            }
            if (!confirm(msg)) return;

            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="delete_user">
                <input type="hidden" name="user_id" value="${id}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    </script>
</body>
</html> 