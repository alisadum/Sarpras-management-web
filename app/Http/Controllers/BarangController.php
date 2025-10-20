<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\KategoriBarang;
use App\Models\UnitBarang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\BarangExport;
use Maatwebsite\Excel\Facades\Excel;

class BarangController extends Controller
{
    public function index()
    {
        $data = Barang::with('kategori')->get();
        return view('barang.index', compact('data'));
    }

    public function create()
    {
        $kategori = KategoriBarang::all();
        return view('barang.create', compact('kategori'));
    }

    public function show($id)
    {
        $barang = Barang::with(['kategori', 'unitBarangs'])->findOrFail($id);
        return view('barang.show', compact('barang'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'kategori_id' => 'required|exists:kategori_barangs,id',
            'stok' => 'required|integer|min:1',
            'tipe' => 'required|string',
            'kode_prefix' => 'required|string|max:10|alpha_num',
            'lokasi' => 'required|string|max:255',
            'foto' => 'nullable|image|mimes:jpg,png,jpeg,gif|max:2048',
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('barang_foto', 'public');
        }

        $barang = Barang::create([
            'nama' => $validated['nama'],
            'kategori_id' => $validated['kategori_id'],
            'stok' => $validated['stok'],
            'tipe' => $validated['tipe'],
            'foto' => $fotoPath,
        ]);

        $prefix = strtoupper($validated['kode_prefix']);

        for ($i = 1; $i <= $barang->stok; $i++) {
            UnitBarang::create([
                'barang_id' => $barang->id,
                'kode_barang' => $prefix . '-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'kondisi' => 'Baik',
                'status' => 'Tersedia',
                'lokasi' => $validated['lokasi'],
                'stok' => 1,
            ]);
        }

        return redirect()->route('barang.index')->with('success', 'Barang dan unit berhasil ditambahkan!');
    }

    public function destroy($id)
    {
        $barang = Barang::findOrFail($id);
        if ($barang->foto) {
            Storage::disk('public')->delete($barang->foto);
        }
        $barang->delete();
        return redirect()->route('barang.index')->with('success', 'Barang berhasil dihapus.');
    }

    public function edit($id)
    {
        $barang = Barang::findOrFail($id);
        $kategori = KategoriBarang::all();
        return view('barang.edit', compact('barang', 'kategori'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'kategori_id' => 'required|exists:kategori_barangs,id',
            'stok' => 'required|integer|min:1',
            'tipe' => 'required|string',
            'foto' => 'nullable|image|mimes:jpg,png,jpeg,gif|max:2048',
        ]);

        $barang = Barang::findOrFail($id);

        if ($request->hasFile('foto')) {
            if ($barang->foto) {
                Storage::disk('public')->delete($barang->foto);
            }
            $validated['foto'] = $request->file('foto')->store('barang_foto', 'public');
        }

        $barang->update($validated);

        return redirect()->route('barang.index')->with('success', 'Barang berhasil diperbarui.');
    }

    public function tambahStok($id)
{
    $barang = Barang::findOrFail($id);
    return view('barang.tambah-stok', compact('barang'));
}

public function storeTambahStok(Request $request, $id)
{
    $validated = $request->validate([
        'jumlah_tambahan' => 'required|integer|min:1',
    ]);

    $barang = Barang::findOrFail($id);
    $jumlahBaru = $barang->stok + $validated['jumlah_tambahan'];
    
    // Update stok total
    $barang->update(['stok' => $jumlahBaru]);
    
    // Ambil prefix dari unit terakhir
    $lastUnit = UnitBarang::where('barang_id', $barang->id)->orderBy('id', 'desc')->first();
    $nextNumber = $lastUnit ? (int)substr($lastUnit->kode_barang, -3) + 1 : 1;
    $prefix = substr($lastUnit->kode_barang ?? 'XXX', 0, 3);

    // Batch insert unit baru
    $units = [];
    for ($i = 0; $i < $validated['jumlah_tambahan']; $i++) {
        $units[] = [
            'barang_id' => $barang->id,
            'kode_barang' => $prefix . '-' . str_pad($nextNumber + $i, 3, '0', STR_PAD_LEFT),
            'kondisi' => 'Baik',
            'status' => 'Tersedia',
            'lokasi' => $lastUnit->lokasi ?? 'Belum ditentukan',
            'stok' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
    UnitBarang::insert($units);

    return redirect()->route('items.index', $barang->id)->with('success', "Berhasil tambah {$validated['jumlah_tambahan']} unit stok!");
} 

    public function exportPdf()
    {
        $data = Barang::with('kategori')->get();
        $pdf = Pdf::loadView('barang.export.pdf', compact('data'));
        return $pdf->download('laporan-stok-barang.pdf');
    }

    public function exportExcel()
    {
        return Excel::download(new BarangExport, 'laporan-stok-barang.xlsx');
    }
}