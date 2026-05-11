<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    // List all contributors for editor
    public function index()
    {
        $contributors = User::where('role', 'contributor')->get();
        return Inertia::render('Editor/Contributors', [
            'contributors' => $contributors,
        ]);
    }

    // Show create form
    public function create()
    {
        return Inertia::render('Editor/CreateContributor');
    }

    // Store new contributor
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email|max:255',
            'field' => 'nullable|string|max:255',
            'password' => 'required|string|min:8',
        ]);
        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'field' => $data['field'] ?? null,
            'role' => 'contributor',
            'password' => bcrypt($data['password']),
        ]);
        return redirect()->route('editor.contributors')->with('message', 'Akun kontributor berhasil ditambahkan.');
    }

    // Show edit form
    public function edit(User $user)
    {
        return Inertia::render('Editor/EditContributor', [
            'user' => $user,
        ]);
    }

    // Update contributor
    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'field' => 'nullable|string|max:255',
        ]);
        $user->update($data);
        return redirect()->route('editor.contributors')->with('message', 'Akun kontributor diperbarui.');
    }

    // Delete contributor
    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('editor.contributors')->with('message', 'Akun kontributor dihapus.');
    }
}
