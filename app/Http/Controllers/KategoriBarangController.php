<?php

namespace App\Http\Controllers;

use App\Models\KategoriBarang;
use Illuminate\Http\Request;

class KategoriBarangController extends Controller
{
    public function index()
    {
        $data = KategoriBarang::all();
        return view('kategori.index', compact('data'));
    }

    public function create()
    {
        return view('kategori.create');
    }

    public function store(Request $request)
    {

        $request->validate([
            'nama_kategori' => 'required|unique:kategori_barangs,nama_kategori'
        ], [
            'nama_kategori.required' => 'Nama kategori tidak boleh kosong.',
            'nama_kategori.unique' => 'Kategori dengan nama ini sudah ada.'
        ]);


        KategoriBarang::create([
            'nama_kategori' => $request->nama_kategori
        ]);


        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil ditambahkan');
    }

    public function edit(KategoriBarang $kategori)
    {
        return view('kategori.edit', compact('kategori'));
    }

    public function update(Request $request, KategoriBarang $kategori)
    {
        // Validasi input
        $request->validate([
            'nama_kategori' => 'required|unique:kategori_barangs,nama_kategori,' . $kategori->id
        ], [
            'nama_kategori.required' => 'Nama kategori tidak boleh kosong.',
            'nama_kategori.unique' => 'Kategori dengan nama ini sudah ada.'
        ]);


        $kategori->update([
            'nama_kategori' => $request->nama_kategori
        ]);


        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil diupdate');
    }

    public function destroy(KategoriBarang $kategori)
    {

        $kategori->delete();

        
        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil dihapus');
    }
}
