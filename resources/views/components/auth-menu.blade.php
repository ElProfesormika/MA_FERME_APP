@props(['user'])

<div class="dropdown">
    <button class="btn btn-outline-light dropdown-toggle" type="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fas fa-user me-1"></i>
        {{ $user->name }}
        <span class="badge bg-{{ $user->role === 'admin' ? 'danger' : ($user->role === 'manager' ? 'warning' : 'info') }} ms-1">
            {{ $user->role_name }}
        </span>
    </button>
    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu">
        <li>
            <a class="dropdown-item" href="{{ route('profile.show') }}">
                <i class="fas fa-user me-2"></i>
                Mon Profil
            </a>
        </li>
        <li>
            <a class="dropdown-item" href="{{ route('profile.edit') }}">
                <i class="fas fa-edit me-2"></i>
                Modifier le profil
            </a>
        </li>
        <li>
            <a class="dropdown-item" href="{{ route('profile.change-password') }}">
                <i class="fas fa-key me-2"></i>
                Changer le mot de passe
            </a>
        </li>
        
        @if(user_has_role('admin'))
            <li><hr class="dropdown-divider"></li>
            <li>
                <a class="dropdown-item" href="{{ route('profile.index') }}">
                    <i class="fas fa-users me-2"></i>
                    Gestion des utilisateurs
                </a>
            </li>
        @endif
        
        <li><hr class="dropdown-divider"></li>
        <li>
            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                @csrf
                <button type="submit" class="dropdown-item text-danger">
                    <i class="fas fa-sign-out-alt me-2"></i>
                    Se déconnecter
                </button>
            </form>
        </li>
    </ul>
</div>
