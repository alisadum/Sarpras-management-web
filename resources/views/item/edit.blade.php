@extends('layouts.app')

@section('content')
<style>
    /* (CSS tetap seperti sebelumnya, tidak diubah) */
    .container {
        max-width: 800px;
        margin: 0 auto;
        background: #fff;
        padding: 24px;
        border-radius: 12px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    .section-title {
        font-size: 20px;
        font-weight: bold;
        color: #333;
        margin-bottom: 20px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
    }

    .form-group input,
    .form-group select {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 8px;
        font-size: 1rem;
        color: #333;
    }

    .form-group input[type="file"] {
        padding: 10px 0;
    }

    .error-message {
        font-size: 0.875rem;
        color: #E53E3E;
        margin-top: 8px;
    }

    .preview-image {
        margin-top: 15px;
        display: none;
    }

    .preview-image img {
        width: 160px;
        height: 160px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid #ccc;
    }

    .current-image {
        margin-top: 15px;
    }

    .current-image img {
        width: 160px;
        height: 160px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid #ccc;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 12px 24px;
        font-size: 1rem;
        font-weight: 600;
        border-radius: 8px;
        text-decoration: none;
        transition: all 0.3s ease;
        cursor: pointer;
        border: none;
    }

    .btn-primary {
        background-color: #4C51BF;
        color: white;
    }

    .btn-primary:hover {
        background-color: #3b3f9b;
        box-shadow: 0 4px 12px rgba(76, 81, 191, 0.3);
        transform: translateY(-2px);
    }

    .btn-primary:active {
        transform: translateY(0);
        box-shadow: 0 2px 6px rgba(76, 81, 191, 0.2);
    }

    .btn-secondary {
        background-color: #6B7280;
        color: white;
    }

    .btn-secondary:hover {
        background-color: #5a6268;
        box-shadow: 0 4px 12px rgba(107, 114, 128, 0.3);
        transform: translateY(-2px);
    }

    .btn-secondary:active {
        transform: translateY(0);
        box-shadow: 0 2px 6px rgba(107, 114, 128, 0.2);
    }

    .button-group {
        margin-top: 20px;
        display: flex;
        gap: 10px;
    }
</style>

<div class="container">
    <h2 class="section-title">Edit Unit Barang: {{ $barang->nama }}</h2>
    <form action="{{ route('admin.barang.units.update', ['barang' => $barang->id, 'unit' => $unit->id]) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="kode_barang">Kode Barang</label>
            <input type="text" name="kode_barang" id="kode_barang" value="{{ old('kode_barang', $unit->kode_barang) }}" required>
            @error('kode_barang')
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="kondisi">Kondisi</label>
            <select name="kondisi" id="kondisi" required>
                <option value="Baik" {{ old('kondisi', $unit->kondisi) == 'Baik' ? 'selected' : '' }}>Baik</option>
                <option value="Rusak" {{ old('kondisi', $unit->kondisi) == 'Rusak' ? 'selected' : '' }}>Rusak</option>
            </select>
            @error('kondisi')
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="status">Status</label>
            <select name="status" id="status" required>
                <option value="Tersedia" {{ old('status', $unit->status) == 'Tersedia' ? 'selected' : '' }}>Tersedia</option>
                <option value="Dipinjam" {{ old('status', $unit->status) == 'Dipinjam' ? 'selected' : '' }}>Dipinjam</option>
                <option value="Rusak" {{ old('status', $unit->status) == 'Rusak' ? 'selected' : '' }}>Rusak</option>
                <option value="Hilang" {{ old('status', $unit->status) == 'Hilang' ? 'selected' : '' }}>Hilang</option>
            </select>
            @error('status')
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="lokasi">Lokasi</label>
            <input type="text" name="lokasi" id="lokasi" value="{{ old('lokasi', $unit->lokasi) }}" required>
            @error('lokasi')
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="foto">Foto Unit</label>
            <input type="file" name="foto" id="foto" accept="image/*">
            @if($unit->foto)
                <div class="current-image">
                    <p style="font-size: 1rem; font-weight: 600; color: #333;">Foto Saat Ini:</p>
                    <img src="{{ asset('storage/' . $unit->foto) }}" alt="Foto Unit">
                </div>
            @else
                <p style="font-size: 0.875rem; color: #999; margin-top: 4px;">Foto barang ini mengikuti foto induk.</p>
            @endif
            <div class="preview-image" id="preview">
                <p style="font-size: 1rem; font-weight: 600; color: #333;">Preview Gambar:</p>
                <img id="imagePreview" src="" alt="Preview">
            </div>
            @error('foto')
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        <div class="button-group">
            <button type="submit" class="btn btn-primary">💾 Simpan Perubahan</button>
            <a href="{{ route('admin.barang.units.index', $barang->id) }}" class="btn btn-secondary">← Kembali</a>
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
