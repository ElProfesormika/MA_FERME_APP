// Gestionnaire PWA pour Ferme d'Élevage
class PWAManager {
    constructor() {
        this.deferredPrompt = null;
        this.isOnline = navigator.onLine;
        this.pendingData = [];
        
        this.init();
    }
    
    init() {
        this.registerServiceWorker();
        this.setupEventListeners();
        this.checkInstallation();
        this.setupOfflineDetection();
        this.setupDataSync();
    }
    
    // Enregistrer le Service Worker
    async registerServiceWorker() {
        if ('serviceWorker' in navigator) {
            try {
                const registration = await navigator.serviceWorker.register('/sw.js');
                console.log('Service Worker enregistré:', registration);
                
                // Écouter les mises à jour
                registration.addEventListener('updatefound', () => {
                    const newWorker = registration.installing;
                    newWorker.addEventListener('statechange', () => {
                        if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                            this.showUpdateNotification();
                        }
                    });
                });
                
            } catch (error) {
                console.error('Erreur d\'enregistrement du Service Worker:', error);
            }
        }
    }
    
    // Configurer les écouteurs d'événements
    setupEventListeners() {
        // Écouter l'événement d'installation
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            this.deferredPrompt = e;
            this.showInstallButton();
        });
        
        // Écouter l'installation réussie
        window.addEventListener('appinstalled', () => {
            console.log('Application installée avec succès');
            this.hideInstallButton();
            this.showInstallationSuccess();
        });
        
        // Écouter les changements de connectivité
        window.addEventListener('online', () => {
            this.isOnline = true;
            this.onConnectionRestored();
        });
        
        window.addEventListener('offline', () => {
            this.isOnline = false;
            this.onConnectionLost();
        });
    }
    
    // Vérifier si l'application est installée
    checkInstallation() {
        if (window.matchMedia('(display-mode: standalone)').matches) {
            console.log('Application installée en mode standalone');
            this.hideInstallButton();
        }
    }
    
    // Configurer la détection hors ligne
    setupOfflineDetection() {
        if (!this.isOnline) {
            this.onConnectionLost();
        }
    }
    
    // Configurer la synchronisation des données
    setupDataSync() {
        // Stocker les données en attente dans localStorage
        this.loadPendingData();
        
        // Synchroniser périodiquement
        setInterval(() => {
            if (this.isOnline && this.pendingData.length > 0) {
                this.syncPendingData();
            }
        }, 60000); // Toutes les minutes
    }
    
    // Afficher le bouton d'installation
    showInstallButton() {
        const installButton = document.getElementById('pwa-install-btn');
        if (installButton) {
            installButton.style.display = 'block';
            installButton.addEventListener('click', () => {
                this.installApp();
            });
        }
    }
    
    // Masquer le bouton d'installation
    hideInstallButton() {
        const installButton = document.getElementById('pwa-install-btn');
        if (installButton) {
            installButton.style.display = 'none';
        }
    }
    
    // Installer l'application
    async installApp() {
        if (this.deferredPrompt) {
            this.deferredPrompt.prompt();
            const { outcome } = await this.deferredPrompt.userChoice;
            
            if (outcome === 'accepted') {
                console.log('Utilisateur a accepté l\'installation');
            } else {
                console.log('Utilisateur a refusé l\'installation');
            }
            
            this.deferredPrompt = null;
        }
    }
    
    // Afficher la notification de mise à jour
    showUpdateNotification() {
        const updateNotification = document.createElement('div');
        updateNotification.className = 'alert alert-info alert-dismissible fade show position-fixed';
        updateNotification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 300px;';
        updateNotification.innerHTML = `
            <strong>Mise à jour disponible !</strong>
            <p class="mb-2">Une nouvelle version de l'application est disponible.</p>
            <button type="button" class="btn btn-sm btn-primary" onclick="pwaManager.updateApp()">
                Mettre à jour
            </button>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(updateNotification);
    }
    
    // Mettre à jour l'application
    updateApp() {
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.ready.then(registration => {
                registration.update();
                window.location.reload();
            });
        }
    }
    
    // Gérer la perte de connexion
    onConnectionLost() {
        console.log('Connexion perdue');
        
        // Afficher un indicateur hors ligne
        this.showOfflineIndicator();
        
        // Basculer en mode hors ligne
        this.enableOfflineMode();
    }
    
    // Gérer la restauration de connexion
    onConnectionRestored() {
        console.log('Connexion restaurée');
        
        // Masquer l'indicateur hors ligne
        this.hideOfflineIndicator();
        
        // Synchroniser les données en attente
        this.syncPendingData();
        
        // Basculer en mode en ligne
        this.enableOnlineMode();
    }
    
    // Afficher l'indicateur hors ligne
    showOfflineIndicator() {
        let indicator = document.getElementById('offline-indicator');
        if (!indicator) {
            indicator = document.createElement('div');
            indicator.id = 'offline-indicator';
            indicator.className = 'alert alert-warning alert-dismissible fade show position-fixed';
            indicator.style.cssText = 'top: 0; left: 0; right: 0; z-index: 9999; margin: 0; border-radius: 0;';
            indicator.innerHTML = `
                <i class="fas fa-wifi-slash me-2"></i>
                <strong>Mode hors ligne</strong> - Certaines fonctionnalités peuvent être limitées
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(indicator);
        }
    }
    
    // Masquer l'indicateur hors ligne
    hideOfflineIndicator() {
        const indicator = document.getElementById('offline-indicator');
        if (indicator) {
            indicator.remove();
        }
    }
    
    // Activer le mode hors ligne
    enableOfflineMode() {
        // Désactiver les formulaires qui nécessitent une connexion
        const forms = document.querySelectorAll('form[data-requires-online="true"]');
        forms.forEach(form => {
            form.classList.add('offline-disabled');
        });
        
        // Afficher des messages d'information
        this.showOfflineMessages();
    }
    
    // Activer le mode en ligne
    enableOnlineMode() {
        // Réactiver les formulaires
        const forms = document.querySelectorAll('form.offline-disabled');
        forms.forEach(form => {
            form.classList.remove('offline-disabled');
        });
        
        // Masquer les messages hors ligne
        this.hideOfflineMessages();
    }
    
    // Afficher les messages hors ligne
    showOfflineMessages() {
        const messages = document.querySelectorAll('[data-offline-message]');
        messages.forEach(element => {
            element.style.display = 'block';
        });
    }
    
    // Masquer les messages hors ligne
    hideOfflineMessages() {
        const messages = document.querySelectorAll('[data-offline-message]');
        messages.forEach(element => {
            element.style.display = 'none';
        });
    }
    
    // Stocker les données en attente
    storePendingData(action, data) {
        const pendingItem = {
            id: Date.now(),
            action: action,
            data: data,
            timestamp: new Date().toISOString()
        };
        
        this.pendingData.push(pendingItem);
        this.savePendingData();
        
        console.log('Données en attente stockées:', pendingItem);
    }
    
    // Sauvegarder les données en attente
    savePendingData() {
        localStorage.setItem('pwa_pending_data', JSON.stringify(this.pendingData));
    }
    
    // Charger les données en attente
    loadPendingData() {
        const saved = localStorage.getItem('pwa_pending_data');
        if (saved) {
            this.pendingData = JSON.parse(saved);
        }
    }
    
    // Synchroniser les données en attente
    async syncPendingData() {
        if (this.pendingData.length === 0) return;
        
        console.log('Synchronisation de', this.pendingData.length, 'éléments');
        
        const successful = [];
        const failed = [];
        
        for (const item of this.pendingData) {
            try {
                await this.syncDataItem(item);
                successful.push(item);
            } catch (error) {
                failed.push(item);
                console.error('Erreur de synchronisation:', error);
            }
        }
        
        // Supprimer les éléments synchronisés avec succès
        this.pendingData = failed;
        this.savePendingData();
        
        if (successful.length > 0) {
            this.showSyncSuccess(successful.length);
        }
    }
    
    // Synchroniser un élément de données
    async syncDataItem(item) {
        const response = await fetch('/api/sync', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(item)
        });
        
        if (!response.ok) {
            throw new Error('Erreur de synchronisation');
        }
        
        return response.json();
    }
    
    // Afficher le succès de synchronisation
    showSyncSuccess(count) {
        const notification = document.createElement('div');
        notification.className = 'alert alert-success alert-dismissible fade show position-fixed';
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 300px;';
        notification.innerHTML = `
            <i class="fas fa-check-circle me-2"></i>
            <strong>Synchronisation réussie</strong>
            <p class="mb-0">${count} élément(s) synchronisé(s)</p>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        // Supprimer automatiquement après 5 secondes
        setTimeout(() => {
            notification.remove();
        }, 5000);
    }
    
    // Obtenir les statistiques PWA
    getStats() {
        return {
            isOnline: this.isOnline,
            isInstalled: window.matchMedia('(display-mode: standalone)').matches,
            pendingDataCount: this.pendingData.length,
            cacheSupported: 'caches' in window,
            serviceWorkerSupported: 'serviceWorker' in navigator
        };
    }
    
    // Vider le cache
    async clearCache() {
        if ('caches' in window) {
            const cacheNames = await caches.keys();
            await Promise.all(
                cacheNames.map(cacheName => caches.delete(cacheName))
            );
            console.log('Cache vidé');
        }
    }
}

// Initialiser le gestionnaire PWA
let pwaManager;

document.addEventListener('DOMContentLoaded', () => {
    pwaManager = new PWAManager();
});

// Exposer le gestionnaire globalement
window.pwaManager = pwaManager; 