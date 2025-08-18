# 🐄 Application de Gestion de Ferme d'Élevage

Une application Laravel robuste et moderne pour la gestion complète d'une ferme d'élevage, incluant la gestion des animaux, des stocks, des employés, des activités et des alertes.

## ✨ Fonctionnalités principales

### 📊 Tableau de bord interactif
- **Statistiques en temps réel** : Nombre d'animaux, employés, alertes critiques
- **Graphiques Chart.js** : Activités par mois, répartition des animaux par espèce
- **Alertes récentes** : Affichage des alertes critiques et importantes
- **Actions rapides** : Accès direct aux fonctions principales

### 🐮 Gestion des animaux
- **CRUD complet** : Création, lecture, modification, suppression
- **Informations détaillées** : Nom, espèce, race, date de naissance, poids, sexe
- **Historique de santé** : Suivi médical et traitements
- **Assignation d'employés** : Responsable de chaque animal
- **Export CSV** : Export des données pour analyse externe
- **Recherche et filtres** : Par espèce, statut, sexe, etc.

### 📦 Gestion des stocks
- **Suivi des produits** : Quantités, dates d'entrée et de péremption
- **Alertes automatiques** : Rupture de stock, produits périmés
- **Catégorisation** : Organisation par type de produit
- **Fournisseurs** : Suivi des sources d'approvisionnement

### 👥 Gestion des employés
- **Profils complets** : Informations personnelles et professionnelles
- **Postes et responsabilités** : Attribution des rôles
- **Suivi des activités** : Historique des tâches effectuées
- **Statuts** : Actif, inactif, congé

### 📋 Gestion des activités
- **Planification** : Création et suivi des tâches
- **Types d'activités** : Nourrissage, soins, nettoyage, maintenance
- **Assignation** : Employés et animaux concernés
- **Statuts** : Planifié, en cours, terminé, annulé
- **Horaires** : Heures de début et de fin

### ⚠️ Système d'alertes
- **Alertes critiques** : Notifications importantes
- **Types variés** : Santé, stock, maintenance
- **Statuts** : Nouvelle, en cours, résolue
- **Notifications email** : Pour les alertes critiques

### 📝 Journal d'activité (Logs)
- **Traçabilité complète** : Toutes les actions utilisateurs
- **Filtres avancés** : Par action, modèle, date, utilisateur
- **Export CSV** : Sauvegarde des logs
- **Nettoyage automatique** : Suppression des anciens logs

### 📈 Statistiques et rapports
- **Graphiques interactifs** : Chart.js pour la visualisation
- **Périodes configurables** : Hebdomadaire, mensuelle
- **Export PDF** : Rapports détaillés
- **API REST** : Pour applications mobiles

## 🛠️ Technologies utilisées

- **Backend** : Laravel 10.x
- **Frontend** : Bootstrap 5, Chart.js
- **Base de données** : MySQL
- **Export** : CSV natif, PDF avec DomPDF
- **Icons** : Font Awesome 6
- **Charts** : Chart.js

## 📋 Prérequis

- PHP 8.1 ou supérieur
- Composer
- MySQL 5.7 ou supérieur
- Node.js et NPM (pour les assets)

## 🚀 Installation

### 1. Cloner le projet
```bash
git clone [url-du-repo]
cd ferme_app
```

### 2. Installer les dépendances
```bash
composer install
npm install
```

### 3. Configuration de l'environnement
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configuration de la base de données
Modifier le fichier `.env` :
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ferme_db
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Créer la base de données
```sql
CREATE DATABASE ferme_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 6. Exécuter les migrations
```bash
php artisan migrate
```

### 7. Compiler les assets (optionnel)
```bash
npm run dev
```

### 8. Démarrer le serveur
```bash
php artisan serve
```

## 📁 Structure du projet

```
ferme_app/
├── app/
│   ├── Http/Controllers/
│   │   ├── AnimalController.php
│   │   ├── DashboardController.php
│   │   ├── LogController.php
│   │   └── ...
│   ├── Models/
│   │   ├── Animal.php
│   │   ├── Stock.php
│   │   ├── Employe.php
│   │   ├── Activite.php
│   │   ├── Alerte.php
│   │   └── Log.php
│   └── ...
├── database/
│   └── migrations/
│       ├── create_animals_table.php
│       ├── create_stocks_table.php
│       ├── create_employes_table.php
│       ├── create_activites_table.php
│       ├── create_alertes_table.php
│       └── create_logs_table.php
├── resources/
│   └── views/
│       ├── layouts/
│       │   └── app.blade.php
│       ├── dashboard/
│       │   └── index.blade.php
│       ├── animaux/
│       │   ├── index.blade.php
│       │   ├── create.blade.php
│       │   └── ...
│       └── ...
└── routes/
    └── web.php
