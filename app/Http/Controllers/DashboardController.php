<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use App\Models\Stock;
use App\Models\Employe;
use App\Models\Activite;
use App\Models\Alerte;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'nb_animaux' => Animal::count(),
            'nb_alertes' => Alerte::critiques()->nonResolues()->count(),
            'nb_employes' => Employe::where('statut', 'actif')->count(),
            'nb_stocks_rupture' => Stock::enRupture()->count(),
            'nb_activites_aujourdhui' => Activite::aujourdhui()->count(),
            'nb_activites_semaine' => Activite::cetteSemaine()->count()
        ];

        // Données pour les graphiques
        $activitesParMois = $this->getActivitesParMois();
        $animauxParEspece = $this->getAnimauxParEspece();
        $alertesParType = $this->getAlertesParType();

        return view('dashboard.index', compact('stats', 'activitesParMois', 'animauxParEspece', 'alertesParType'));
    }

    public function statsParPeriode(Request $request)
    {
        $periode = $request->get('periode', 'mensuelle');
        
        if ($periode == 'hebdo') {
            $donnees = DB::table('activites')
                ->select(DB::raw("WEEK(date) as semaine"), DB::raw('COUNT(*) as total'))
                ->groupBy('semaine')
                ->orderBy('semaine')
                ->get();
        } else {
            $donnees = DB::table('activites')
                ->select(DB::raw("MONTH(date) as mois"), DB::raw('COUNT(*) as total'))
                ->groupBy('mois')
                ->orderBy('mois')
                ->get();
        }

        return view('dashboard.stats', compact('donnees', 'periode'));
    }

    private function getActivitesParMois()
    {
        return DB::table('activites')
            ->select(DB::raw("MONTH(date) as mois"), DB::raw('COUNT(*) as total'))
            ->whereYear('date', date('Y'))
            ->groupBy('mois')
            ->orderBy('mois')
            ->get()
            ->map(function ($item) {
                $item->mois_nom = Carbon::create()->month($item->mois)->format('M');
                return $item;
            });
    }

    private function getAnimauxParEspece()
    {
        return DB::table('animals')
            ->select('espece', DB::raw('COUNT(*) as total'))
            ->groupBy('espece')
            ->orderBy('total', 'desc')
            ->get();
    }

    private function getAlertesParType()
    {
        return DB::table('alertes')
            ->select('type', DB::raw('COUNT(*) as total'))
            ->groupBy('type')
            ->orderBy('total', 'desc')
            ->get();
    }

    public function rapportPDF()
    {
        $stats = [
            'nb_animaux' => Animal::count(),
            'nb_employes' => Employe::where('statut', 'actif')->count(),
            'nb_alertes' => Alerte::critiques()->nonResolues()->count(),
            'nb_stocks' => Stock::count()
        ];

        $animaux = Animal::with('employe')->get();
        $activites = Activite::with(['employe', 'animal'])->latest()->take(10)->get();

        $pdf = \PDF::loadView('pdf.rapport', compact('stats', 'animaux', 'activites'));
        return $pdf->download('rapport_ferme_' . date('Y-m-d') . '.pdf');
    }
} 