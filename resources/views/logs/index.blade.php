@extends('layouts.app')

@section('title', 'Journal d\'activité')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800">Journal d'activité</h1>
    <div>
        <a href="{{ route('logs.export') }}" class="btn btn-info me-2">
            <i class="fas fa-download me-1"></i>Exporter CSV
        </a>
        <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#clearLogsModal">
            <i class="fas fa-trash me-1"></i>Nettoyer
        </button>
    </div>
</div>

<!-- Statistiques -->
<div class="row mb-4">
    <div class="col-xl-2 col-md-4 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total logs</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_logs'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-history fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Aujourd'hui</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['logs_aujourdhui'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Cette semaine</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['logs_semaine'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar-week fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Créations</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['actions_creations'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-plus fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 mb-4">
        <div class="card border-left-secondary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Modifications</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['actions_modifications'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-edit fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 mb-4">
        <div class="card border-left-danger shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Suppressions</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['actions_suppressions'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-trash fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filtres -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Filtres</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('logs.index') }}" class="row g-3">
            <div class="col-md-2">
                <label for="action" class="form-label">Action</label>
                <select class="form-select" id="action" name="action">
                    <option value="">Toutes</option>
                    @foreach($actions as $action)
                        <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                            {{ $action }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label for="model_type" class="form-label">Modèle</label>
                <select class="form-select" id="model_type" name="model_type">
                    <option value="">Tous</option>
                    @foreach($modelTypes as $type)
                        <option value="{{ $type }}" {{ request('model_type') == $type ? 'selected' : '' }}>
                            {{ $type }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label for="date_from" class="form-label">Date début</label>
                <input type="date" class="form-control" id="date_from" name="date_from" 
                       value="{{ request('date_from') }}">
            </div>

            <div class="col-md-2">
                <label for="date_to" class="form-label">Date fin</label>
                <input type="date" class="form-control" id="date_to" name="date_to" 
                       value="{{ request('date_to') }}">
            </div>

            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-search me-1"></i>Filtrer
                </button>
                <a href="{{ route('logs.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times me-1"></i>Réinitialiser
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Liste des logs -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Journal d'activité ({{ $logs->total() }} entrées)</h6>
    </div>
    <div class="card-body">
        @if($logs->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Date/Heure</th>
                            <th>Utilisateur</th>
                            <th>Action</th>
                            <th>Modèle</th>
                            <th>ID</th>
                            <th>IP</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                            <tr>
                                <td>
                                    <div class="small">{{ $log->created_at->format('d/m/Y') }}</div>
                                    <div class="small text-muted">{{ $log->created_at->format('H:i:s') }}</div>
                                </td>
                                <td>
                                    @if($log->user)
                                        <span class="font-weight-bold">{{ $log->user->name }}</span>
                                        <br><small class="text-muted">{{ $log->user->email }}</small>
                                    @else
                                        <span class="text-muted">Système</span>
                                    @endif
                                </td>
                                <td>
                                    @switch($log->action)
                                        @case('Création')
                                            <span class="badge bg-success">{{ $log->action }}</span>
                                            @break
                                        @case('Modification')
                                            <span class="badge bg-warning">{{ $log->action }}</span>
                                            @break
                                        @case('Suppression')
                                            <span class="badge bg-danger">{{ $log->action }}</span>
                                            @break
                                        @default
                                            <span class="badge bg-secondary">{{ $log->action }}</span>
                                    @endswitch
                                </td>
                                <td>
                                    @if($log->model_type)
                                        <span class="badge bg-info">{{ $log->model_type }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($log->model_id)
                                        <span class="badge bg-light text-dark">{{ $log->model_id }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">{{ $log->ip_address ?? '-' }}</small>
                                </td>
                                <td>
                                    <a href="{{ route('logs.show', $log) }}" 
                                       class="btn btn-sm btn-outline-info" title="Voir les détails">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center">
                {{ $logs->appends(request()->query())->links() }}
            </div>
        @else
            <div class="text-center py-4">
                <i class="fas fa-history fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Aucun log trouvé</h5>
                <p class="text-muted">Aucune activité n'a été enregistrée pour les critères sélectionnés.</p>
            </div>
        @endif
    </div>
</div>

<!-- Modal de nettoyage des logs -->
<div class="modal fade" id="clearLogsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nettoyer les logs</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('logs.clear') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p>Cette action supprimera définitivement tous les logs de plus de :</p>
                    <div class="mb-3">
                        <label for="days" class="form-label">Nombre de jours</label>
                        <select class="form-select" id="days" name="days" required>
                            <option value="7">7 jours</option>
                            <option value="30" selected>30 jours</option>
                            <option value="90">90 jours</option>
                            <option value="365">1 an</option>
                        </select>
                    </div>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Attention :</strong> Cette action est irréversible !
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i>Nettoyer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit du formulaire de filtres quand les dates changent
    const dateInputs = document.querySelectorAll('#date_from, #date_to');
    dateInputs.forEach(input => {
        input.addEventListener('change', function() {
            if (this.value) {
                document.querySelector('form').submit();
            }
        });
    });
});
</script>
@endpush 