```

## 🔧 Configuration avancée

### Notifications email
Pour activer les notifications d'alertes critiques, configurer dans `.env` :
```env
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@ferme.com
MAIL_FROM_NAME="Ferme d'Élevage"
```

### Sauvegarde automatique
Ajouter dans `app/Console/Kernel.php` :
```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('backup:run')->daily();
}
```

## 📱 API REST

L'application expose une API REST pour les applications mobiles :

### Endpoints disponibles
- `GET /api/animaux` - Liste des animaux
- `GET /api/stocks` - État des stocks
- `GET /api/alertes` - Alertes critiques
- `POST /api/alertes` - Créer une alerte

### Authentification
L'API utilise Laravel Sanctum pour l'authentification.

## 🔒 Sécurité

- **Authentification** : Laravel Breeze
- **Validation** : Règles de validation strictes
- **CSRF Protection** : Protection contre les attaques CSRF
- **Journalisation** : Traçabilité complète des actions
- **Permissions** : Système de rôles et permissions

## 📊 Fonctionnalités avancées

### Statistiques d'activité
- **Vue hebdomadaire** : Activités par semaine
- **Vue mensuelle** : Activités par mois
- **Graphiques interactifs** : Chart.js
- **Export PDF** : Rapports détaillés

### Module de journalisation
- **Logs automatiques** : Toutes les actions CRUD
- **Filtres avancés** : Par action, modèle, utilisateur, date
- **Export CSV** : Sauvegarde des logs
- **Nettoyage automatique** : Suppression des anciens logs

### Interface d'import/export
- **Export CSV** : Données des animaux
- **Import CSV** : Import en lot
- **Validation** : Vérification des données importées
- **Gestion d'erreurs** : Rapport d'erreurs d'import

## 🎨 Interface utilisateur

### Design moderne
- **Bootstrap 5** : Framework CSS moderne
- **Responsive** : Compatible mobile et tablette
- **Thème personnalisé** : Couleurs et styles adaptés
- **Icons Font Awesome** : Interface intuitive

### Navigation
- **Sidebar** : Navigation principale
- **Breadcrumbs** : Navigation contextuelle
- **Actions rapides** : Accès direct aux fonctions
- **Recherche** : Filtres et recherche avancée

## 🚀 Déploiement

### Production
1. Configurer l'environnement de production
2. Optimiser l'application : `php artisan optimize`
3. Configurer le serveur web (Apache/Nginx)
4. Configurer la base de données de production
5. Mettre en place les sauvegardes automatiques

### Docker (optionnel)
```dockerfile
FROM php:8.1-fpm
# Configuration Docker pour la production
```

## 🤝 Contribution

1. Fork le projet
2. Créer une branche feature (`git checkout -b feature/AmazingFeature`)
3. Commit les changements (`git commit -m 'Add some AmazingFeature'`)
4. Push vers la branche (`git push origin feature/AmazingFeature`)
5. Ouvrir une Pull Request

## 📄 Licence

Ce projet est sous licence MIT. Voir le fichier `LICENSE` pour plus de détails.

## 📞 Support

Pour toute question ou problème :
- Ouvrir une issue sur GitHub
- Contacter l'équipe de développement
- Consulter la documentation technique

## 🔄 Mises à jour

### Version 1.0.0
- ✅ Gestion complète des animaux
- ✅ Système d'alertes
- ✅ Tableau de bord interactif
- ✅ Journal d'activité
- ✅ Export/Import CSV
- ✅ API REST

### Prochaines versions
- 🔄 Application mobile native
- 🔄 Intégration IoT (capteurs)
- 🔄 Intelligence artificielle pour les prédictions
- 🔄 Géolocalisation des animaux
- 🔄 Intégration avec des systèmes tiers

---

**Développé avec ❤️ pour la gestion moderne des fermes d'élevage** 