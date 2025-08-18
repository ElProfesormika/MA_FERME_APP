<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Models\Stock;
use App\Models\Alerte;
use App\Models\Animal;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Vérification quotidienne des alertes de stock
        $schedule->call(function () {
            $this->verifierAlertesStock();
        })->dailyAt('08:00')->name('verifier-alertes-stock');

        // Vérification quotidienne des alertes de péremption
        $schedule->call(function () {
            $this->verifierAlertesPeremption();
        })->dailyAt('09:00')->name('verifier-alertes-peremption');

        // Vérification hebdomadaire des animaux malades
        $schedule->call(function () {
            $this->verifierAnimauxMalades();
        })->weekly()->mondays()->at('10:00')->name('verifier-animaux-malades');

        // Sauvegarde automatique de la base de données
        $schedule->command('backup:run')->dailyAt('02:00')->name('sauvegarde-bdd');

        // Nettoyage des logs anciens (plus de 90 jours)
        $schedule->call(function () {
            $this->nettoyerLogs();
        })->monthly()->name('nettoyer-logs');

        // Génération de rapports mensuels
        $schedule->call(function () {
            $this->genererRapportMensuel();
        })->monthly()->name('rapport-mensuel');

        // Vérification des activités en retard
        $schedule->call(function () {
            $this->verifierActivitesRetard();
        })->hourly()->name('verifier-activites-retard');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

    /**
     * Vérifier les alertes de rupture de stock
     */
    private function verifierAlertesStock()
    {
        $stocksEnRupture = Stock::enRupture()->get();

        foreach ($stocksEnRupture as $stock) {
            Alerte::updateOrCreate(
                [
                    'type' => 'Rupture de stock',
                    'stock_id' => $stock->id,
                    'statut' => 'nouvelle'
                ],
                [
                    'message' => "Rupture de stock pour {$stock->produit} (Quantité: {$stock->quantite} {$stock->unite})",
                    'critique' => $stock->quantite <= 5,
                    'statut' => 'nouvelle'
                ]
            );
        }

        Log::info("Vérification des alertes de stock terminée. {$stocksEnRupture->count()} alertes créées.");
    }

    /**
     * Vérifier les alertes de péremption
     */
    private function verifierAlertesPeremption()
    {
        $stocksPerimes = Stock::perime()->get();

        foreach ($stocksPerimes as $stock) {
            $joursRestants = $stock->date_peremption->diffInDays(now());
            
            Alerte::updateOrCreate(
                [
                    'type' => 'Péremption',
                    'stock_id' => $stock->id,
                    'statut' => 'nouvelle'
                ],
                [
                    'message' => "Produit {$stock->produit} expire dans {$joursRestants} jours",
                    'critique' => $joursRestants <= 7,
                    'statut' => 'nouvelle'
                ]
            );
        }

        Log::info("Vérification des alertes de péremption terminée. {$stocksPerimes->count()} alertes créées.");
    }

    /**
     * Vérifier les animaux malades
     */
    private function verifierAnimauxMalades()
    {
        $animauxMalades = Animal::where('statut', 'malade')->get();

        foreach ($animauxMalades as $animal) {
            Alerte::updateOrCreate(
                [
                    'type' => 'Santé animale',
                    'animal_id' => $animal->id,
                    'statut' => 'nouvelle'
                ],
                [
                    'message' => "Animal {$animal->nom} nécessite des soins vétérinaires",
                    'critique' => true,
                    'statut' => 'nouvelle'
                ]
            );
        }

        Log::info("Vérification des animaux malades terminée. {$animauxMalades->count()} alertes créées.");
    }

    /**
     * Nettoyer les logs anciens
     */
    private function nettoyerLogs()
    {
        $deleted = \App\Models\Log::where('created_at', '<', now()->subDays(90))->delete();
        Log::info("Nettoyage des logs terminé. {$deleted} logs supprimés.");
    }

    /**
     * Générer un rapport mensuel
     */
    private function genererRapportMensuel()
    {
        $mois = now()->format('F Y');
        
        $stats = [
            'nb_animaux' => Animal::count(),
            'nb_animaux_malades' => Animal::where('statut', 'malade')->count(),
            'nb_employes' => \App\Models\Employe::where('statut', 'actif')->count(),
            'nb_activites' => \App\Models\Activite::whereMonth('created_at', now()->month)->count(),
            'nb_alertes' => Alerte::whereMonth('created_at', now()->month)->count(),
            'stocks_rupture' => Stock::enRupture()->count(),
            'stocks_perimes' => Stock::perime()->count()
        ];

        // Créer une alerte avec le rapport
        Alerte::create([
            'type' => 'Rapport mensuel',
            'message' => "Rapport mensuel {$mois} : " . json_encode($stats),
            'critique' => false,
            'statut' => 'nouvelle'
        ]);

        Log::info("Rapport mensuel généré pour {$mois}");
    }

    /**
     * Vérifier les activités en retard
     */
    private function verifierActivitesRetard()
    {
        $activitesRetard = \App\Models\Activite::where('statut', 'planifié')
            ->where('date', '<', now()->toDateString())
            ->get();

        foreach ($activitesRetard as $activite) {
            Alerte::updateOrCreate(
                [
                    'type' => 'Activité en retard',
                    'activite_id' => $activite->id,
                    'statut' => 'nouvelle'
                ],
                [
                    'message' => "Activité '{$activite->titre}' est en retard (prévue le {$activite->date->format('d/m/Y')})",
                    'critique' => false,
                    'statut' => 'nouvelle'
                ]
            );
        }

        if ($activitesRetard->count() > 0) {
            Log::info("Vérification des activités en retard terminée. {$activitesRetard->count()} alertes créées.");
        }
    }
} 