// Service Worker pour Ferme d'Élevage - PWA
const CACHE_NAME = 'ferme-elevage-v1.3';
const STATIC_CACHE = 'ferme-elevage-static-v1.3';
const DYNAMIC_CACHE = 'ferme-elevage-dynamic-v1.3';

// Fichiers à mettre en cache statique (toujours disponibles hors ligne)
// Ne PAS mettre en cache les pages PHP (dynamiques) pour éviter les problèmes de devise/cookies
const STATIC_FILES = [
    '/offline.html',
    '/manifest.json',
    '/pwa-manager.js',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css',
    'https://cdn.jsdelivr.net/npm/chart.js'
];

// Fichiers à mettre en cache dynamique (données)
const DYNAMIC_FILES = [
    '/api/',
    '/data/'
];

// Installation du Service Worker
self.addEventListener('install', (event) => {
    console.log('Service Worker: Installation...');
    
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then((cache) => {
                console.log('Service Worker: Mise en cache des fichiers statiques');
                return cache.addAll(STATIC_FILES);
            })
            .then(() => {
                console.log('Service Worker: Installation terminée');
                return self.skipWaiting();
            })
            .catch((error) => {
                console.error('Service Worker: Erreur lors de l\'installation', error);
            })
    );
});

// Activation du Service Worker
self.addEventListener('activate', (event) => {
    console.log('Service Worker: Activation...');
    
    event.waitUntil(
        caches.keys()
            .then((cacheNames) => {
                return Promise.all(
                    cacheNames.map((cacheName) => {
                        if (cacheName !== STATIC_CACHE && cacheName !== DYNAMIC_CACHE) {
                            console.log('Service Worker: Suppression de l\'ancien cache', cacheName);
                            return caches.delete(cacheName);
                        }
                    })
                );
            })
            .then(() => {
                console.log('Service Worker: Activation terminée');
                return self.clients.claim();
            })
    );
});

// Interception des requêtes réseau
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Toujours network-first pour les pages HTML/PHP afin de respecter les cookies (devise, session)
    const isHtmlRequest = request.destination === 'document' || /\.php($|\?|#)/i.test(url.pathname);
    if (isHtmlRequest) {
        event.respondWith(
            fetch(request).catch(() => caches.match('/offline.html'))
        );
        return;
    }

    // Stratégie de cache pour les fichiers statiques (CSS/JS/manifest/...)
    if (STATIC_FILES.includes(url.pathname) || STATIC_FILES.includes(request.url)) {
        event.respondWith(
            caches.match(request)
                .then((response) => {
                    if (response) {
                        // Retour du cache
                        return response;
                    }
                    
                    return fetch(request)
                        .then((response) => {
                            if (response.status === 200) {
                                const responseClone = response.clone();
                                caches.open(STATIC_CACHE)
                                    .then((cache) => {
                                        cache.put(request, responseClone);
                                    });
                            }
                            return response;
                        })
                        .catch(() => undefined);
                })
        );
    }

    // Stratégie de cache pour les données dynamiques
    else if (url.pathname.startsWith('/api/') || url.pathname.startsWith('/data/')) {
        event.respondWith(
            fetch(request)
                .then((response) => {
                    if (response.status === 200) {
                        const responseClone = response.clone();
                        caches.open(DYNAMIC_CACHE)
                            .then((cache) => {
                                cache.put(request, responseClone);
                            });
                    }
                    return response;
                })
                .catch(() => {
                    return caches.match(request)
                        .then((response) => {
                            if (response) {
                                console.log('Service Worker: Données trouvées en cache', url.pathname);
                                return response;
                            }
                            return new Response(JSON.stringify({
                                error: 'Hors ligne',
                                message: 'Données non disponibles hors ligne'
                            }), {
                                headers: { 'Content-Type': 'application/json' }
                            });
                        });
                })
        );
    }
    
    // Stratégie par défaut : Network First, puis Cache
    else {
        event.respondWith(
            fetch(request)
                .then((response) => {
                    if (response.status === 200) {
                        const responseClone = response.clone();
                        caches.open(DYNAMIC_CACHE)
                            .then((cache) => {
                                cache.put(request, responseClone);
                            });
                    }
                    return response;
                })
                .catch(() => {
                    return caches.match(request);
                })
        );
    }
});

// Gestion des messages depuis l'application
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
    
    if (event.data && event.data.type === 'GET_VERSION') {
        event.ports[0].postMessage({ version: CACHE_NAME });
    }
    
    if (event.data && event.data.type === 'CLEAR_CACHE') {
        event.waitUntil(
            caches.keys()
                .then((cacheNames) => {
                    return Promise.all(
                        cacheNames.map((cacheName) => {
                            return caches.delete(cacheName);
                        })
                    );
                })
                .then(() => {
                    event.ports[0].postMessage({ success: true });
                })
        );
    }
});

// Synchronisation en arrière-plan
self.addEventListener('sync', (event) => {
    if (event.tag === 'background-sync') {
        console.log('Service Worker: Synchronisation en arrière-plan');
        event.waitUntil(doBackgroundSync());
    }
});

// Fonction de synchronisation en arrière-plan
async function doBackgroundSync() {
    try {
        // Synchroniser les données locales avec le serveur
        const pendingData = await getPendingData();
        
        for (const data of pendingData) {
            await syncDataToServer(data);
        }
        
        console.log('Service Worker: Synchronisation terminée');
    } catch (error) {
        console.error('Service Worker: Erreur de synchronisation', error);
    }
}

// Récupérer les données en attente de synchronisation
async function getPendingData() {
    // Implémenter la récupération des données locales
    return [];
}

// Synchroniser les données avec le serveur
async function syncDataToServer(data) {
    // Implémenter la synchronisation
    return fetch('/api/sync', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    });
}

// Gestion des notifications push
self.addEventListener('push', (event) => {
    console.log('Service Worker: Notification push reçue');
    
    const options = {
        body: event.data ? event.data.text() : 'Nouvelle notification de la ferme',
        icon: '/icons/icon.php?size=192',
        badge: '/icons/icon.php?size=72',
        vibrate: [100, 50, 100],
        data: {
            dateOfArrival: Date.now(),
            primaryKey: 1
        },
        actions: [
            {
                action: 'explore',
                title: 'Voir les détails',
                icon: '/icons/icon.php?size=96'
            },
            {
                action: 'close',
                title: 'Fermer',
                icon: '/icons/icon.php?size=96'
            }
        ]
    };
    
    event.waitUntil(
        self.registration.showNotification('Ferme d\'Élevage', options)
    );
});

// Gestion des clics sur les notifications
self.addEventListener('notificationclick', (event) => {
    console.log('Service Worker: Clic sur notification');
    
    event.notification.close();
    
    if (event.action === 'explore') {
        event.waitUntil(
            clients.openWindow('/dashboard_fixed.php')
        );
    }
}); 