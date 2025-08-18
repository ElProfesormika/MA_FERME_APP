<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'permissions',
        'last_login',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'permissions' => 'array',
        'last_login' => 'datetime',
    ];

    /**
     * Rôles disponibles dans l'application
     */
    const ROLES = [
        'admin' => 'Administrateur',
        'manager' => 'Gestionnaire',
        'user' => 'Utilisateur',
    ];

    /**
     * Permissions disponibles
     */
    const PERMISSIONS = [
        'animaux' => 'Gestion des animaux',
        'stocks' => 'Gestion des stocks',
        'activites' => 'Gestion des activités',
        'employes' => 'Gestion des employés',
        'alertes' => 'Gestion des alertes',
        'rapports' => 'Consultation des rapports',
        'equipe' => 'Gestion de l\'équipe',
        'systeme' => 'Configuration système',
    ];

    /**
     * Vérifie si l'utilisateur a un rôle spécifique
     */
    public function hasRole($role): bool
    {
        return $this->role === $role;
    }

    /**
     * Vérifie si l'utilisateur a au moins un des rôles spécifiés
     */
    public function hasAnyRole($roles): bool
    {
        if (is_string($roles)) {
            return $this->hasRole($roles);
        }

        return in_array($this->role, $roles);
    }

    /**
     * Vérifie si l'utilisateur a une permission spécifique
     */
    public function hasPermission($permission): bool
    {
        // L'admin a toutes les permissions
        if ($this->hasRole('admin')) {
            return true;
        }

        return in_array($permission, $this->permissions ?? []);
    }

    /**
     * Vérifie si l'utilisateur a au moins une des permissions spécifiées
     */
    public function hasAnyPermission($permissions): bool
    {
        if (is_string($permissions)) {
            return $this->hasPermission($permissions);
        }

        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Vérifie si l'utilisateur a toutes les permissions spécifiées
     */
    public function hasAllPermissions($permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Obtient le nom lisible du rôle
     */
    public function getRoleNameAttribute(): string
    {
        return self::ROLES[$this->role] ?? $this->role;
    }

    /**
     * Met à jour la dernière connexion
     */
    public function updateLastLogin(): void
    {
        $this->update(['last_login' => now()]);
    }

    /**
     * Obtient les permissions de l'utilisateur avec leurs noms
     */
    public function getPermissionsWithNames(): array
    {
        $permissions = [];
        foreach ($this->permissions ?? [] as $permission) {
            $permissions[$permission] = self::PERMISSIONS[$permission] ?? $permission;
        }
        return $permissions;
    }

    /**
     * Scope pour filtrer par rôle
     */
    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Scope pour les utilisateurs actifs
     */
    public function scopeActive($query)
    {
        return $query->whereNotNull('email_verified_at');
    }
} 