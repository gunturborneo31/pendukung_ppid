<?php

namespace App\Http\Controllers;

use App\Models\Opd;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    private function routeBase(Request $request): string
    {
        return $request->routeIs('superadmin.*') ? 'superadmin.contributors' : 'editor.contributors';
    }

    // List all contributors for editor (bisa difilter per OPD, default semua OPD).
    public function index(Request $request)
    {
        $routeBase = $this->routeBase($request);

        $contributors = User::where('role', 'contributor')
            ->with('opd')
            ->when($request->filled('opd_id'), fn ($q) => $q->where('opd_id', $request->opd_id))
            ->orderBy('name')
            ->get();

        return Inertia::render('Editor/Contributors', [
            'contributors' => $contributors,
            'opds' => Opd::where('is_active', true)->orderBy('name')->get(),
            'filters' => $request->only('opd_id'),
            'routeBase' => $routeBase,
        ]);
    }

    // Show create form
    public function create(Request $request)
    {
        $routeBase = $this->routeBase($request);

        return Inertia::render('Editor/CreateContributor', [
            'opds' => Opd::where('is_active', true)->orderBy('name')->get(),
            'routeBase' => $routeBase,
        ]);
    }

    // Store new contributor
    public function store(Request $request)
    {
        $routeBase = $this->routeBase($request);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email|max:255',
            'field' => 'nullable|string|max:255',
            'opd_id' => 'required|exists:opds,id',
            'password' => 'required|string|min:8',
        ]);
        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'field' => $data['field'] ?? null,
            'opd_id' => $data['opd_id'],
            'role' => 'contributor',
            'password' => bcrypt($data['password']),
        ]);

        return redirect()->route($routeBase)->with('message', 'Akun kontributor berhasil ditambahkan.');
    }

    // Show edit form
    public function edit(Request $request, User $user)
    {
        abort_unless($user->role === 'contributor', 404);

        $routeBase = $this->routeBase($request);

        return Inertia::render('Editor/EditContributor', [
            'user' => $user,
            'opds' => Opd::where('is_active', true)->orderBy('name')->get(),
            'routeBase' => $routeBase,
        ]);
    }

    // Update contributor
    public function update(Request $request, User $user)
    {
        abort_unless($user->role === 'contributor', 404);

        $routeBase = $this->routeBase($request);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'field' => 'nullable|string|max:255',
            'opd_id' => 'required|exists:opds,id',
        ]);

        $user->update($data);

        return redirect()->route($routeBase)->with('message', 'Akun kontributor diperbarui.');
    }

    // Delete contributor
    public function destroy(Request $request, User $user)
    {
        abort_unless($user->role === 'contributor', 404);

        $routeBase = $this->routeBase($request);

        $user->delete();

        return redirect()->route($routeBase)->with('message', 'Akun kontributor dihapus.');
    }
}
