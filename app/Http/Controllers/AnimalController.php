<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use App\Models\Log;
use App\Models\Employe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnimalController extends Controller
{
    public function index()
    {
        $animaux = Animal::with('employe')->latest()->paginate(15);
        return view('animaux.index', compact('animaux'));
    }

    public function create()
    {
        $employes = Employe::where('statut', 'actif')->get();
        return view('animaux.create', compact('employes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'espece' => 'required|string|max:255',
            'race' => 'required|string|max:255',
            'date_naissance' => 'nullable|date',
            'historique_sante' => 'nullable|string',
            'poids' => 'nullable|numeric|min:0',
            'sexe' => 'nullable|in:M,F',
            'statut' => 'required|in:actif,inactif,malade,mort',
            'employe_id' => 'nullable|exists:employes,id'
        ]);

        $animal = Animal::create($validated);

        // Journalisation
        Log::create([
            'user_id' => Auth::id(),
            'action' => 'Création',
            'model_type' => 'Animal',
            'model_id' => $animal->id,
            'details' => $animal->toArray(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return redirect()->route('animaux.index')
            ->with('success', 'Animal ajouté avec succès.');
    }

    public function show(Animal $animal)
    {
        $animal->load(['employe', 'activites', 'alertes']);
        return view('animaux.show', compact('animal'));
    }

    public function edit(Animal $animal)
    {
        $employes = Employe::where('statut', 'actif')->get();
        return view('animaux.edit', compact('animal', 'employes'));
    }

    public function update(Request $request, Animal $animal)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'espece' => 'required|string|max:255',
            'race' => 'required|string|max:255',
            'date_naissance' => 'nullable|date',
            'historique_sante' => 'nullable|string',
            'poids' => 'nullable|numeric|min:0',
            'sexe' => 'nullable|in:M,F',
            'statut' => 'required|in:actif,inactif,malade,mort',
            'employe_id' => 'nullable|exists:employes,id'
        ]);

        $oldData = $animal->toArray();
        $animal->update($validated);

        // Journalisation
        Log::create([
            'user_id' => Auth::id(),
            'action' => 'Modification',
            'model_type' => 'Animal',
            'model_id' => $animal->id,
            'details' => [
                'ancien' => $oldData,
                'nouveau' => $animal->toArray()
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return redirect()->route('animaux.index')
            ->with('success', 'Animal modifié avec succès.');
    }

    public function destroy(Animal $animal)
    {
        $animalData = $animal->toArray();
        $animal->delete();

        // Journalisation
        Log::create([
            'user_id' => Auth::id(),
            'action' => 'Suppression',
            'model_type' => 'Animal',
            'model_id' => $animal->id,
            'details' => $animalData,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);

        return redirect()->route('animaux.index')
            ->with('success', 'Animal supprimé avec succès.');
    }

    public function exportCSV()
    {
        $animaux = Animal::with('employe')->get();
        
        $filename = 'animaux_' . date('Y-m-d_H-i-s') . '.csv';
        $handle = fopen('php://temp', 'r+');
        
        // En-têtes
        fputcsv($handle, ['ID', 'Nom', 'Espèce', 'Race', 'Date de naissance', 'Poids', 'Sexe', 'Statut', 'Employé']);
        
        // Données
        foreach ($animaux as $animal) {
            fputcsv($handle, [
                $animal->id,
                $animal->nom,
                $animal->espece,
                $animal->race,
                $animal->date_naissance,
                $animal->poids,
                $animal->sexe,
                $animal->statut,
                $animal->employe ? $animal->employe->nom_complet : ''
            ]);
        }
        
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);
        
        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
} 