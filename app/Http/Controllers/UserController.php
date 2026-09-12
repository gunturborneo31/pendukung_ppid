<?php

namespace App\Http\Controllers;

use App\Models\Opd;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Throwable;
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
        $hasOpdPivot = Schema::hasTable('opd_user');

        $query = User::where('role', 'contributor')
            ->with('opd')
            ->orderBy('name');

        if ($hasOpdPivot) {
            $query->with('accessibleOpds');
        }

        if ($request->filled('opd_id')) {
            if ($hasOpdPivot) {
                $query->where(function ($innerQuery) use ($request) {
                    $innerQuery->where('opd_id', $request->opd_id)
                        ->orWhereHas('accessibleOpds', fn ($opdQuery) => $opdQuery->where('opds.id', $request->opd_id));
                });
            } else {
                $query->where('opd_id', $request->opd_id);
            }
        }

        $contributors = $query->get();

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
            'opd_ids' => 'required|array|min:1',
            'opd_ids.*' => 'integer|distinct|exists:opds,id',
            'password' => 'required|string|min:8',
        ]);

        try {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'field' => $data['field'] ?? null,
                'opd_id' => $data['opd_ids'][0],
                'role' => 'contributor',
                'password' => bcrypt($data['password']),
            ]);
            if (Schema::hasTable('opd_user')) {
                $user->accessibleOpds()->sync($data['opd_ids']);
            }
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with('error', $this->contributorFailureReason($exception, 'menyimpan akun kontributor'));
        }

        return redirect()->route($routeBase)->with('message', 'Akun kontributor berhasil ditambahkan.');
    }

    // Show edit form
    public function edit(Request $request, User $user)
    {
        abort_unless($user->role === 'contributor', 404);

        $routeBase = $this->routeBase($request);

        $hasOpdPivot = Schema::hasTable('opd_user');

        return Inertia::render('Editor/EditContributor', [
            'user' => $hasOpdPivot ? $user->load('accessibleOpds') : $user,
            'opds' => Opd::where('is_active', true)->orderBy('name')->get(),
            'selected_opd_ids' => $user->accessibleOpdIds(),
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
            'opd_ids' => 'required|array|min:1',
            'opd_ids.*' => 'integer|distinct|exists:opds,id',
        ]);

        try {
            $user->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'field' => $data['field'] ?? null,
                'opd_id' => $data['opd_ids'][0],
            ]);
            if (Schema::hasTable('opd_user')) {
                $user->accessibleOpds()->sync($data['opd_ids']);
            }
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with('error', $this->contributorFailureReason($exception, 'menyimpan perubahan kontributor'));
        }

        return redirect()->route($routeBase)->with('message', 'Akun kontributor diperbarui.');
    }

    // Delete contributor
    public function destroy(Request $request, User $user)
    {
        abort_unless($user->role === 'contributor', 404);

        $routeBase = $this->routeBase($request);

        try {
            $user->delete();
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', $this->contributorFailureReason($exception, 'menghapus kontributor'));
        }

        return redirect()->route($routeBase)->with('message', 'Akun kontributor dihapus.');
    }

    private function contributorFailureReason(Throwable $exception, string $action): string
    {
        if ($exception instanceof QueryException) {
            $message = strtolower((string) $exception->getMessage());

            if (
                str_contains($message, 'duplicate entry')
                || str_contains($message, 'duplicate key value violates unique constraint')
                || str_contains($message, 'unique constraint failed')
                || str_contains($message, 'sqlstate[23000]')
            ) {
                return "Gagal {$action}: email sudah digunakan oleh akun lain.";
            }

            if (
                str_contains($message, 'foreign key constraint fails')
                || str_contains($message, 'violates foreign key constraint')
                || str_contains($message, 'foreign key constraint failed')
            ) {
                return "Gagal {$action}: data OPD tidak valid atau masih dipakai relasi lain.";
            }

            if (
                str_contains($message, "table 'pendukung_ppid.opd_user' doesn't exist")
                || str_contains($message, "base table or view not found")
                || str_contains($message, 'no such table: opd_user')
            ) {
                return "Gagal {$action}: tabel relasi opd_user belum tersedia. Jalankan migrasi database terlebih dahulu.";
            }

            if (str_contains($message, 'data too long')) {
                return "Gagal {$action}: ada data yang melebihi batas maksimal kolom database.";
            }
        }

        return "Gagal {$action}: terjadi kesalahan pada server. Silakan coba lagi atau hubungi admin.";
    }
}
