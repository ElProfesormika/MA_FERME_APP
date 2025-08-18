<?php
// Configuration des devises disponibles
$devises = [
    'FCFA' => [
        'nom' => 'Franc CFA',
        'symbole' => 'FCFA',
        'position' => 'after', // après le montant
        'separateur' => ' ',
        'decimales' => 0,
        'taux_euro' => 655.957, // 1 EUR = 655.957 FCFA
        'taux_usd' => 588.95, // 1 USD = 588.95 FCFA
        'couleur' => 'success'
    ],
    'EUR' => [
        'nom' => 'Euro',
        'symbole' => '€',
        'position' => 'before', // avant le montant
        'separateur' => ' ',
        'decimales' => 2,
        'taux_fcfa' => 0.0015, // 1 FCFA = 0.0015 EUR
        'taux_usd' => 1.09, // 1 EUR = 1.09 USD
        'couleur' => 'primary'
    ],
    'USD' => [
        'nom' => 'Dollar US',
        'symbole' => '$',
        'position' => 'before',
        'separateur' => ' ',
        'decimales' => 2,
        'taux_fcfa' => 0.0017, // 1 FCFA = 0.0017 USD
        'taux_eur' => 0.92, // 1 USD = 0.92 EUR
        'couleur' => 'info'
    ]
];

// Fonction pour formater un montant selon la devise
function formaterMontant($montant, $devise = 'FCFA') {
    global $devises;
    
    if (!isset($devises[$devise])) {
        $devise = 'FCFA'; // Devise par défaut
    }
    
    $config = $devises[$devise];
    $montant_formate = number_format($montant, $config['decimales'], ',', ' ');
    
    if ($config['position'] === 'before') {
        return $config['symbole'] . $config['separateur'] . $montant_formate;
    } else {
        return $montant_formate . $config['separateur'] . $config['symbole'];
    }
}

// Fonction pour convertir un montant entre devises
function convertirDevise($montant, $devise_source, $devise_cible) {
    global $devises;
    
    if ($devise_source === $devise_cible) {
        return $montant;
    }
    
    if (!isset($devises[$devise_source]) || !isset($devises[$devise_cible])) {
        return $montant; // Retourne le montant original si devise invalide
    }
    
    // Conversion directe entre devises
    if ($devise_source === 'FCFA') {
        if ($devise_cible === 'EUR') {
            return $montant * $devises['EUR']['taux_fcfa'];
        } elseif ($devise_cible === 'USD') {
            return $montant * $devises['USD']['taux_fcfa'];
        }
    } elseif ($devise_source === 'EUR') {
        if ($devise_cible === 'FCFA') {
            return $montant * $devises['FCFA']['taux_euro'];
        } elseif ($devise_cible === 'USD') {
            return $montant * $devises['EUR']['taux_usd'];
        }
    } elseif ($devise_source === 'USD') {
        if ($devise_cible === 'FCFA') {
            return $montant * $devises['FCFA']['taux_usd'];
        } elseif ($devise_cible === 'EUR') {
            return $montant * $devises['USD']['taux_eur'];
        }
    }
    
    return $montant;
}

// Fonction pour obtenir la devise actuelle (depuis session ou cookie)
function getDeviseActuelle() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (isset($_SESSION['devise'])) {
        return $_SESSION['devise'];
    } elseif (isset($_COOKIE['devise'])) {
        return $_COOKIE['devise'];
    }
    return 'FCFA'; // Devise par défaut
}

// Fonction pour définir la devise actuelle
function setDeviseActuelle($devise) {
    global $devises;
    
    if (isset($devises[$devise])) {
        $_SESSION['devise'] = $devise;
        
        // Vérifier si les headers ont déjà été envoyés avant de définir le cookie
        if (!headers_sent()) {
            setcookie('devise', $devise, time() + (86400 * 30), "/"); // 30 jours
        }
        return true;
    }
    return false;
}

// Fonction pour convertir automatiquement un montant stocké en FCFA vers la devise actuelle
function convertirMontantAutomatique($montant_fcfa) {
    $devise_actuelle = getDeviseActuelle();
    
    if ($devise_actuelle === 'FCFA') {
        return $montant_fcfa;
    } else {
        return convertirDevise($montant_fcfa, 'FCFA', $devise_actuelle);
    }
}

// Fonction pour formater un montant avec conversion automatique
function formaterMontantAutomatique($montant_fcfa) {
    $montant_converti = convertirMontantAutomatique($montant_fcfa);
    return formaterMontant($montant_converti, getDeviseActuelle());
}

// Initialiser la session si pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Traitement du changement de devise si POST
if (isset($_POST['changer_devise']) && isset($_POST['devise'])) {
    setDeviseActuelle($_POST['devise']);
}
?> 