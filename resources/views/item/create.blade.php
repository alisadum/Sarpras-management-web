@extends('layouts.app')

@section('content')
    <div class="bg-white p-6 rounded shadow">
        <form action="{{ route('items.store', $barang->id) }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="mb-4">
                <label for="kode_barang" class="block font-medium">Kode Barang</label>
                <input type="text" name="kode_barang" id="kode_barang" required
                    class="w-full border-gray-300 rounded mt-1" value="{{ old('kode_barang') }}">
            </div>

            <div class="mb-4">
                <label for="kondisi" class="block font-medium">Kondisi</label>
                <select name="kondisi" id="kondisi" required class="w-full border-gray-300 rounded mt-1">
                    <option value="">Pilih Kondisi</option>
                    <option value="Baik">Baik</option>
                    <option value="Rusak">Rusak</option>
                </select>
            </div>

            <div class="mb-4">
                <label for="status" class="block font-medium">Status</label>
                <select name="status" id="status" required class="w-full border-gray-300 rounded mt-1">
                    <option value="">Pilih Status</option>
                    <option value="Tersedia">Tersedia</option>
                    <option value="Tidak Aktif">Tidak Aktif</option>
                </select>
            </div>

            <div class="mb-4">
                <label for="lokasi" class="block font-medium">Lokasi</label>
                <input type="text" name="lokasi" id="lokasi" required
                    class="w-full border-gray-300 rounded mt-1" value="{{ old('lokasi') }}">
            </div>

            <div class="mb-4">
                <label for="stok" class="block font-medium">Stok Unit</label>
                <input type="number" name="stok" id="stok" required min="1"
                    class="w-full border-gray-300 rounded mt-1" value="{{ old('stok') }}">
            </div>

            <div class="mb-4">
                <label for="foto" class="block font-medium">Foto Unit (opsional)</label>
                <input type="file" name="foto" id="foto" accept="image/*"
                    class="w-full border-gray-300 rounded mt-1">
            </div>

            <div class="flex justify-end">
                <a href="{{ route('items.index', $barang->id) }}" class="text-gray-600 mr-4">Batal</a>
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                    Simpan
                </button>
            </div>
        </form>
    </div>
@endsection
