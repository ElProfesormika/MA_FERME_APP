<?php
// Configuration du système d'authentification

// Configuration des rôles et leurs permissions
$ROLES_CONFIG = [
    'admin' => [
        'nom' => 'Administrateur',
        'description' => 'Accès complet à toutes les fonctionnalités',
        'couleur' => 'danger',
        'permissions' => [
            'gestion_utilisateurs' => true,
            'gestion_animaux' => true,
            'gestion_stocks' => true,
            'gestion_employes' => true,
            'gestion_activites' => true,
            'gestion_alertes' => true,
            'rapports' => true,
            'convertisseur_devises' => true,
            'parametres' => true,
            'promotion_roles' => true,
            'suppression_utilisateurs' => true,
            'activation_comptes' => true
        ]
    ],
    'manager' => [
        'nom' => 'Manager',
        'description' => 'Gestion des équipes et rapports',
        'couleur' => 'primary',
        'permissions' => [
            'gestion_utilisateurs' => false,
            'gestion_animaux' => true,
            'gestion_stocks' => true,
            'gestion_employes' => true,
            'gestion_activites' => true,
            'gestion_alertes' => true,
            'rapports' => true,
            'convertisseur_devises' => true,
            'parametres' => false,
            'promotion_roles' => false,
            'suppression_utilisateurs' => false,
            'activation_comptes' => false
        ]
    ],
    'employe' => [
        'nom' => 'Employé',
        'description' => 'Saisie des données et consultations',
        'couleur' => 'success',
        'permissions' => [
            'gestion_utilisateurs' => false,
            'gestion_animaux' => true,
            'gestion_stocks' => true,
            'gestion_employes' => false,
            'gestion_activites' => true,
            'gestion_alertes' => true,
            'rapports' => false,
            'convertisseur_devises' => true,
            'parametres' => false,
            'promotion_roles' => false,
            'suppression_utilisateurs' => false,
            'activation_comptes' => false
        ]
    ],
    'observateur' => [
        'nom' => 'Observateur',
        'description' => 'Consultation uniquement',
        'couleur' => 'info',
        'permissions' => [
            'gestion_utilisateurs' => false,
            'gestion_animaux' => false,
            'gestion_stocks' => false,
            'gestion_employes' => false,
            'gestion_activites' => false,
            'gestion_alertes' => false,
            'rapports' => true,
            'convertisseur_devises' => true,
            'parametres' => false,
            'promotion_roles' => false,
            'suppression_utilisateurs' => false,
            'activation_comptes' => false
        ]
    ]
];

// Fonction pour vérifier si un utilisateur a une permission
function hasPermission($permission) {
    if (!isset($_SESSION['user_role'])) {
        return false;
    }

    global $ROLES_CONFIG;
    $role = $_SESSION['user_role'];

    if (!isset($ROLES_CONFIG[$role])) {
        return false;
    }

    return $ROLES_CONFIG[$role]['permissions'][$permission] ?? false;
}

// Fonction pour obtenir le nom du rôle
function getRoleName($role) {
    global $ROLES_CONFIG;
    return $ROLES_CONFIG[$role]['nom'] ?? 'Rôle inconnu';
}

// Fonction pour obtenir la couleur du rôle
function getRoleColor($role) {
    global $ROLES_CONFIG;
    return $ROLES_CONFIG[$role]['couleur'] ?? 'secondary';
}

// Fonction pour obtenir la description du rôle
function getRoleDescription($role) {
    global $ROLES_CONFIG;
    return $ROLES_CONFIG[$role]['description'] ?? 'Description non disponible';
}

// Fonction pour vérifier si l'utilisateur est connecté
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Fonction pour rediriger si non connecté
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit;
    }
}

// Fonction pour rediriger si pas la permission
function requirePermission($permission) {
    requireLogin();
    if (!hasPermission($permission)) {
        header("Location: access_denied.php");
        exit;
    }
}

// Fonction pour vérifier si l'utilisateur est admin
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Fonction pour obtenir les informations de l'utilisateur connecté
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }

    $db = connectDB();
    if (!$db) {
        return null;
    }

    try {
        $stmt = $db->prepare("SELECT * FROM utilisateurs WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return null;
    }
}

// Fonction pour créer un utilisateur par défaut (admin)
function createDefaultAdmin() {
    $db = connectDB();
    if (!$db) {
        return false;
    }

    try {
        // Vérifier si l'admin existe déjà
        $stmt = $db->prepare("SELECT COUNT(*) FROM utilisateurs WHERE email = ?");
        $stmt->execute(['admin@ferme.com']);
        if ($stmt->fetchColumn() > 0) {
            return true; // Admin existe déjà
        }

        // Créer l'admin par défaut
        $stmt = $db->prepare("
            INSERT INTO utilisateurs (nom_complet, email, mot_de_passe, role, statut, date_creation)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");

        $mot_de_passe_hash = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt->execute(['Administrateur Système', 'admin@ferme.com', $mot_de_passe_hash, 'admin', 'actif']);

        return true;
    } catch (Exception $e) {
        return false;
    }
}

// Fonction pour obtenir tous les rôles disponibles
function getAvailableRoles() {
    global $ROLES_CONFIG;
    return array_keys($ROLES_CONFIG);
}

// Fonction pour vérifier si un rôle peut être promu vers un autre
function canPromoteTo($fromRole, $toRole) {
    global $ROLES_CONFIG;
    
    if (!isset($ROLES_CONFIG[$fromRole]) || !isset($ROLES_CONFIG[$toRole])) {
        return false;
    }
    
    // Seul l'admin peut promouvoir vers admin
    if ($toRole === 'admin' && !isAdmin()) {
        return false;
    }
    
    // Un utilisateur ne peut pas se promouvoir lui-même
    if ($fromRole === $toRole) {
        return false;
    }
    
    return true;
}
?>
