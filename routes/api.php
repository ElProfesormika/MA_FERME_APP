<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AnimalController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\EmployeController;
use App\Http\Controllers\ActiviteController;
use App\Http\Controllers\AlerteController;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Routes publiques (sans authentification)
Route::get('/health', function () {
    return response()->json([
        'status' => 'OK',
        'timestamp' => now(),
        'version' => '1.0.0'
    ]);
});

// Routes protégées par authentification
Route::middleware('auth:sanctum')->group(function () {
    
    // Dashboard et statistiques
    Route::get('/dashboard/stats', [DashboardController::class, 'apiStats']);
    Route::get('/dashboard/quick-stats', [DashboardController::class, 'apiQuickStats']);
    
    // Animaux
    Route::get('/animaux', [AnimalController::class, 'apiIndex']);
    Route::get('/animaux/{animal}', [AnimalController::class, 'apiShow']);
    Route::post('/animaux', [AnimalController::class, 'apiStore']);
    Route::put('/animaux/{animal}', [AnimalController::class, 'apiUpdate']);
    Route::delete('/animaux/{animal}', [AnimalController::class, 'apiDestroy']);
    
    // Stocks
    Route::get('/stocks', [StockController::class, 'apiIndex']);
    Route::get('/stocks/{stock}', [StockController::class, 'apiShow']);
    Route::post('/stocks', [StockController::class, 'apiStore']);
    Route::put('/stocks/{stock}', [StockController::class, 'apiUpdate']);
    Route::delete('/stocks/{stock}', [StockController::class, 'apiDestroy']);
    Route::get('/stocks/alertes/rupture', [StockController::class, 'apiRuptures']);
    Route::get('/stocks/alertes/peremption', [StockController::class, 'apiPerimes']);
    
    // Employés
    Route::get('/employes', [EmployeController::class, 'apiIndex']);
    Route::get('/employes/{employe}', [EmployeController::class, 'apiShow']);
    Route::post('/employes', [EmployeController::class, 'apiStore']);
    Route::put('/employes/{employe}', [EmployeController::class, 'apiUpdate']);
    Route::delete('/employes/{employe}', [EmployeController::class, 'apiDestroy']);
    
    // Activités
    Route::get('/activites', [ActiviteController::class, 'apiIndex']);
    Route::get('/activites/{activite}', [ActiviteController::class, 'apiShow']);
    Route::post('/activites', [ActiviteController::class, 'apiStore']);
    Route::put('/activites/{activite}', [ActiviteController::class, 'apiUpdate']);
    Route::delete('/activites/{activite}', [ActiviteController::class, 'apiDestroy']);
    Route::get('/activites/aujourdhui', [ActiviteController::class, 'apiAujourdhui']);
    Route::get('/activites/semaine', [ActiviteController::class, 'apiCetteSemaine']);
    
    // Alertes
    Route::get('/alertes', [AlerteController::class, 'apiIndex']);
    Route::get('/alertes/{alerte}', [AlerteController::class, 'apiShow']);
    Route::post('/alertes', [AlerteController::class, 'apiStore']);
    Route::put('/alertes/{alerte}', [AlerteController::class, 'apiUpdate']);
    Route::delete('/alertes/{alerte}', [AlerteController::class, 'apiDestroy']);
    Route::get('/alertes/critiques', [AlerteController::class, 'apiCritiques']);
    Route::get('/alertes/non-resolues', [AlerteController::class, 'apiNonResolues']);
    
    // Recherche globale
    Route::get('/search', function (Request $request) {
        $query = $request->get('q');
        $type = $request->get('type', 'all');
        
        $results = [];
        
        if ($type === 'all' || $type === 'animaux') {
            $animaux = \App\Models\Animal::where('nom', 'like', "%{$query}%")
                ->orWhere('espece', 'like', "%{$query}%")
                ->orWhere('race', 'like', "%{$query}%")
                ->take(5)
                ->get(['id', 'nom', 'espece', 'race']);
            
            $results['animaux'] = $animaux;
        }
        
        if ($type === 'all' || $type === 'stocks') {
            $stocks = \App\Models\Stock::where('produit', 'like', "%{$query}%")
                ->orWhere('categorie', 'like', "%{$query}%")
                ->take(5)
                ->get(['id', 'produit', 'quantite', 'unite']);
            
            $results['stocks'] = $stocks;
        }
        
        if ($type === 'all' || $type === 'employes') {
            $employes = \App\Models\Employe::where('nom', 'like', "%{$query}%")
                ->orWhere('prenom', 'like', "%{$query}%")
                ->orWhere('poste', 'like', "%{$query}%")
                ->take(5)
                ->get(['id', 'nom', 'prenom', 'poste']);
            
            $results['employes'] = $employes;
        }
        
        return response()->json($results);
    });
    
    // Synchronisation
    Route::post('/sync', function (Request $request) {
        $data = $request->all();
        $results = [];
        
        // Synchroniser les données reçues
        if (isset($data['animaux'])) {
            foreach ($data['animaux'] as $animalData) {
                $animal = \App\Models\Animal::updateOrCreate(
                    ['id' => $animalData['id']],
                    $animalData
                );
                $results['animaux'][] = $animal;
            }
        }
        
        if (isset($data['activites'])) {
            foreach ($data['activites'] as $activiteData) {
                $activite = \App\Models\Activite::updateOrCreate(
                    ['id' => $activiteData['id']],
                    $activiteData
                );
                $results['activites'][] = $activite;
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Synchronisation réussie',
            'data' => $results
        ]);
    });
    
    // Notifications push (pour futures fonctionnalités)
    Route::post('/notifications/register', function (Request $request) {
        $user = $request->user();
        $token = $request->get('push_token');
        
        // Enregistrer le token pour les notifications push
        $user->update(['push_token' => $token]);
        
        return response()->json([
            'success' => true,
            'message' => 'Token enregistré avec succès'
        ]);
    });
});

// Routes pour les alertes critiques (accessibles sans authentification pour les capteurs IoT)
Route::post('/alertes/critiques', [AlerteController::class, 'apiStoreCritique']);

// Documentation de l'API
Route::get('/docs', function () {
    return response()->json([
        'api_name' => 'Ferme d\'Élevage API',
        'version' => '1.0.0',
        'endpoints' => [
            'GET /api/health' => 'Vérification de l\'état de l\'API',
            'GET /api/dashboard/stats' => 'Statistiques du tableau de bord',
            'GET /api/animaux' => 'Liste des animaux',
            'POST /api/animaux' => 'Créer un animal',
            'GET /api/stocks' => 'Liste des stocks',
            'GET /api/alertes/critiques' => 'Alertes critiques',
            'POST /api/alertes/critiques' => 'Créer une alerte critique (IoT)',
            'GET /api/search' => 'Recherche globale',
            'POST /api/sync' => 'Synchronisation des données'
        ],
        'authentication' => 'Laravel Sanctum',
        'rate_limiting' => '100 requests per minute per user'
    ]);
}); 