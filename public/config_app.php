<?php
// Configuration centrale de l'application (noms, slogan, textes, couleurs)

$APP_NAME = "Ma FERME D'ÉLEVAGE";
$APP_SHORT_NAME = "BuildNovaG";
$APP_SLOGAN = "Système de gestion complet pour votre exploitation agricole";

$APP_FOOTER_COLUMNS = [
    [
        'title' => '© ' . date('Y') . " Ferme d'Élevage",
        'lines' => ['Tous droits réservés']
    ],
    [
        'title' => 'Système de Gestion',
        'lines' => ["Solution complète pour l'élevage"]
    ],
    [
        'title' => 'Support Technique',
        'lines' => ['Assistance et maintenance']
    ]
];

$APP_LEGAL_NOTICE = "Ce logiciel est protégé par les lois sur la propriété intellectuelle. Toute reproduction ou distribution non autorisée est strictement interdite.#Ismaila.YABRE";

function app_name(): string { global $APP_NAME; return $APP_NAME; }
function app_short_name(): string { global $APP_SHORT_NAME; return $APP_SHORT_NAME; }
function app_slogan(): string { global $APP_SLOGAN; return $APP_SLOGAN; }
function app_footer_columns(): array { global $APP_FOOTER_COLUMNS; return $APP_FOOTER_COLUMNS; }
function app_legal_notice(): string { global $APP_LEGAL_NOTICE; return $APP_LEGAL_NOTICE; }

?>


