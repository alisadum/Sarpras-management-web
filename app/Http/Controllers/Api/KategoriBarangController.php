<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KategoriBarang;
use Illuminate\Http\Request;

class KategoriBarangController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum'); // Pastikan hanya yang login yang bisa akses
        $this->middleware('admin')->only(['store', 'update', 'destroy']); // Hanya admin yang bisa akses store, update, destroy
    }

    // Menampilkan semua kategori barang
    public function index()
    {
        return response()->json([
            'status' => true,
            'message' => 'List kategori',
            'data' => KategoriBarang::all()
        ], 200);
    }

    // Menambahkan kategori barang baru (Hanya Admin)
    public function store(Request $request)
    {
        $request->validate([
            'nama_kategori' => 'required'
        ]);

        $kategori = KategoriBarang::create([
            'nama_kategori' => $request->nama_kategori
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Kategori berhasil ditambahkan',
            'data' => $kategori
        ], 201);
    }

    // Menampilkan kategori berdasarkan ID
    public function show($id)
    {
        $kategori = KategoriBarang::find($id);

        if (!$kategori) {
            return response()->json([
                'status' => false,
                'message' => 'Kategori tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $kategori
        ]);
    }

    // Update kategori barang (Hanya Admin)
    public function update(Request $request, $id)
    {
        $kategori = KategoriBarang::find($id);

        if (!$kategori) {
            return response()->json([
                'status' => false,
                'message' => 'Kategori tidak ditemukan'
            ], 404);
        }

        $request->validate([
            'nama_kategori' => 'required'
        ]);

        $kategori->update([
            'nama_kategori' => $request->nama_kategori
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Kategori berhasil diupdate',
            'data' => $kategori
        ]);
    }

    // Hapus kategori barang (Hanya Admin)
    public function destroy($id)
    {
        $kategori = KategoriBarang::find($id);

        if (!$kategori) {
            return response()->json([
                'status' => false,
                'message' => 'Kategori tidak ditemukan'
            ], 404);
        }

        $kategori->delete();

        return response()->json([
            'status' => true,
            'message' => 'Kategori berhasil dihapus'
        ]);
    }
}
