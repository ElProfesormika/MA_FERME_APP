@extends('layouts.app')

@section('title', 'Tableau de bord')

@section('content')
<div class="row">
    <!-- Statistiques principales -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Animaux
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['nb_animaux'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-cow fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Employés actifs
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['nb_employes'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Alertes critiques
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['nb_alertes'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Activités aujourd'hui
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['nb_activites_aujourdhui'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-tasks fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Graphique des activités par mois -->
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Activités par mois</h6>
                <div class="dropdown no-arrow">
                    <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown">
                        <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                        <a class="dropdown-item" href="{{ route('dashboard.stats', ['periode' => 'hebdo']) }}">Vue hebdomadaire</a>
                        <a class="dropdown-item" href="{{ route('dashboard.stats', ['periode' => 'mensuelle']) }}">Vue mensuelle</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-area">
                    <canvas id="chartActivites"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphique des animaux par espèce -->
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Animaux par espèce</h6>
            </div>
            <div class="card-body">
                <div class="chart-pie pt-4 pb-2">
                    <canvas id="chartAnimaux"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Alertes récentes -->
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Alertes récentes</h6>
            </div>
            <div class="card-body">
                @php
                    $alertesRecentes = \App\Models\Alerte::with(['animal', 'stock'])
                        ->latest()
                        ->take(5)
                        ->get();
                @endphp
                
                @if($alertesRecentes->count() > 0)
                    @foreach($alertesRecentes as $alerte)
                        <div class="alert alert-{{ $alerte->critique ? 'danger' : 'warning' }} alert-sm">
                            <strong>{{ $alerte->type }}:</strong> {{ $alerte->message }}
                            <small class="text-muted d-block">{{ $alerte->created_at->diffForHumans() }}</small>
                        </div>
                    @endforeach
                @else
                    <p class="text-muted">Aucune alerte récente</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Activités récentes -->
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Activités récentes</h6>
            </div>
            <div class="card-body">
                @php
                    $activitesRecentes = \App\Models\Activite::with(['employe', 'animal'])
                        ->latest()
                        ->take(5)
                        ->get();
                @endphp
                
                @if($activitesRecentes->count() > 0)
                    @foreach($activitesRecentes as $activite)
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <i class="fas fa-circle text-{{ $activite->statut == 'terminé' ? 'success' : ($activite->statut == 'en_cours' ? 'warning' : 'info') }}"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="small text-gray-500">{{ $activite->date->format('d/m/Y') }}</div>
                                <span class="font-weight-bold">{{ $activite->titre }}</span>
                                <div class="small text-muted">{{ $activite->employe->nom_complet }}</div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <p class="text-muted">Aucune activité récente</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Actions rapides -->
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Actions rapides</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('animaux.create') }}" class="btn btn-primary btn-block">
                            <i class="fas fa-plus me-2"></i>Ajouter un animal
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('activites.create') }}" class="btn btn-success btn-block">
                            <i class="fas fa-tasks me-2"></i>Nouvelle activité
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('animaux.export.csv') }}" class="btn btn-info btn-block">
                            <i class="fas fa-download me-2"></i>Exporter animaux
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('dashboard.pdf') }}" class="btn btn-secondary btn-block">
                            <i class="fas fa-file-pdf me-2"></i>Rapport PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Graphique des activités par mois
const ctxActivites = document.getElementById('chartActivites').getContext('2d');
new Chart(ctxActivites, {
    type: 'line',
    data: {
        labels: @json($activitesParMois->pluck('mois_nom')),
        datasets: [{
            label: 'Nombre d\'activités',
            data: @json($activitesParMois->pluck('total')),
            borderColor: 'rgba(78, 115, 223, 1)',
            backgroundColor: 'rgba(78, 115, 223, 0.05)',
            borderWidth: 2,
            fill: true,
            tension: 0.3
        }]
    },
    options: {
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        },
        plugins: {
            legend: {
                display: false
            }
        }
    }
});

// Graphique des animaux par espèce
const ctxAnimaux = document.getElementById('chartAnimaux').getContext('2d');
new Chart(ctxAnimaux, {
    type: 'doughnut',
    data: {
        labels: @json($animauxParEspece->pluck('espece')),
        datasets: [{
            data: @json($animauxParEspece->pluck('total')),
            backgroundColor: [
                '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b',
                '#858796', '#5a5c69', '#2e59d9', '#17a673', '#2c9faf'
            ],
            hoverBackgroundColor: [
                '#2e59d9', '#17a673', '#2c9faf', '#f4b619', '#e02424'
            ],
            hoverBorderColor: "rgba(234, 236, 244, 1)",
        }]
    },
    options: {
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>
@endpush 