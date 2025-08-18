<?php

use Illuminate\Support\Facades\Auth;

if (!function_exists('app_name')) {
    function app_name(): string
    {
        return config('app.name', 'Ma FERME D\'ÉLEVAGE');
    }
}

if (!function_exists('app_short_name')) {
    function app_short_name(): string
    {
        return 'BuildNovaG';
    }
}

if (!function_exists('app_slogan')) {
    function app_slogan(): string
    {
        return 'Système de gestion complet pour votre exploitation agricole';
    }
}

if (!function_exists('app_footer_columns')) {
    function app_footer_columns(): array
    {
        return [
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
    }
}

if (!function_exists('app_legal_notice')) {
    function app_legal_notice(): string
    {
        return "Ce logiciel est protégé par les lois sur la propriété intellectuelle. Toute reproduction ou distribution non autorisée est strictement interdite.#Ismaila.YABRE";
    }
}

// Nouvelles fonctions helper pour les permissions

if (!function_exists('user_has_role')) {
    function user_has_role($role): bool
    {
        if (!Auth::check()) {
            return false;
        }
        
        return Auth::user()->hasRole($role);
    }
}

if (!function_exists('user_has_any_role')) {
    function user_has_any_role($roles): bool
    {
        if (!Auth::check()) {
            return false;
        }
        
        return Auth::user()->hasAnyRole($roles);
    }
}

if (!function_exists('user_has_permission')) {
    function user_has_permission($permission): bool
    {
        if (!Auth::check()) {
            return false;
        }
        
        return Auth::user()->hasPermission($permission);
    }
}

if (!function_exists('user_has_any_permission')) {
    function user_has_any_permission($permissions): bool
    {
        if (!Auth::check()) {
            return false;
        }
        
        return Auth::user()->hasAnyPermission($permissions);
    }
}

if (!function_exists('user_has_all_permissions')) {
    function user_has_all_permissions($permissions): bool
    {
        if (!Auth::check()) {
            return false;
        }
        
        return Auth::user()->hasAllPermissions($permissions);
    }
}

if (!function_exists('get_user_role_name')) {
    function get_user_role_name(): string
    {
        if (!Auth::check()) {
            return '';
        }
        
        return Auth::user()->role_name;
    }
}

if (!function_exists('get_user_permissions')) {
    function get_user_permissions(): array
    {
        if (!Auth::check()) {
            return [];
        }
        
        return Auth::user()->getPermissionsWithNames();
    }
}

if (!function_exists('is_admin')) {
    function is_admin(): bool
    {
        return user_has_role('admin');
    }
}

if (!function_exists('is_manager')) {
    function is_manager(): bool
    {
        return user_has_role('manager');
    }
}

if (!function_exists('is_user')) {
    function is_user(): bool
    {
        return user_has_role('user');
    }
} 