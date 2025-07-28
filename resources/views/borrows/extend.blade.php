@extends('layouts.app')

@section('title', 'Perpanjang Peminjaman')

@section('content')
<div class="container">
    <h2>Perpanjang Peminjaman #{{ $borrow->id }}</h2>

    @if (session('success'))
        <div class="alert success" role="alert">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
            <button type="button" class="alert-close" aria-label="Close">✕</button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert error" role="alert">
            <i class="bi bi-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="alert-close" aria-label="Close">✕</button>
        </div>
    @endif

    <div class="card">
        <div class="modal-body p-4">
            <form action="{{ route('admin.borrows.extend', $borrow->id) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="tanggal_kembali_baru" class="form-label">Tanggal Kembali Baru</label>
                    <input type="date" class="form-control" id="tanggal_kembali_baru" name="tanggal_kembali_baru" 
                           min="{{ \Carbon\Carbon::parse($borrow->tanggal_kembali)->addDay()->format('Y-m-d') }}" 
                           required>
                    @error('tanggal_kembali_baru')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.borrows.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-success">Perpanjang</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.alert-close').forEach(button => {
        button.addEventListener('click', () => {
            button.parentElement.style.display = 'none';
        });
    });
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(alert => {
            alert.style.display = 'none';
        });
    }, 5000);
</script>
@endsection