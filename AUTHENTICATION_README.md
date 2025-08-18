# Système d'Authentification - Ferme d'Élevage

## Vue d'ensemble

Le système d'authentification de l'application Ferme d'Élevage offre une gestion complète des utilisateurs avec des rôles et permissions granulaires. Il est basé sur Laravel et inclut toutes les fonctionnalités modernes d'authentification.

## Fonctionnalités

### 🔐 Authentification de base
- **Inscription** : Création de compte avec sélection de rôle
- **Connexion** : Authentification sécurisée
- **Déconnexion** : Fermeture de session sécurisée
- **Récupération de mot de passe** : Système complet de réinitialisation

### 👥 Gestion des rôles
- **Administrateur** : Accès complet à toutes les fonctionnalités
- **Gestionnaire** : Accès à la gestion quotidienne de la ferme
- **Utilisateur** : Accès limité aux fonctionnalités de base

### 🔑 Système de permissions
- **Permissions granulaires** : Contrôle d'accès par fonctionnalité
- **Permissions par défaut** : Attribution automatique selon le rôle
- **Permissions personnalisées** : Possibilité de modifier les permissions par utilisateur

### 👤 Gestion des profils
- **Profil utilisateur** : Consultation et modification des informations
- **Changement de mot de passe** : Mise à jour sécurisée
- **Historique des connexions** : Suivi des dernières connexions

## Structure des fichiers

### Modèles
```
app/Models/User.php                    # Modèle utilisateur avec gestion des rôles
app/Policies/UserPolicy.php            # Politiques d'autorisation
```

### Contrôleurs
```
app/Http/Controllers/Auth/
├── AuthenticatedSessionController.php # Connexion/Déconnexion
├── RegisteredUserController.php       # Inscription
├── ForgotPasswordController.php       # Récupération mot de passe
└── ResetPasswordController.php        # Réinitialisation mot de passe

app/Http/Controllers/ProfileController.php # Gestion des profils
```

### Middlewares
```
app/Http/Middleware/
├── CheckRole.php                      # Vérification des rôles
└── CheckPermission.php                # Vérification des permissions
```

### Vues
```
resources/views/auth/
├── login.blade.php                    # Page de connexion
├── register.blade.php                 # Page d'inscription
├── forgot-password.blade.php          # Demande de récupération
└── reset-password.blade.php           # Réinitialisation

resources/views/profile/
├── show.blade.php                     # Affichage du profil
├── edit.blade.php                     # Modification du profil
├── change-password.blade.php          # Changement de mot de passe
└── index.blade.php                    # Liste des utilisateurs (admin)
```

### Composants
```
resources/views/components/
├── auth-menu.blade.php                # Menu utilisateur
└── permission-gate.blade.php          # Contrôle d'affichage
```

## Utilisation

### Inscription d'un nouvel utilisateur

```php
// Création avec rôle par défaut
$user = User::create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => Hash::make('password'),
    'role' => 'manager',
    'permissions' => ['animaux', 'stocks', 'activites']
]);
```

### Vérification des permissions

```php
// Dans les contrôleurs
if ($user->hasPermission('animaux')) {
    // Accès autorisé
}

// Dans les vues
@if(user_has_permission('animaux'))
    <!-- Contenu protégé -->
@endif

// Avec le composant
<x-permission-gate permission="animaux">
    <div>Contenu protégé</div>
</x-permission-gate>
```

### Protection des routes

```php
// Protection par rôle
Route::middleware(['role:admin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index']);
});

// Protection par permission
Route::middleware(['permission:animaux'])->group(function () {
    Route::resource('animaux', AnimalController::class);
});
```

### Middlewares disponibles

```php
// Vérification de rôle
'role:admin'           // Utilisateur doit avoir le rôle admin
'role:admin,manager'   // Utilisateur doit avoir admin OU manager

// Vérification de permission
'permission:animaux'           // Utilisateur doit avoir la permission animaux
'permission:animaux,stocks'    // Utilisateur doit avoir animaux OU stocks
```

## Configuration

### Fichier de configuration des permissions

```php
// config/permissions.php
return [
    'permissions' => [
        'animaux' => [
            'name' => 'Gestion des animaux',
            'description' => 'Permet de gérer les animaux',
            'default_roles' => ['admin', 'manager', 'user']
        ],
        // ... autres permissions
    ],
    
    'roles' => [
        'admin' => [
            'name' => 'Administrateur',
            'permissions' => ['animaux', 'stocks', 'activites', ...]
        ],
        // ... autres rôles
    ]
];
```

### Helpers disponibles

```php
// Vérification de rôles
user_has_role('admin')
user_has_any_role(['admin', 'manager'])
is_admin()
is_manager()
is_user()

// Vérification de permissions
user_has_permission('animaux')
user_has_any_permission(['animaux', 'stocks'])
user_has_all_permissions(['animaux', 'stocks'])

// Informations utilisateur
get_user_role_name()
get_user_permissions()
```

## Sécurité

### Fonctionnalités de sécurité incluses

- **Hachage des mots de passe** : Utilisation de bcrypt
- **Protection CSRF** : Tokens automatiques
- **Validation des données** : Règles strictes
- **Protection contre les attaques** : Middlewares de sécurité
- **Sessions sécurisées** : Gestion automatique
- **Récupération de mot de passe sécurisée** : Tokens temporaires

### Bonnes pratiques

1. **Toujours vérifier les permissions** avant d'accorder l'accès
2. **Utiliser les middlewares** pour protéger les routes
3. **Valider les données** côté serveur et client
4. **Logger les actions sensibles** pour audit
5. **Utiliser HTTPS** en production
6. **Changer régulièrement les mots de passe**

## Base de données

### Table `users`

```sql
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(191) NOT NULL,
    email VARCHAR(191) NOT NULL UNIQUE,
    role ENUM('admin', 'manager', 'user') DEFAULT 'user',
    permissions JSON DEFAULT NULL,
    last_login TIMESTAMP NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_users_role (role),
    INDEX idx_users_email (email)
);
```

## Tests

### Exemples de tests

```php
// Test de création d'utilisateur
public function test_can_create_user_with_role()
{
    $user = User::factory()->create(['role' => 'manager']);
    
    $this->assertTrue($user->hasRole('manager'));
    $this->assertTrue($user->hasPermission('animaux'));
}

// Test de middleware
public function test_role_middleware_blocks_unauthorized_access()
{
    $user = User::factory()->create(['role' => 'user']);
    
    $response = $this->actingAs($user)
        ->get('/admin');
        
    $response->assertStatus(403);
}
```

## Maintenance

### Commandes utiles

```bash
# Créer un utilisateur admin
php artisan tinker
User::create(['name' => 'Admin', 'email' => 'admin@ferme.com', 'password' => Hash::make('password'), 'role' => 'admin']);

# Vider les sessions expirées
php artisan session:table
php artisan migrate

# Optimiser l'application
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Support

Pour toute question ou problème avec le système d'authentification, consultez :

1. La documentation Laravel officielle
2. Les logs d'erreur dans `storage/logs/`
3. La documentation des middlewares personnalisés
4. Les tests unitaires pour des exemples d'utilisation

---

**Développé par Ismaila YABRE**  
*Système de gestion de ferme d'élevage*
