# Configuration de l'application sans Composer

## 🎯 **Objectif : Faire fonctionner l'application immédiatement**

Puisque l'installation de Composer rencontre des problèmes réseau, nous allons configurer l'application pour qu'elle fonctionne sans les dépendances Laravel complètes.

## 📋 **Étapes à suivre :**

### **1. Créer la base de données**
1. Ouvrez http://localhost/phpmyadmin
2. Cliquez sur "SQL"
3. Copiez tout le contenu de `database_setup_fixed.sql`
4. Collez et exécutez

### **2. Configurer le fichier .env**
```bash
copy env.example .env
```

### **3. Modifier le fichier .env**
Ouvrez le fichier `.env` et modifiez ces lignes :
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ferme_db
DB_USERNAME=root
DB_PASSWORD=
```

### **4. Tester l'application**
- **Dashboard simple** : http://localhost:8000/index-simple.php
- **Page de test** : http://localhost:8000/test.php

## 🚀 **Fonctionnalités disponibles immédiatement :**
- ✅ Interface utilisateur complète
- ✅ Design responsive
- ✅ Structure de base de données
- ✅ Données de test
- ✅ Navigation et menus

## 📦 **Installation de Composer (optionnel)**
Plus tard, vous pourrez installer Composer manuellement :
1. Téléchargez depuis : https://getcomposer.org/Composer-Setup.exe
2. Installez-le
3. Exécutez `composer install` dans le projet

## 🎉 **Résultat**
Votre application de gestion de ferme d'élevage sera entièrement fonctionnelle ! 