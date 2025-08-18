@extends('layouts.app')

@section('title', 'Ajouter un animal')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800">Ajouter un nouvel animal</h1>
    <a href="{{ route('animaux.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i>Retour à la liste
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Informations de l'animal</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('animaux.store') }}" method="POST">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nom" class="form-label">Nom de l'animal *</label>
                            <input type="text" class="form-control @error('nom') is-invalid @enderror" 
                                   id="nom" name="nom" value="{{ old('nom') }}" required>
                            @error('nom')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="espece" class="form-label">Espèce *</label>
                            <select class="form-select @error('espece') is-invalid @enderror" 
                                    id="espece" name="espece" required>
                                <option value="">Sélectionner une espèce</option>
                                <option value="Bovin" {{ old('espece') == 'Bovin' ? 'selected' : '' }}>Bovin</option>
                                <option value="Ovin" {{ old('espece') == 'Ovin' ? 'selected' : '' }}>Ovin</option>
                                <option value="Porcin" {{ old('espece') == 'Porcin' ? 'selected' : '' }}>Porcin</option>
                                <option value="Volaille" {{ old('espece') == 'Volaille' ? 'selected' : '' }}>Volaille</option>
                                <option value="Caprin" {{ old('espece') == 'Caprin' ? 'selected' : '' }}>Caprin</option>
                                <option value="Équin" {{ old('espece') == 'Équin' ? 'selected' : '' }}>Équin</option>
                                <option value="Autre" {{ old('espece') == 'Autre' ? 'selected' : '' }}>Autre</option>
                            </select>
                            @error('espece')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="race" class="form-label">Race *</label>
                            <input type="text" class="form-control @error('race') is-invalid @enderror" 
                                   id="race" name="race" value="{{ old('race') }}" required>
                            @error('race')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="sexe" class="form-label">Sexe</label>
                            <select class="form-select @error('sexe') is-invalid @enderror" 
                                    id="sexe" name="sexe">
                                <option value="">Non spécifié</option>
                                <option value="M" {{ old('sexe') == 'M' ? 'selected' : '' }}>Mâle</option>
                                <option value="F" {{ old('sexe') == 'F' ? 'selected' : '' }}>Femelle</option>
                            </select>
                            @error('sexe')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="date_naissance" class="form-label">Date de naissance</label>
                            <input type="date" class="form-control @error('date_naissance') is-invalid @enderror" 
                                   id="date_naissance" name="date_naissance" value="{{ old('date_naissance') }}">
                            @error('date_naissance')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="poids" class="form-label">Poids (kg)</label>
                            <input type="number" step="0.01" class="form-control @error('poids') is-invalid @enderror" 
                                   id="poids" name="poids" value="{{ old('poids') }}" min="0">
                            @error('poids')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="statut" class="form-label">Statut *</label>
                        <select class="form-select @error('statut') is-invalid @enderror" 
                                id="statut" name="statut" required>
                            <option value="actif" {{ old('statut') == 'actif' ? 'selected' : '' }}>Actif</option>
                            <option value="inactif" {{ old('statut') == 'inactif' ? 'selected' : '' }}>Inactif</option>
                            <option value="malade" {{ old('statut') == 'malade' ? 'selected' : '' }}>Malade</option>
                            <option value="mort" {{ old('statut') == 'mort' ? 'selected' : '' }}>Mort</option>
                        </select>
                        @error('statut')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="employe_id" class="form-label">Responsable</label>
                        <select class="form-select @error('employe_id') is-invalid @enderror" 
                                id="employe_id" name="employe_id">
                            <option value="">Aucun responsable assigné</option>
                            @foreach($employes as $employe)
                                <option value="{{ $employe->id }}" {{ old('employe_id') == $employe->id ? 'selected' : '' }}>
                                    {{ $employe->nom_complet }} - {{ $employe->poste }}
                                </option>
                            @endforeach
                        </select>
                        @error('employe_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="historique_sante" class="form-label">Historique de santé</label>
                        <textarea class="form-control @error('historique_sante') is-invalid @enderror" 
                                  id="historique_sante" name="historique_sante" rows="4" 
                                  placeholder="Informations sur l'état de santé, traitements, etc.">{{ old('historique_sante') }}</textarea>
                        @error('historique_sante')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end">
                        <a href="{{ route('animaux.index') }}" class="btn btn-secondary me-2">Annuler</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Enregistrer l'animal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Aide et conseils -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-info-circle me-1"></i>Conseils
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6 class="text-primary">Nom de l'animal</h6>
                    <p class="small text-muted">Choisissez un nom unique et facilement identifiable pour l'animal.</p>
                </div>
                
                <div class="mb-3">
                    <h6 class="text-primary">Espèce et race</h6>
                    <p class="small text-muted">Précisez l'espèce et la race pour un meilleur suivi et des soins adaptés.</p>
                </div>
                
                <div class="mb-3">
                    <h6 class="text-primary">Date de naissance</h6>
                    <p class="small text-muted">Si connue, permet de calculer l'âge et de planifier les soins appropriés.</p>
                </div>
                
                <div class="mb-3">
                    <h6 class="text-primary">Responsable</h6>
                    <p class="small text-muted">Assignez un employé responsable pour assurer le suivi quotidien.</p>
                </div>
                
                <div class="mb-3">
                    <h6 class="text-primary">Historique de santé</h6>
                    <p class="small text-muted">Notez les informations importantes sur la santé, les traitements, etc.</p>
                </div>
            </div>
        </div>

        <!-- Statistiques rapides -->
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-chart-bar me-1"></i>Statistiques
                </h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-end">
                            <h4 class="text-primary">{{ \App\Models\Animal::count() }}</h4>
                            <small class="text-muted">Total animaux</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <h4 class="text-success">{{ \App\Models\Animal::where('statut', 'actif')->count() }}</h4>
                        <small class="text-muted">Animaux actifs</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validation en temps réel
    const form = document.querySelector('form');
    const inputs = form.querySelectorAll('input, select, textarea');
    
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            validateField(this);
        });
    });
    
    function validateField(field) {
        const value = field.value.trim();
        const fieldName = field.name;
        
        // Supprimer les classes d'erreur existantes
        field.classList.remove('is-invalid', 'is-valid');
        
        // Validation spécifique par champ
        switch(fieldName) {
            case 'nom':
                if (value.length < 2) {
                    showError(field, 'Le nom doit contenir au moins 2 caractères');
                } else {
                    showSuccess(field);
                }
                break;
                
            case 'espece':
                if (!value) {
                    showError(field, 'Veuillez sélectionner une espèce');
                } else {
                    showSuccess(field);
                }
                break;
                
            case 'race':
                if (!value) {
                    showError(field, 'Veuillez saisir la race');
                } else {
                    showSuccess(field);
                }
                break;
                
            case 'poids':
                if (value && parseFloat(value) < 0) {
                    showError(field, 'Le poids ne peut pas être négatif');
                } else if (value) {
                    showSuccess(field);
                }
                break;
        }
    }
    
    function showError(field, message) {
        field.classList.add('is-invalid');
        let feedback = field.parentNode.querySelector('.invalid-feedback');
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            field.parentNode.appendChild(feedback);
        }
        feedback.textContent = message;
    }
    
    function showSuccess(field) {
        field.classList.add('is-valid');
    }
});
</script>
@endpush 