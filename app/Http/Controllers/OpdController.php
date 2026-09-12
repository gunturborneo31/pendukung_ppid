<?php

namespace App\Http\Controllers;

use App\Models\Opd;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class OpdController extends Controller
{
    public function index()
    {
        $opds = Opd::withCount(['users', 'articles'])
            ->orderBy('name')
            ->paginate(15);

        return Inertia::render('Superadmin/Opds/Index', [
            'opds' => $opds,
        ]);
    }

    public function create()
    {
        return Inertia::render('Superadmin/Opds/Create', [
            'regions' => $this->regions(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:opds,code',
            'daerah' => ['required', Rule::in($this->regions())],
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:30',
            'is_active' => 'nullable|boolean',
        ]);

        Opd::create($data);

        return redirect()->route('superadmin.opds.index')->with('message', 'OPD berhasil ditambahkan.');
    }

    public function edit(Opd $opd)
    {
        return Inertia::render('Superadmin/Opds/Edit', [
            'opd' => $opd,
            'regions' => $this->regions(),
        ]);
    }

    public function update(Request $request, Opd $opd)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:opds,code,' . $opd->id,
            'daerah' => ['required', Rule::in($this->regions())],
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:30',
            'is_active' => 'nullable|boolean',
        ]);

        $opd->update($data);

        return redirect()->route('superadmin.opds.index')->with('message', 'OPD berhasil diperbarui.');
    }

    public function destroy(Opd $opd)
    {
        if ($opd->users()->exists() || $opd->articles()->exists()) {
            return back()->with('error', 'OPD tidak bisa dihapus karena masih memiliki pengguna atau artikel. Nonaktifkan saja OPD ini.');
        }

        $opd->delete();

        return redirect()->route('superadmin.opds.index')->with('message', 'OPD berhasil dihapus.');
    }

    private function regions(): array
    {
        return [
            'Prov. Kaltim',
            'Samarinda',
            'Balikpapan',
            'Kukar',
            'Kubar',
            'Kutim',
            'Mahulu',
            'Paser',
            'Pu',
            'Bontang',
            'Berau',
        ];
    }
}
