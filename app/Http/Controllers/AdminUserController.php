<?php

namespace App\Http\Controllers;

use App\Models\Opd;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;
use Inertia\Inertia;

class AdminUserController extends Controller
{
    // Superadmin mengelola akun editor, leader, dan uploader.
    public function index()
    {
        $query = User::whereIn('role', ['editor', 'leader', 'uploader'])
            ->with('opd')
            ->orderBy('name');

        if (Schema::hasTable('opd_user')) {
            $query->with('accessibleOpds');
        }

        $users = $query->get();

        return Inertia::render('Superadmin/Users/Index', [
            'users' => $users,
        ]);
    }

    public function create()
    {
        return Inertia::render('Superadmin/Users/Create', [
            'opds' => Opd::where('is_active', true)->orderBy('name')->get(),
            'supportsUploaderRole' => $this->supportsUploaderRole(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email|max:255',
            'role' => 'required|in:editor,leader,uploader',
            'opd_ids' => 'nullable|array',
            'opd_ids.*' => 'integer|distinct|exists:opds,id',
            'password' => 'required|string|min:8',
        ]);

        $opdIds = $data['opd_ids'] ?? [];

        if ($data['role'] === 'uploader' && !$this->supportsUploaderRole()) {
            return back()
                ->withInput()
                ->with('error', 'Role uploader belum tersedia di database. Jalankan migrasi terbaru terlebih dahulu.');
        }

        try {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'role' => $data['role'],
                'opd_id' => !empty($opdIds) ? $opdIds[0] : null,
                'password' => bcrypt($data['password']),
            ]);

            if (Schema::hasTable('opd_user')) {
                $user->accessibleOpds()->sync($opdIds);
            }
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with('error', $this->userFailureReason($exception, 'menyimpan akun'));
        }

        return redirect()->route('superadmin.users.index')->with('message', 'Akun berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        abort_unless(in_array($user->role, ['editor', 'leader', 'uploader']), 404);

        $hasOpdPivot = Schema::hasTable('opd_user');

        return Inertia::render('Superadmin/Users/Edit', [
            'user' => $hasOpdPivot ? $user->load('accessibleOpds') : $user,
            'opds' => Opd::where('is_active', true)->orderBy('name')->get(),
            'selected_opd_ids' => $user->accessibleOpdIds(),
            'supportsUploaderRole' => $this->supportsUploaderRole(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        abort_unless(in_array($user->role, ['editor', 'leader', 'uploader']), 404);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|in:editor,leader,uploader',
            'opd_ids' => 'nullable|array',
            'opd_ids.*' => 'integer|distinct|exists:opds,id',
        ]);

        $opdIds = $data['opd_ids'] ?? [];

        if ($data['role'] === 'uploader' && !$this->supportsUploaderRole()) {
            return back()
                ->withInput()
                ->with('error', 'Role uploader belum tersedia di database. Jalankan migrasi terbaru terlebih dahulu.');
        }

        try {
            $user->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'role' => $data['role'],
                'opd_id' => !empty($opdIds) ? $opdIds[0] : null,
            ]);

            if (Schema::hasTable('opd_user')) {
                $user->accessibleOpds()->sync($opdIds);
            }
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with('error', $this->userFailureReason($exception, 'memperbarui akun'));
        }

        return redirect()->route('superadmin.users.index')->with('message', 'Akun berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        abort_unless(in_array($user->role, ['editor', 'leader', 'uploader']), 404);

        try {
            $user->delete();
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', $this->userFailureReason($exception, 'menghapus akun'));
        }

        return redirect()->route('superadmin.users.index')->with('message', 'Akun berhasil dihapus.');
    }

    private function supportsUploaderRole(): bool
    {
        try {
            $column = DB::selectOne("SHOW COLUMNS FROM users LIKE 'role'");

            if (!$column || !isset($column->Type)) {
                return false;
            }

            return str_contains(strtolower((string) $column->Type), "'uploader'");
        } catch (Throwable $exception) {
            report($exception);

            return false;
        }
    }

    private function userFailureReason(Throwable $exception, string $action): string
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
                str_contains($message, 'data truncated for column \'role\'')
                || str_contains($message, 'data truncated for column \"role\"')
            ) {
                return "Gagal {$action}: role yang dipilih belum didukung database aktif. Jalankan migrasi terbaru terlebih dahulu.";
            }

            if (
                str_contains($message, 'foreign key constraint fails')
                || str_contains($message, 'violates foreign key constraint')
                || str_contains($message, 'foreign key constraint failed')
            ) {
                return "Gagal {$action}: data OPD tidak valid atau masih dipakai relasi lain.";
            }
        }

        return "Gagal {$action}: terjadi kesalahan pada server. Silakan coba lagi atau hubungi admin.";
    }
}
