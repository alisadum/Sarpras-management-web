@extends('layouts.app')

@section('title', 'Penugasan Unit - Peminjaman')

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
        font-family: 'Poppins', sans-serif; /* Menggunakan Poppins dari layouts.app */
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

    .form-check {
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .form-check-input {
        margin: 0;
        cursor: pointer;
        border-color: #e2e8f0;
        transition: var(--transition);
    }

    .form-check-input:checked {
        background-color: var(--primary);
        border-color: var(--primary);
    }

    .form-check-label {
        cursor: pointer;
        font-size: 0.9rem;
        color: var(--text);
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

<!-- SweetAlert2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.12.4/dist/sweetalert2.min.css" rel="stylesheet">

<div class="container">
    <a href="{{ route('admin.borrows.index') }}" class="back-link">
        <i class="bi bi-arrow-left"></i> Kembali ke Daftar Peminjaman
    </a>
    <h2>Penugasan Unit untuk Peminjaman #{{ $borrow->id }} (Jumlah: {{ $borrow->jumlah_unit }})</h2>

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

        <form action="{{ route('admin.borrows.assign.unit', $borrow->id) }}" method="POST" id="assignForm">
            @csrf
            <div class="form-group">
                <label for="unit_ids">Unit Barang (Pilih {{ $borrow->jumlah_unit }} unit yang tersedia)</label>
                @if ($units->isNotEmpty())
                    @foreach ($units as $unit)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="unit_ids[]" value="{{ $unit->id }}" id="unit{{ $unit->id }}">
                            <label class="form-check-label" for="unit{{ $unit->id }}">
                                {{ $unit->kode_barang }} ({{ $borrow->barang->nama }})
                            </label>
                        </div>
                    @endforeach
                @else
                    <p class="error">Tidak ada unit tersedia untuk barang ini. Silakan periksa kembali stok barang.</p>
                @endif
            </div>

            <button type="submit" class="btn btn-primary" {{ $units->isEmpty() ? 'disabled' : '' }}>
                <i class="bi bi-check-circle"></i> Tetapkan Unit
            </button>
        </form>
    </div>
</div>

<!-- jQuery dan SweetAlert2 JS -->
<script src="https://code.jquery.com czyst7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.12.4/dist/sweetalert2.all.min.js"></script>
<script>
    $(document).ready(function() {
        $('#assignForm').on('submit', function(e) {
            const selectedUnits = $('input[name="unit_ids[]"]:checked').length;
            const requiredUnits = {{ $borrow->jumlah_unit }};

            if (selectedUnits !== requiredUnits) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Validasi Gagal',
                    text: 'Mohon pilih tepat ' + requiredUnits + ' unit barang untuk penugasan.',
                    confirmButtonText: 'Tutup',
                    confirmButtonColor: '#dc3545',
                    allowOutsideClick: true,
                    allowEscapeKey: true,
                    showCloseButton: true,
                });
            }
        });
    });
</script>
@endsection