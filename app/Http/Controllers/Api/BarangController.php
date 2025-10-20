<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\KategoriBarang;
use App\Models\UnitBarang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class BarangController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('admin')->only(['store', 'update', 'destroy']);
    }

    public function index()
    {
        $barang = Barang::with('kategori')->get();
        return response()->json($barang);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'stok' => 'required|integer|min:1',
            'kategori_id' => 'required|exists:kategori_barangs,id',
            'tipe' => 'required|string',
            'foto' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('foto_barang', 'public');
        }

        $barang = Barang::create($validated);

        $kategori = KategoriBarang::find($validated['kategori_id']);
        $prefix = strtoupper(substr($kategori->nama ?? 'XXX', 0, 3));

        for ($i = 1; $i <= $barang->stok; $i++) {
            UnitBarang::create([
                'barang_id' => $barang->id,
                'kode_barang' => $prefix . '-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'kondisi' => 'Baik',
                'status' => 'Tersedia',
                'lokasi' => 'Belum ditentukan',
                'stok' => 1
            ]);
        }

        return response()->json([
            'message' => 'Barang berhasil ditambahkan!',
            'data' => $barang
        ], 201);
    }

    public function show($id)
    {
        $barang = Barang::with('kategori')->findOrFail($id);
        return response()->json($barang);
    }

    public function update(Request $request, Barang $barang)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'stok' => 'required|integer',
            'kategori_id' => 'required|exists:kategori_barangs,id',
            'tipe' => 'required|string',
            'foto' => 'nullable|image|max:2048',
        ]);

        $stokLama = $barang->stok;
        $stokBaru = $validated['stok'];
        $selisih = $stokBaru - $stokLama;

        if ($request->hasFile('foto')) {
            if ($barang->foto) {
                Storage::disk('public')->delete($barang->foto);
            }
            $validated['foto'] = $request->file('foto')->store('foto_barang', 'public');
        }

        $barang->update($validated);

        if ($selisih > 0) {
            $kategori = KategoriBarang::find($validated['kategori_id']);
            $prefix = strtoupper(substr($kategori->nama ?? 'XXX', 0, 3));
            $lastUnit = UnitBarang::where('barang_id', $barang->id)->orderBy('kode_barang', 'desc')->first();
            $nextNumber = $lastUnit ? (int)substr($lastUnit->kode_barang, -3) + 1 : $stokLama + 1;

            for ($i = 0; $i < $selisih; $i++) {
                UnitBarang::create([
                    'barang_id' => $barang->id,
                    'kode_barang' => $prefix . '-' . str_pad($nextNumber + $i, 3, '0', STR_PAD_LEFT),
                    'kondisi' => 'Baik',
                    'status' => 'Tersedia',
                    'lokasi' => 'Belum ditentukan',
                    'stok' => 1
                ]);
            }
        }

        return response()->json([
            'message' => 'Barang berhasil diperbarui!',
            'data' => $barang
        ]);
    }

    public function destroy(Barang $barang)
    {
        if ($barang->foto) {
            Storage::disk('public')->delete($barang->foto);
        }

        $barang->delete();
        return response()->json(['message' => 'Barang berhasil dihapus!']);
    }
}