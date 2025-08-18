<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\Log;
use App\Models\Alerte;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockController extends Controller
{
    public function index()
    {
        $stocks = Stock::latest()->paginate(15);
        return view('stocks.index', compact('stocks'));
    }

    public function create()
    {
        return view('stocks.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'produit' => 'required|string|max:255',
            'quantite' => 'required|integer|min:0',
            'unite' => 'required|string|max:50',
            'date_entree' => 'required|date',
            'date_peremption' => 'nullable|date|after:date_entree',
            'prix_unitaire' => 'nullable|numeric|min:0',
            'fournisseur' => 'nullable|string|max:255',
            'categorie' => 'nullable|string|max:100'
        ]);

        $stock = Stock::create($validated);

        // Vérifier les alertes
        $this->verifierAlertes($stock);

        // Journalisation
        Log::create([
            'user_id' => Auth::id(),
            'action' => 'Création',
            'model_type' => 'Stock',
            'model_id' => $stock->id,
            'details' => $stock->toArray(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return redirect()->route('stocks.index')
            ->with('success', 'Produit ajouté au stock avec succès.');
    }

    public function show(Stock $stock)
    {
        return view('stocks.show', compact('stock'));
    }

    public function edit(Stock $stock)
    {
        return view('stocks.edit', compact('stock'));
    }

    public function update(Request $request, Stock $stock)
    {
        $validated = $request->validate([
            'produit' => 'required|string|max:255',
            'quantite' => 'required|integer|min:0',
            'unite' => 'required|string|max:50',
            'date_entree' => 'required|date',
            'date_peremption' => 'nullable|date|after:date_entree',
            'prix_unitaire' => 'nullable|numeric|min:0',
            'fournisseur' => 'nullable|string|max:255',
            'categorie' => 'nullable|string|max:100'
        ]);

        $oldData = $stock->toArray();
        $stock->update($validated);

        // Vérifier les alertes
        $this->verifierAlertes($stock);

        // Journalisation
        Log::create([
            'user_id' => Auth::id(),
            'action' => 'Modification',
            'model_type' => 'Stock',
            'model_id' => $stock->id,
            'details' => [
                'ancien' => $oldData,
                'nouveau' => $stock->toArray()
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return redirect()->route('stocks.index')
            ->with('success', 'Stock modifié avec succès.');
    }

    public function destroy(Stock $stock)
    {
        $stockData = $stock->toArray();
        $stock->delete();

        // Journalisation
        Log::create([
            'user_id' => Auth::id(),
            'action' => 'Suppression',
            'model_type' => 'Stock',
            'model_id' => $stock->id,
            'details' => $stockData,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);

        return redirect()->route('stocks.index')
            ->with('success', 'Produit supprimé du stock avec succès.');
    }

    private function verifierAlertes(Stock $stock)
    {
        // Alerte de rupture de stock
        if ($stock->quantite <= 10) {
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

        // Alerte de péremption
        if ($stock->date_peremption && $stock->date_peremption->diffInDays(now()) <= 30) {
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
    }

    public function alertes()
    {
        $ruptures = Stock::enRupture()->get();
        $perimes = Stock::perime()->get();
        
        return view('stocks.alertes', compact('ruptures', 'perimes'));
    }

    public function exportCSV()
    {
        $stocks = Stock::all();
        
        $filename = 'stocks_' . date('Y-m-d_H-i-s') . '.csv';
        $handle = fopen('php://temp', 'r+');
        
        // En-têtes
        fputcsv($handle, ['ID', 'Produit', 'Quantité', 'Unité', 'Date entrée', 'Date péremption', 'Prix unitaire', 'Fournisseur', 'Catégorie']);
        
        // Données
        foreach ($stocks as $stock) {
            fputcsv($handle, [
                $stock->id,
                $stock->produit,
                $stock->quantite,
                $stock->unite,
                $stock->date_entree->format('d/m/Y'),
                $stock->date_peremption ? $stock->date_peremption->format('d/m/Y') : '',
                $stock->prix_unitaire,
                $stock->fournisseur,
                $stock->categorie
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