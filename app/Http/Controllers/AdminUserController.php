<?php

namespace App\Http\Controllers;

use App\Models\Opd;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminUserController extends Controller
{
    // Superadmin mengelola akun editor & leader (lintas OPD).
    public function index()
    {
        $users = User::whereIn('role', ['editor', 'leader'])
            ->with('opd')
            ->orderBy('name')
            ->get();

        return Inertia::render('Superadmin/Users/Index', [
            'users' => $users,
        ]);
    }

    public function create()
    {
        return Inertia::render('Superadmin/Users/Create', [
            'opds' => Opd::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email|max:255',
            'role' => 'required|in:editor,leader',
            'opd_id' => 'nullable|exists:opds,id',
            'password' => 'required|string|min:8',
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'opd_id' => $data['opd_id'] ?? null,
            'password' => bcrypt($data['password']),
        ]);

        return redirect()->route('superadmin.users.index')->with('message', 'Akun berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        abort_unless(in_array($user->role, ['editor', 'leader']), 404);

        return Inertia::render('Superadmin/Users/Edit', [
            'user' => $user,
            'opds' => Opd::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        abort_unless(in_array($user->role, ['editor', 'leader']), 404);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|in:editor,leader',
            'opd_id' => 'nullable|exists:opds,id',
        ]);

        $user->update($data);

        return redirect()->route('superadmin.users.index')->with('message', 'Akun berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        abort_unless(in_array($user->role, ['editor', 'leader']), 404);

        $user->delete();

        return redirect()->route('superadmin.users.index')->with('message', 'Akun berhasil dihapus.');
    }
}
