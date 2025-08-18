@extends('layouts.app')

@section('title', 'Gestion des animaux')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800">Gestion des animaux</h1>
    <div>
        <a href="{{ route('animaux.export.csv') }}" class="btn btn-info me-2">
            <i class="fas fa-download me-1"></i>Exporter CSV
        </a>
        <a href="{{ route('animaux.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Ajouter un animal
        </a>
    </div>
</div>

<!-- Filtres et recherche -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Filtres</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('animaux.index') }}" class="row g-3">
            <div class="col-md-3">
                <label for="search" class="form-label">Recherche</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="{{ request('search') }}" placeholder="Nom, espèce, race...">
            </div>
            <div class="col-md-2">
                <label for="espece" class="form-label">Espèce</label>
                <select class="form-select" id="espece" name="espece">
                    <option value="">Toutes</option>
                    @foreach(['Bovin', 'Ovin', 'Porcin', 'Volaille', 'Caprin'] as $espece)
                        <option value="{{ $espece }}" {{ request('espece') == $espece ? 'selected' : '' }}>
                            {{ $espece }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="statut" class="form-label">Statut</label>
                <select class="form-select" id="statut" name="statut">
                    <option value="">Tous</option>
                    @foreach(['actif', 'inactif', 'malade', 'mort'] as $statut)
                        <option value="{{ $statut }}" {{ request('statut') == $statut ? 'selected' : '' }}>
                            {{ ucfirst($statut) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="sexe" class="form-label">Sexe</label>
                <select class="form-select" id="sexe" name="sexe">
                    <option value="">Tous</option>
                    <option value="M" {{ request('sexe') == 'M' ? 'selected' : '' }}>Mâle</option>
                    <option value="F" {{ request('sexe') == 'F' ? 'selected' : '' }}>Femelle</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-search me-1"></i>Filtrer
                </button>
                <a href="{{ route('animaux.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times me-1"></i>Réinitialiser
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Liste des animaux -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Liste des animaux ({{ $animaux->total() }})</h6>
    </div>
    <div class="card-body">
        @if($animaux->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Espèce</th>
                            <th>Race</th>
                            <th>Sexe</th>
                            <th>Date de naissance</th>
                            <th>Poids</th>
                            <th>Statut</th>
                            <th>Responsable</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($animaux as $animal)
                            <tr>
                                <td>{{ $animal->id }}</td>
                                <td>
                                    <strong>{{ $animal->nom }}</strong>
                                    @if($animal->date_naissance)
                                        <br><small class="text-muted">
                                            Âge: {{ $animal->date_naissance->age }} ans
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ $animal->espece }}</span>
                                </td>
                                <td>{{ $animal->race }}</td>
                                <td>
                                    @if($animal->sexe == 'M')
                                        <i class="fas fa-mars text-primary"></i> Mâle
                                    @elseif($animal->sexe == 'F')
                                        <i class="fas fa-venus text-danger"></i> Femelle
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $animal->date_naissance ? $animal->date_naissance->format('d/m/Y') : '-' }}
                                </td>
                                <td>
                                    {{ $animal->poids ? $animal->poids . ' kg' : '-' }}
                                </td>
                                <td>
                                    @switch($animal->statut)
                                        @case('actif')
                                            <span class="badge bg-success">Actif</span>
                                            @break
                                        @case('inactif')
                                            <span class="badge bg-secondary">Inactif</span>
                                            @break
                                        @case('malade')
                                            <span class="badge bg-warning">Malade</span>
                                            @break
                                        @case('mort')
                                            <span class="badge bg-danger">Mort</span>
                                            @break
                                    @endswitch
                                </td>
                                <td>
                                    @if($animal->employe)
                                        {{ $animal->employe->nom_complet }}
                                    @else
                                        <span class="text-muted">Non assigné</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('animaux.show', $animal) }}" 
                                           class="btn btn-sm btn-outline-info" title="Voir">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('animaux.edit', $animal) }}" 
                                           class="btn btn-sm btn-outline-warning" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('animaux.destroy', $animal) }}" 
                                              method="POST" class="d-inline"
                                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet animal ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center">
                {{ $animaux->appends(request()->query())->links() }}
            </div>
        @else
            <div class="text-center py-4">
                <i class="fas fa-cow fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Aucun animal trouvé</h5>
                <p class="text-muted">Commencez par ajouter votre premier animal.</p>
                <a href="{{ route('animaux.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Ajouter un animal
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Statistiques rapides -->
<div class="row">
    <div class="col-md-3">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total animaux
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $animaux->total() }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-cow fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Animaux actifs
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ \App\Models\Animal::where('statut', 'actif')->count() }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Animaux malades
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ \App\Models\Animal::where('statut', 'malade')->count() }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Espèces différentes
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ \App\Models\Animal::distinct('espece')->count() }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-list fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialisation de DataTables si nécessaire
    if ($.fn.DataTable) {
        $('#dataTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/French.json"
            }
        });
    }
});
</script>
@endpush 