@extends('layouts.app')

@section('title', 'Buat Peminjaman')

@section('content')
<style>
    :root {
        --primary: #4a90e2;
        --primary-hover: #357abd;
        --danger: #dc3545;
        --text: #2d3748;
        --background: #f7fafc;
        --card-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
        --border-radius: 12px;
        --transition: all 0.3s ease;
    }

    body {
        font-family: 'Inter', sans-serif;
        background: var(--background);
        color: var(--text);
        line-height: 1.6;
        margin: 0;
    }

    .container {
        max-width: 600px;
        margin: 2rem auto;
        padding: 0 1rem;
    }

    h2 {
        font-size: 1.8rem;
        font-weight: 700;
        margin-bottom: 1.5rem;
        color: var(--text);
    }

    .card {
        background: #fff;
        border-radius: var(--border-radius);
        box-shadow: var(--card-shadow);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    label {
        display: block;
        font-weight: 600;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }

    input,
    select,
    textarea {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #e2e8f0;
        border-radius: var(--border-radius);
        font-size: 0.9rem;
        transition: var(--transition);
        box-sizing: border-box;
    }

    input:focus,
    select:focus,
    textarea:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
    }

    textarea {
        resize: vertical;
        min-height: 100px;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        border-radius: var(--border-radius);
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 600;
        transition: var(--transition);
        cursor: pointer;
        border: none;
    }

    .btn-primary {
        background: var(--primary);
        color: #fff;
    }

    .btn-primary:hover {
        background: var(--primary-hover);
        transform: translateY(-2px);
    }

    .error {
        color: var(--danger);
        font-size: 0.85rem;
        margin-top: 0.5rem;
    }

    .error ul {
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--primary);
        text-decoration: none;
        font-weight: 500;
        margin-bottom: 1rem;
        font-size: 0.9rem;
        transition: var(--transition);
    }

    .back-link:hover {
        color: var(--primary-hover);
    }

    @media (max-width: 768px) {
        .container {
            padding: 0 0.5rem;
        }
        h2 {
            font-size: 1.5rem;
        }
        .card {
            padding: 1rem;
        }
    }
</style>

<div class="container">
    <a href="{{ route('admin.borrows.index') }}" class="back-link">
        <i class="bi bi-arrow-left"></i> Kembali ke Daftar
    </a>
    <h2>Buat Peminjaman Baru</h2>

    <div class="card">
        @if ($errors->any())
            <div class="error">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.borrows.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="user_id">User</label>
                <select name="user_id" required>
                    <option value="" disabled selected>Pilih User</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="barang_id">Barang</label>
                <select name="barang_id" required>
                    <option value="" disabled selected>Pilih Barang</option>
                    @foreach ($barangs as $barang)
                        <option value="{{ $barang->id }}">{{ $barang->nama }} (Stok: {{ $barang->stok }})</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="jumlah">Jumlah Unit</label>
                <input type="number" name="jumlah" min="1" required>
            </div>

            <div class="form-group">
                <label for="tanggal_pinjam">Tanggal Pinjam</label>
                <input type="date" name="tanggal_pinjam" value="{{ now()->toDateString() }}" required>
            </div>

            <div class="form-group">
                <label for="tanggal_kembali">Tanggal Kembali</label>
                <input type="date" name="tanggal_kembali" required>
            </div>

            <div class="form-group">
                <label for="lokasi">Lokasi</label>
                <input type="text" name="lokasi" required>
            </div>

            <div class="form-group">
                <label for="deskripsi">Deskripsi</label>
                <textarea name="deskripsi"></textarea>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Simpan
            </button>
        </form>
    </div>
</div>
@endsection
