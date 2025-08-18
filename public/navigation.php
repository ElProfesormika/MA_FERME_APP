<?php
// Fichier de navigation centralisé - Ferme d'Élevage
// Tous les liens de l'application sont définis ici

// Configuration des liens principaux
$navigation = [
    'accueil' => [
        'url' => 'index_final.php',
        'titre' => 'Accueil',
        'icone' => 'fas fa-home',
        'description' => 'Menu principal'
    ],
    'dashboard' => [
        'url' => 'dashboard_fixed.php',
        'titre' => 'Dashboard',
        'icone' => 'fas fa-chart-line',
        'description' => 'Tableau de bord complet'
    ],
    
    // Modules de gestion
    'animaux' => [
        'url' => 'animaux_improved.php',
        'titre' => 'Gestion des Animaux',
        'icone' => 'fas fa-cow',
        'description' => 'Gérer le cheptel'
    ],
    'stocks' => [
        'url' => 'stocks_improved.php',
        'titre' => 'Gestion des Stocks',
        'icone' => 'fas fa-boxes',
        'description' => 'Inventaire et alertes'
    ],
    'activites' => [
        'url' => 'activites_improved.php',
        'titre' => 'Gestion des Activités',
        'icone' => 'fas fa-tasks',
        'description' => 'Planning et suivi'
    ],
    'employes' => [
        'url' => 'employes_improved.php',
        'titre' => 'Gestion des Employés',
        'icone' => 'fas fa-users',
        'description' => 'Personnel et responsabilités'
    ],
    'alertes' => [
        'url' => 'alertes_improved.php',
        'titre' => 'Système d\'Alertes',
        'icone' => 'fas fa-bell',
        'description' => 'Surveillance et notifications'
    ],
    'rapports' => [
        'url' => 'rapports.php',
        'titre' => 'Rapports et Exports',
        'icone' => 'fas fa-chart-bar',
        'description' => 'Génération de rapports'
    ],
    'equipe' => [
        'url' => 'gestion_equipe.php',
        'titre' => 'Gestion d\'Équipe',
        'icone' => 'fas fa-users-cog',
        'description' => 'Accès et permissions'
    ],
    
    // Outils et utilitaires
    'convertisseur' => [
        'url' => 'convertisseur_devises.php',
        'titre' => 'Convertisseur de Devises',
        'icone' => 'fas fa-exchange-alt',
        'description' => 'Conversion entre devises'
    ],
    
    // Outils de développement et maintenance
    'test_db' => [
        'url' => 'test_db.php',
        'titre' => 'Test Base de Données',
        'icone' => 'fas fa-database',
        'description' => 'Vérifier la connexion DB'
    ],
    'setup_db' => [
        'url' => 'setup_database.php',
        'titre' => 'Configuration DB',
        'icone' => 'fas fa-cogs',
        'description' => 'Initialiser la base de données'
    ],
    'update_db' => [
        'url' => 'update_database.php',
        'titre' => 'Mise à jour DB',
        'icone' => 'fas fa-sync',
        'description' => 'Mettre à jour la structure'
    ],
    'repair_db' => [
        'url' => 'repair_database.php',
        'titre' => 'Réparation DB',
        'icone' => 'fas fa-tools',
        'description' => 'Corriger les problèmes'
    ],
    'test_system' => [
        'url' => 'test_system.php',
        'titre' => 'Test du Système',
        'icone' => 'fas fa-clipboard-check',
        'description' => 'Vérifier tous les modules'
    ],
    
    // Pages PWA
    'offline' => [
        'url' => 'offline.html',
        'titre' => 'Mode Hors Ligne',
        'icone' => 'fas fa-wifi-slash',
        'description' => 'Page hors ligne'
    ]
];

// Fonction pour obtenir un lien
function getLink($key) {
    global $navigation;
    return $navigation[$key]['url'] ?? 'index_final.php';
}

// Fonction pour obtenir les informations d'un lien
function getLinkInfo($key) {
    global $navigation;
    return $navigation[$key] ?? null;
}

// Fonction pour générer un bouton de navigation
function generateNavButton($key, $class = 'btn-primary', $text = null) {
    $info = getLinkInfo($key);
    if (!$info) return '';
    
    $text = $text ?: $info['titre'];
    $url = $info['url'];
    $icon = $info['icone'];
    
    return "<a href=\"$url\" class=\"btn $class\">
        <i class=\"$icon\"></i> $text
    </a>";
}

// Fonction pour générer un lien de menu
function generateMenuItem($key, $active = false) {
    $info = getLinkInfo($key);
    if (!$info) return '';
    
    $activeClass = $active ? 'active' : '';
    $url = $info['url'];
    $icon = $info['icone'];
    $titre = $info['titre'];
    
    return "<a class=\"nav-link $activeClass\" href=\"$url\">
        <i class=\"$icon\"></i> $titre
    </a>";
}

