@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 800px; margin: 0 auto; padding: 20px;">
    <h1 style="font-size: 2rem; font-weight: bold; color: #4C51BF; margin-bottom: 20px;">📦 Edit Barang</h1>

    @if ($errors->any())
        <div style="background-color: #FEE2E2; padding: 12px; border-radius: 8px; margin-bottom: 20px;">
            <ul style="margin: 0; padding-left: 20px; color: #E53E3E;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('barang.update', $barang->id) }}" enctype="multipart/form-data" style="background-color: #fff; padding: 20px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); border-radius: 12px;">
        @csrf
        @method('PUT')

        {{-- Nama Barang --}}
        <div style="margin-bottom: 20px;">
            <label for="nama" style="display: block; font-weight: 600; color: #333; margin-bottom: 8px;">Nama Barang</label>
            <input type="text" name="nama" id="nama" value="{{ old('nama', $barang->nama) }}"
                style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px; font-size: 1rem; color: #333;"
                placeholder="Contoh: Proyektor" required>
            @error('nama')
                <p style="font-size: 0.875rem; color: #E53E3E; margin-top: 8px;">{{ $message }}</p>
            @enderror
        </div>

        {{-- Kategori --}}
        <div style="margin-bottom: 20px;">
            <label for="kategori_id" style="display: block; font-weight: 600; color: #333; margin-bottom: 8px;">Kategori</label>
            <select name="kategori_id" id="kategori_id" required
                style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px; font-size: 1rem; color: #333;">
                <option value="">-- Pilih Kategori --</option>
                @foreach ($kategori as $item)
                    <option value="{{ $item->id }}" {{ old('kategori_id', $barang->kategori_id) == $item->id ? 'selected' : '' }}>{{ $item->nama_kategori }}</option>
                @endforeach
            </select>
            @error('kategori_id')
                <p style="font-size: 0.875rem; color: #E53E3E; margin-top: 8px;">{{ $message }}</p>
            @enderror
        </div>

        {{-- Tipe --}}
        <div style="margin-bottom: 20px;">
            <label style="display: block; font-weight: 600; color: #333; margin-bottom: 8px;">Tipe Barang</label>
            <select name="tipe" required
                style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px; font-size: 1rem; color: #333;">
                <option value="">-- Pilih Tipe --</option>
                <option value="Barang Sekali Pakai" {{ old('tipe', $barang->tipe) == 'Barang Sekali Pakai' ? 'selected' : '' }}>Barang Sekali Pakai</option>
                <option value="Barang Dikembalikan" {{ old('tipe', $barang->tipe) == 'Barang Dikembalikan' ? 'selected' : '' }}>Barang Dikembalikan</option>
            </select>
            @error('tipe')
                <p style="font-size: 0.875rem; color: #E53E3E; margin-top: 8px;">{{ $message }}</p>
            @enderror
        </div>

        {{-- Stok --}}
        <div style="margin-bottom: 20px;">
            <label for="stok" style="display: block; font-weight: 600; color: #333; margin-bottom: 8px;">Stok</label>
            <input type="number" name="stok" id="stok" value="{{ old('stok', $barang->stok) }}"
                style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px; font-size: 1rem; color: #333;"
                placeholder="Masukkan jumlah stok..." required min="1">
            @error('stok')
                <p style="font-size: 0.875rem; color: #E53E3E; margin-top: 8px;">{{ $message }}</p>
            @enderror
        </div>

        {{-- Foto --}}
        <div style="margin-bottom: 20px;">
            <label for="foto" style="display: block; font-weight: 600; color: #333; margin-bottom: 8px;">Foto Barang</label>
            <input type="file" name="foto" id="foto" accept="image/*"
                style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px;">
            @if ($barang->foto)
                <div style="margin-top: 15px;">
                    <p style="font-size: 1rem; font-weight: 600; color: #333;">Foto Saat Ini:</p>
                    <img src="{{ asset('storage/' . $barang->foto) }}" style="width: 160px; height: 160px; object-fit: cover; border-radius: 8px; border: 1px solid #ccc;">
                </div>
            @endif
            <div id="preview" style="margin-top: 15px; display: none;">
                <p style="font-size: 1rem; font-weight: 600; color: #333;">Preview Gambar:</p>
                <img id="imagePreview" src="" style="width: 160px; height: 160px; object-fit: cover; border-radius: 8px; border: 1px solid #ccc;">
            </div>
        </div>

        {{-- Tombol Submit --}}
        <div>
            <button type="submit" style="padding: 12px 24px; background-color: #4C51BF; color: white; font-size: 1.1rem; font-weight: 600; border: none; border-radius: 8px; cursor: pointer; transition: background-color 0.3s ease;">
                💾 Update Barang
            </button>
            <a href="{{ route('barang.index') }}" style="padding: 12px 24px; background-color: #6B7280; color: white; font-size: 1.1rem; font-weight: 600; border: none; border-radius: 8px; text-decoration: none; margin-left: 10px;">
                Kembali
            </a>
        </div>
    </form>
</div>

<script>
    document.getElementById('foto').addEventListener('change', function (e) {
        const [file] = e.target.files;
        if (file) {
            const preview = document.getElementById('preview');
            const img = document.getElementById('imagePreview');
            img.src = URL.createObjectURL(file);
            preview.style.display = 'block';
        }
    });
</script>
@endsection
