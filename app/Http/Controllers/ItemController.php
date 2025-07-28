<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\UnitBarang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ItemController extends Controller
{
    public function index(Barang $barang)
    {
        $barang->load([
            'unitBarangs' => function ($query) {
                $query->with(['detailBorrows.borrow' => function ($query) {
                    $query->whereIn('status', ['assigned', 'expired']);
                }]);
            },
            'unitBarangs.barang'
        ]);

        return view('item.index', compact('barang'));
    }

    public function create($barangId)
    {
        $barang = Barang::findOrFail($barangId);
        return view('item.create', compact('barang'));
    }

    public function store(Request $request, Barang $barang)
    {
        $validated = $request->validate([
            'kode_barang' => 'required|string|max:255|unique:unit_barangs,kode_barang',
            'kondisi' => 'required|in:Baik,Rusak',
            'status' => 'required|in:Tersedia,Dipinjam,Rusak,Hilang',
            'lokasi' => 'required|string|max:255',
            'stok' => 'required|integer|min:1',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $validated['barang_id'] = $barang->id;
        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('unit_fotos', 'public');
        }

        UnitBarang::create($validated);
        $barang->stok += $validated['stok'];
        $barang->save();

        return redirect()->route('admin.barang.units.index', $barang->id)->with('success', 'Unit barang berhasil ditambahkan.');
    }

    public function edit($barangId, $unitId)
    {
        $barang = Barang::findOrFail($barangId);
        $unit = UnitBarang::findOrFail($unitId);
        return view('item.edit', compact('barang', 'unit'));
    }

    public function update(Request $request, $barangId, $unitId)
    {
        $unit = UnitBarang::findOrFail($unitId);
        $validated = $request->validate([
            'kode_barang' => 'required|string|max:255|unique:unit_barangs,kode_barang,' . $unit->id,
            'kondisi' => 'required|in:Baik,Rusak',
            'status' => 'required|in:Tersedia,Dipinjam,Rusak,Hilang',
            'lokasi' => 'required|string|max:255',
            'stok' => 'required|integer|min:1',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('foto')) {
            if ($unit->foto && Storage::disk('public')->exists($unit->foto)) {
                Storage::disk('public')->delete($unit->foto);
            }
            $validated['foto'] = $request->file('foto')->store('unit_fotos', 'public');
        } else {
            $validated['foto'] = $unit->foto;
        }

        $unit->update($validated);
        $barang = Barang::findOrFail($barangId);
        $barang->stok = $barang->unitBarangs->sum('stok');
        $barang->save();

        return redirect()->route('admin.barang.units.index', $barang->id)->with('success', 'Unit barang berhasil diperbarui.');
    }

    public function destroy($barangId, $unitId)
    {
        $unit = UnitBarang::findOrFail($unitId);
        $barang = Barang::findOrFail($barangId);

        if ($unit->foto && Storage::disk('public')->exists($unit->foto)) {
            Storage::disk('public')->delete($unit->foto);
        }

        $barang->stok -= $unit->stok;
        $barang->save();
        $unit->delete();

        return redirect()->route('admin.barang.units.index', $barang->id)->with('success', 'Unit barang berhasil dihapus.');
    }
}
