<?php

namespace App\Http\Controllers;

use App\Models\Log;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $query = Log::with('user')->recentes();

        // Filtres
        if ($request->filled('action')) {
            $query->parAction($request->action);
        }

        if ($request->filled('user_id')) {
            $query->parUtilisateur($request->user_id);
        }

        if ($request->filled('model_type')) {
            $query->where('model_type', $request->model_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(20);

        // Statistiques
        $stats = [
            'total_logs' => Log::count(),
            'logs_aujourdhui' => Log::whereDate('created_at', today())->count(),
            'logs_semaine' => Log::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'actions_creations' => Log::parAction('Création')->count(),
            'actions_modifications' => Log::parAction('Modification')->count(),
            'actions_suppressions' => Log::parAction('Suppression')->count(),
        ];

        // Actions disponibles pour le filtre
        $actions = Log::distinct('action')->pluck('action');
        $modelTypes = Log::distinct('model_type')->whereNotNull('model_type')->pluck('model_type');

        return view('logs.index', compact('logs', 'stats', 'actions', 'modelTypes'));
    }

    public function show(Log $log)
    {
        $log->load('user');
        
        return view('logs.show', compact('log'));
    }

    public function export(Request $request)
    {
        $query = Log::with('user')->recentes();

        // Appliquer les mêmes filtres que dans index
        if ($request->filled('action')) {
            $query->parAction($request->action);
        }

        if ($request->filled('user_id')) {
            $query->parUtilisateur($request->user_id);
        }

        if ($request->filled('model_type')) {
            $query->where('model_type', $request->model_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->get();

        $filename = 'logs_' . date('Y-m-d_H-i-s') . '.csv';
        $handle = fopen('php://temp', 'r+');
        
        // En-têtes
        fputcsv($handle, ['ID', 'Utilisateur', 'Action', 'Modèle', 'ID Modèle', 'IP', 'Date']);
        
        // Données
        foreach ($logs as $log) {
            fputcsv($handle, [
                $log->id,
                $log->user ? $log->user->name : 'Système',
                $log->action,
                $log->model_type,
                $log->model_id,
                $log->ip_address,
                $log->created_at->format('d/m/Y H:i:s')
            ]);
        }
        
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);
        
        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function clear(Request $request)
    {
        // Supprimer les logs de plus de X jours
        $days = $request->get('days', 30);
        $deleted = Log::where('created_at', '<', now()->subDays($days))->delete();
        
        return redirect()->route('logs.index')
            ->with('success', "{$deleted} logs supprimés (plus de {$days} jours).");
    }
} 