// Fonction pour générer un breadcrumb
function generateBreadcrumb($currentPage, $parentPage = null) {
    $breadcrumb = '<nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="' . getLink('accueil') . '">
                    <i class="fas fa-home"></i> Accueil
                </a>
            </li>';
    
    if ($parentPage) {
        $parentInfo = getLinkInfo($parentPage);
        if ($parentInfo) {
            $breadcrumb .= '<li class="breadcrumb-item">
                <a href="' . $parentInfo['url'] . '">' . $parentInfo['titre'] . '</a>
            </li>';
        }
    }
    
    $currentInfo = getLinkInfo($currentPage);
    if ($currentInfo) {
        $breadcrumb .= '<li class="breadcrumb-item active" aria-current="page">' . $currentInfo['titre'] . '</li>';
    }
    
    $breadcrumb .= '</ol></nav>';
    return $breadcrumb;
}

// Fonction pour générer un bouton "Retour à l'accueil"
function generateHomeButton($class = 'btn-secondary') {
    return '<a href="' . getLink('accueil') . '" class="btn ' . $class . '">
        <i class="fas fa-home"></i> Accueil
    </a>';
}

// Fonction pour générer un bouton "Retour au menu"
function generateMenuButton($class = 'btn-secondary') {
    return '<a href="' . getLink('accueil') . '" class="btn ' . $class . '">
        <i class="fas fa-bars"></i> Menu principal
    </a>';
}

// Fonction pour vérifier si un fichier existe
function checkFileExists($key) {
    $info = getLinkInfo($key);
    if (!$info) return false;
    
    $filePath = __DIR__ . '/' . $info['url'];
    return file_exists($filePath);
}

// Fonction pour générer un menu de navigation complet
function generateNavigationMenu($currentPage = null) {
    global $navigation;
    
    $menu = '<div class="row">';
    
    foreach ($navigation as $key => $info) {
        if (in_array($key, ['accueil', 'dashboard'])) continue; // Skip main pages
        
        $activeClass = ($currentPage === $key) ? 'active' : '';
        $menu .= '<div class="col-md-6 col-lg-4 mb-3">
            <div class="card h-100 ' . $activeClass . '">
                <div class="card-body text-center">
                    <i class="' . $info['icone'] . ' fa-3x text-primary mb-3"></i>
                    <h5 class="card-title">' . $info['titre'] . '</h5>
                    <p class="card-text">' . $info['description'] . '</p>
                    <a href="' . $info['url'] . '" class="btn btn-primary">
                        <i class="' . $info['icone'] . '"></i> Accéder
                    </a>
                </div>
            </div>
        </div>';
    }
    
    $menu .= '</div>';
    return $menu;
}

// Fonction pour générer des liens rapides
function generateQuickLinks($exclude = []) {
    global $navigation;
    
    $links = '<div class="d-flex flex-wrap gap-2">';
    
    foreach ($navigation as $key => $info) {
        if (in_array($key, $exclude)) continue;
        
        $links .= '<a href="' . $info['url'] . '" class="btn btn-outline-primary btn-sm">
            <i class="' . $info['icone'] . '"></i> ' . $info['titre'] . '
        </a>';
    }
    
    $links .= '</div>';
    return $links;
}

// Fonction pour générer un footer avec liens
function generateFooter() {
    return '<footer class="text-center mt-5 pt-4 border-top">
        <div class="row">
            <div class="col-md-4">
                <h6 class="text-muted">© 2025 Ferme d\'Élevage</h6>
                <p class="small text-muted">Tous droits réservés</p>
            </div>
            <div class="col-md-4">
                <h6 class="text-muted">Système de Gestion</h6>
                <p class="small text-muted">Solution complète pour l\'élevage</p>
            </div>
            <div class="col-md-4">
                <h6 class="text-muted">Support Technique</h6>
                <p class="small text-muted">Assistance et maintenance</p>
            </div>
        </div>
        <hr class="my-3">
        <p class="small text-muted mb-0">
            Ce logiciel est protégé par les lois sur la propriété intellectuelle. 
            Toute reproduction ou distribution non autorisée est strictement interdite.
        </p>
    </footer>';
}

// Fonction pour générer un header avec navigation
function generateHeader($title = 'Ferme d\'Élevage', $showDeviseSelector = true) {
    global $navigation;
    
    $header = '<div class="d-flex justify-content-between align-items-center mb-4">
        <div class="text-center">
            <h1 class="display-4 fw-bold text-primary mb-3">
                <i class="fas fa-tractor me-3"></i>' . $title . '
            </h1>
        </div>';
    
    if ($showDeviseSelector) {
        $header .= '<div class="d-flex gap-2">
            <form method="POST" class="d-flex align-items-center">
                <label class="form-label me-2 mb-0">Devise :</label>
                <select name="devise" class="form-select form-select-sm me-2" style="width: auto;">
                    <option value="FCFA">FCFA</option>
                    <option value="EUR">€ Euro</option>
                    <option value="USD">$ Dollar</option>
                </select>
                <button type="submit" name="changer_devise" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-sync"></i>
                </button>
            </form>
        </div>';
    }
    
    $header .= '</div>';
    return $header;
}
?> 