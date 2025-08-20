# 🚀 Guide de Déploiement sur Railway

## 📋 Prérequis
- Compte GitHub (pour héberger le code)
- Compte Railway (gratuit)

## 🎯 Étapes de Déploiement

### 1. **Préparer le Repository GitHub**

1. **Créer un nouveau repository sur GitHub**
   - Allez sur [github.com](https://github.com)
   - Cliquez sur "New repository"
   - Nommez-le `ferme-app` ou `projet-ismaila`
   - Laissez-le public ou privé selon votre préférence

2. **Pousser le code vers GitHub**
   ```bash
   git init
   git add .
   git commit -m "Initial commit - Application Ferme"
   git branch -M main
   git remote add origin https://github.com/VOTRE_USERNAME/ferme-app.git
   git push -u origin main
   ```

### 2. **Créer un compte Railway**

1. **Aller sur Railway**
   - Visitez [railway.app](https://railway.app)
   - Cliquez sur "Start a New Project"

2. **Se connecter avec GitHub**
   - Choisissez "Deploy from GitHub repo"
   - Autorisez Railway à accéder à votre GitHub
   - Sélectionnez votre repository `ferme-app`

### 3. **Configurer le Projet**

1. **Railway détectera automatiquement PHP**
   - Le fichier `railway.json` sera utilisé
   - Le fichier `nixpacks.toml` configurera PHP

2. **Attendre le premier déploiement**
   - Railway va construire votre application
   - Cela peut prendre 2-5 minutes

### 4. **Configurer la Base de Données**

1. **Ajouter une base de données MySQL**
   - Dans votre projet Railway
   - Cliquez sur "New"
   - Sélectionnez "Database" → "MySQL"

2. **Configurer les variables d'environnement**
   - Railway créera automatiquement les variables :
     - `MYSQL_HOST`
     - `MYSQL_DATABASE`
     - `MYSQL_USERNAME`
     - `MYSQL_PASSWORD`

3. **Initialiser la base de données**
   - Dans l'onglet "Variables" de votre base de données
   - Cliquez sur "Connect" → "MySQL"
   - Copiez le contenu de `railway_init.sql`
   - Exécutez le script dans l'interface MySQL

### 5. **Tester l'Application**

1. **Accéder à votre application**
   - Railway vous donnera une URL comme : `https://votre-app.railway.app`
   - Cliquez sur l'URL pour tester

2. **Se connecter avec les comptes de test**
   - **Admin :** `admin@ferme.com` / `password`
   - **Manager :** `manager@ferme.com` / `password`
   - **Employé :** `employe@ferme.com` / `password`
   - **Observateur :** `observateur@ferme.com` / `password`

## 🔧 Configuration Avancée

### Variables d'Environnement Personnalisées

Vous pouvez ajouter des variables personnalisées dans Railway :

```bash
APP_ENV=production
APP_DEBUG=false
APP_URL=https://votre-app.railway.app
```

### Domaine Personnalisé (Optionnel)

1. **Dans Railway, allez dans "Settings"**
2. **Cliquez sur "Custom Domains"**
3. **Ajoutez votre domaine**

## 📊 Monitoring et Logs

### Voir les Logs
- Dans Railway, cliquez sur votre service
- Onglet "Deployments" → "View Logs"

### Monitoring
- Railway fournit des métriques automatiques
- Temps de réponse, utilisation CPU/RAM

## 🔒 Sécurité

### Recommandations
1. **Changer les mots de passe par défaut**
2. **Utiliser HTTPS** (automatique sur Railway)
3. **Limiter l'accès** aux utilisateurs nécessaires

### Mise à Jour
```bash
# Pour mettre à jour l'application
git add .
git commit -m "Update application"
git push origin main
# Railway redéploiera automatiquement
```

## 🆘 Dépannage

### Problèmes Courants

1. **Erreur de connexion à la base de données**
   - Vérifiez les variables d'environnement
   - Assurez-vous que la base de données est créée

2. **Page blanche**
   - Vérifiez les logs dans Railway
   - Vérifiez les erreurs PHP

3. **Déploiement échoue**
   - Vérifiez la syntaxe PHP
   - Vérifiez les dépendances

### Support
- **Railway Docs :** [docs.railway.app](https://docs.railway.app)
- **Community :** [discord.gg/railway](https://discord.gg/railway)

## 🎉 Félicitations !

Votre application est maintenant déployée sur Railway ! 🚀

**URL de votre application :** `https://votre-app.railway.app`

**Prochaines étapes :**
1. Testez toutes les fonctionnalités
2. Changez les mots de passe par défaut
3. Ajoutez vos vraies données
4. Configurez un domaine personnalisé (optionnel)

---

*Ce guide vous accompagne pour un déploiement réussi sur Railway !* 🎯
