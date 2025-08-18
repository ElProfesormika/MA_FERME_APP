@extends('layouts.app')

@section('title', 'Mon Profil')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-user me-2"></i>
                        Mon Profil
                    </h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="text-primary mb-3">Informations personnelles</h5>
                            
                            <div class="mb-3">
                                <label class="fw-bold">Nom complet :</label>
                                <p class="text-muted">{{ $user->name }}</p>
                            </div>

                            <div class="mb-3">
                                <label class="fw-bold">Email :</label>
                                <p class="text-muted">{{ $user->email }}</p>
                            </div>

                            <div class="mb-3">
                                <label class="fw-bold">Rôle :</label>
                                <span class="badge bg-{{ $user->role === 'admin' ? 'danger' : ($user->role === 'manager' ? 'warning' : 'info') }}">
                                    {{ $user->role_name }}
                                </span>
                            </div>

                            <div class="mb-3">
                                <label class="fw-bold">Membre depuis :</label>
                                <p class="text-muted">{{ $user->created_at->format('d/m/Y H:i') }}</p>
                            </div>

                            @if($user->last_login)
                                <div class="mb-3">
                                    <label class="fw-bold">Dernière connexion :</label>
                                    <p class="text-muted">{{ $user->last_login->format('d/m/Y H:i') }}</p>
                                </div>
                            @endif
                        </div>

                        <div class="col-md-6">
                            <h5 class="text-primary mb-3">Permissions</h5>
                            
                            @if($user->permissions && count($user->permissions) > 0)
                                <div class="row">
                                    @foreach($user->getPermissionsWithNames() as $key => $permission)
                                        <div class="col-12 mb-2">
                                            <span class="badge bg-success me-1">
                                                <i class="fas fa-check me-1"></i>
                                                {{ $permission }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted">Aucune permission spécifique</p>
                            @endif
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('profile.edit') }}" class="btn btn-primary">
                            <i class="fas fa-edit me-1"></i>
                            Modifier le profil
                        </a>
                        
                        <a href="{{ route('profile.change-password') }}" class="btn btn-warning">
                            <i class="fas fa-key me-1"></i>
                            Changer le mot de passe
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
