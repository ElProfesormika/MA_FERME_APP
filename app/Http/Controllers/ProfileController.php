<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Afficher le profil de l'utilisateur connecté
     */
    public function show(): View
    {
        $user = Auth::user();
        $roles = User::ROLES;
        $permissions = User::PERMISSIONS;
        
        return view('profile.show', compact('user', 'roles', 'permissions'));
    }

    /**
     * Afficher le formulaire de modification du profil
     */
    public function edit(): View
    {
        $user = Auth::user();
        $roles = User::ROLES;
        $permissions = User::PERMISSIONS;
        
        return view('profile.edit', compact('user', 'roles', 'permissions'));
    }

    /**
     * Mettre à jour le profil de l'utilisateur
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        return redirect()->route('profile.show')->with('success', 'Profil mis à jour avec succès !');
    }

    /**
     * Afficher le formulaire de changement de mot de passe
     */
    public function changePassword(): View
    {
        return view('profile.change-password');
    }

    /**
     * Mettre à jour le mot de passe
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = Auth::user();
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('profile.show')->with('success', 'Mot de passe mis à jour avec succès !');
    }

    /**
     * Afficher la liste des utilisateurs (admin seulement)
     */
    public function index(): View
    {
        $this->authorize('viewAny', User::class);
        
        $users = User::orderBy('created_at', 'desc')->paginate(10);
        $roles = User::ROLES;
        
        return view('profile.index', compact('users', 'roles'));
    }

    /**
     * Afficher un utilisateur spécifique (admin seulement)
     */
    public function showUser(User $user): View
    {
        $this->authorize('view', $user);
        
        $roles = User::ROLES;
        $permissions = User::PERMISSIONS;
        
        return view('profile.show-user', compact('user', 'roles', 'permissions'));
    }

    /**
     * Modifier un utilisateur (admin seulement)
     */
    public function editUser(User $user): View
    {
        $this->authorize('update', $user);
        
        $roles = User::ROLES;
        $permissions = User::PERMISSIONS;
        
        return view('profile.edit-user', compact('user', 'roles', 'permissions'));
    }

    /**
     * Mettre à jour un utilisateur (admin seulement)
     */
    public function updateUser(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', 'string', 'in:' . implode(',', array_keys(User::ROLES))],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['string', 'in:' . implode(',', array_keys(User::PERMISSIONS))],
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'permissions' => $request->permissions ?? [],
        ]);

        return redirect()->route('profile.index')->with('success', 'Utilisateur mis à jour avec succès !');
    }

    /**
     * Supprimer un utilisateur (admin seulement)
     */
    public function destroyUser(User $user)
    {
        $this->authorize('delete', $user);

        if ($user->id === Auth::id()) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $user->delete();

        return redirect()->route('profile.index')->with('success', 'Utilisateur supprimé avec succès !');
    }
}
