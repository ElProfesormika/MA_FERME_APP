# Ferme d'Élevage - Système de Gestion

## Description
Système de gestion complet pour exploitation agricole et d'élevage. Application web moderne avec interface responsive et fonctionnalités avancées.

## Fonctionnalités

### 🐄 Gestion des Animaux
- Suivi du cheptel (vaches, taureaux, chèvres, poules, etc.)
- Historique sanitaire et reproductif
- Gestion des poids et statuts
- Calcul des valeurs d'achat/vente

### 📦 Gestion des Stocks
- Inventaire des produits et fournitures
- Alertes de rupture et péremption
- Suivi des fournisseurs
- Calcul des valeurs de stock

### 👥 Gestion des Employés
- Fiche du personnel
- Postes et responsabilités
- Planning et congés
- Suivi des salaires

### 📅 Gestion des Activités
- Planning des tâches quotidiennes
- Assignation d'employés
- Suivi des priorités
- Historique des activités

### 🔔 Système d'Alertes
- Surveillance automatique
- Détection de problèmes
- Gestion des priorités
- Notifications en temps réel

### 📊 Rapports et Exports
- Statistiques détaillées
- Exports PDF et CSV
- Tableaux de bord
- Analyses financières

### 💰 Gestion Financière
- Suivi des ventes et achats
- Gestion des dépenses et revenus
- Convertisseur de devises
- Rapports financiers

### 👨‍💼 Gestion d'Équipe
- Gestion des utilisateurs
- Rôles et permissions
- Sécurité des accès
- Audit des actions

## Installation

### Prérequis
- Serveur web (Apache/Nginx)
- PHP 7.4 ou supérieur
- MySQL 5.7 ou supérieur
- WAMP/XAMPP (pour développement local)

### Installation Locale (WAMP)

1. **Cloner ou télécharger le projet**
```bash
   # Copier les fichiers dans le dossier www de WAMP
   C:\wamp64\www\ferme_app\
   ```

2. **Créer la base de données**
   - Ouvrir phpMyAdmin (http://localhost/phpmyadmin)
   - Créer une base de données nommée `ferme_db`
   - Importer le fichier `database_setup_fixed.sql`

3. **Configurer la base de données**
   - Ouvrir `http://localhost/ferme_app/check_database.php`
   - Vérifier la connexion à la base de données

4. **Accéder à l'application**
   - Ouvrir `http://localhost/ferme_app/`
   - L'application redirigera automatiquement vers le tableau de bord

### Installation sur InfinityFree

1. **Uploader les fichiers**
   - Télécharger `ferme_infinityfree_ready.zip`
   - Décompresser dans le dossier `htdocs` d'InfinityFree

2. **Créer la base de données**
   - Créer une base de données MySQL sur InfinityFree
   - Noter les informations de connexion

3. **Configurer l'application**
   - Ouvrir `install_infinityfree.php`
   - Saisir les informations de base de données
   - Importer `database_setup_fixed.sql` dans phpMyAdmin

4. **Nettoyer l'installation**
   - Exécuter `clean_installation.php` (une seule fois)
   - Accéder à l'application via `index.php`

## Configuration

### Fichiers de configuration
- `config_infinityfree.php` : Configuration principale
- `db_config.php` : Configuration base de données
- `config_app.php` : Configuration application
- `config_devises.php` : Configuration devises

### Variables d'environnement
- `APP_NAME` : Nom de l'application
- `APP_SLOGAN` : Slogan de l'application
- `DEVISE_DEFAUT` : Devise par défaut (FCFA)

## Utilisation

### Interface principale
- **Tableau de bord** : Vue d'ensemble avec statistiques
- **Navigation** : Menu principal avec tous les modules
- **Devise** : Sélecteur de devise en haut à droite

### Modules principaux
1. **Animaux** : Gestion du cheptel
2. **Stocks** : Inventaire et fournitures
3. **Employés** : Gestion du personnel
4. **Activités** : Planning des tâches
5. **Alertes** : Notifications système
6. **Rapports** : Exports et statistiques
7. **Équipe** : Gestion des utilisateurs

### Fonctionnalités avancées
- **PWA** : Installation comme application mobile
- **Mode hors ligne** : Fonctionnement sans connexion
- **Responsive** : Interface adaptée mobile/tablette
- **Multi-devises** : Support FCFA, EUR, USD

## Sécurité

### Protection des fichiers
- `.htaccess` : Protection des fichiers sensibles
- `db_config.php.backup` : Fichier de configuration protégé
- Validation des entrées utilisateur
- Protection CSRF

### Gestion des utilisateurs
- Rôles : admin, manager, employé, visiteur
- Permissions granulaires
- Sessions sécurisées
- Audit des connexions

## Maintenance

### Sauvegarde
- Exporter régulièrement la base de données
- Sauvegarder les fichiers de configuration
- Vérifier les logs d'erreur

### Mise à jour
- Vérifier les nouvelles versions
- Tester en environnement de développement
- Sauvegarder avant mise à jour

## Support

### Documentation
- Guide d'installation détaillé
- Manuel utilisateur
- FAQ et dépannage

### Contact
- Support technique : support@ferme.com
- Développeur : Ismaila YABRE
- Version : 2.0.0

## Licence

Ce logiciel est protégé par les lois sur la propriété intellectuelle.
Toute reproduction ou distribution non autorisée est strictement interdite.

© 2025 Ferme d'Élevage - Tous droits réservés 