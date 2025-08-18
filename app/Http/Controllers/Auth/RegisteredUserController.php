<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $roles = User::ROLES;
        $permissions = User::PERMISSIONS;
        
        return view('auth.register', compact('roles', 'permissions'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'string', 'in:' . implode(',', array_keys(User::ROLES))],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['string', 'in:' . implode(',', array_keys(User::PERMISSIONS))],
        ]);

        // Définir les permissions par défaut selon le rôle
        $defaultPermissions = $this->getDefaultPermissionsForRole($request->role);
        $permissions = $request->permissions ?? $defaultPermissions;

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'permissions' => $permissions,
            'email_verified_at' => now(), // Auto-verification pour simplifier
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(RouteServiceProvider::HOME)->with('success', 'Compte créé avec succès !');
    }

    /**
     * Obtenir les permissions par défaut selon le rôle
     */
    private function getDefaultPermissionsForRole(string $role): array
    {
        $defaultPermissions = [
            'admin' => [
                'animaux', 'stocks', 'activites', 'employes', 
                'alertes', 'rapports', 'equipe', 'systeme'
            ],
            'manager' => [
                'animaux', 'stocks', 'activites', 'employes', 
                'alertes', 'rapports'
            ],
            'user' => [
                'animaux', 'stocks', 'activites'
            ],
        ];

        return $defaultPermissions[$role] ?? [];
    }
} 