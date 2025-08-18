<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Permissions de l'application
    |--------------------------------------------------------------------------
    |
    | Ce fichier contient toutes les permissions disponibles dans l'application
    | avec leurs descriptions et les rôles qui y ont accès par défaut.
    |
    */

    'permissions' => [
        'animaux' => [
            'name' => 'Gestion des animaux',
            'description' => 'Permet de gérer les animaux (créer, modifier, supprimer, consulter)',
            'default_roles' => ['admin', 'manager', 'user']
        ],
        'stocks' => [
            'name' => 'Gestion des stocks',
            'description' => 'Permet de gérer les stocks (créer, modifier, supprimer, consulter)',
            'default_roles' => ['admin', 'manager', 'user']
        ],
        'activites' => [
            'name' => 'Gestion des activités',
            'description' => 'Permet de gérer les activités (créer, modifier, supprimer, consulter)',
            'default_roles' => ['admin', 'manager', 'user']
        ],
        'employes' => [
            'name' => 'Gestion des employés',
            'description' => 'Permet de gérer les employés (créer, modifier, supprimer, consulter)',
            'default_roles' => ['admin', 'manager']
        ],
        'alertes' => [
            'name' => 'Gestion des alertes',
            'description' => 'Permet de gérer les alertes (créer, modifier, supprimer, consulter)',
            'default_roles' => ['admin', 'manager']
        ],
        'rapports' => [
            'name' => 'Consultation des rapports',
            'description' => 'Permet de consulter les rapports et statistiques',
            'default_roles' => ['admin', 'manager']
        ],
        'equipe' => [
            'name' => 'Gestion de l\'équipe',
            'description' => 'Permet de gérer l\'équipe et les utilisateurs',
            'default_roles' => ['admin']
        ],
        'systeme' => [
            'name' => 'Configuration système',
            'description' => 'Permet de configurer le système et les paramètres',
            'default_roles' => ['admin']
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Rôles de l'application
    |--------------------------------------------------------------------------
    |
    | Ce fichier contient tous les rôles disponibles dans l'application
    | avec leurs descriptions et permissions par défaut.
    |
    */

    'roles' => [
        'admin' => [
            'name' => 'Administrateur',
            'description' => 'Accès complet à toutes les fonctionnalités',
            'permissions' => [
                'animaux', 'stocks', 'activites', 'employes', 
                'alertes', 'rapports', 'equipe', 'systeme'
            ]
        ],
        'manager' => [
            'name' => 'Gestionnaire',
            'description' => 'Accès à la gestion quotidienne de la ferme',
            'permissions' => [
                'animaux', 'stocks', 'activites', 'employes', 
                'alertes', 'rapports'
            ]
        ],
        'user' => [
            'name' => 'Utilisateur',
            'description' => 'Accès limité aux fonctionnalités de base',
            'permissions' => [
                'animaux', 'stocks', 'activites'
            ]
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Permissions par défaut pour les nouveaux utilisateurs
    |--------------------------------------------------------------------------
    |
    | Permissions automatiquement attribuées selon le rôle choisi
    | lors de l'inscription.
    |
    */

    'default_permissions_by_role' => [
        'admin' => [
            'animaux', 'stocks', 'activites', 'employes', 
            'alertes', 'rapports', 'equipe', 'systeme'
        ],
        'manager' => [
            'animaux', 'stocks', 'activites', 'employes', 
            'alertes', 'rapports'
        ],
        'user' => [
            'animaux', 'stocks', 'activites'
        ],
    ],
];
