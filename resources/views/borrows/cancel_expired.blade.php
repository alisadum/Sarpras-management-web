@extends('layouts.app')

@section('title', 'Batalkan Peminjaman Expired')

@section('content')
<div class="container">
    <h2>Batalkan Peminjaman Expired #{{ $borrow->id }}</h2>

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
            <form action="{{ route('admin.borrows.cancelExpired', $borrow->id) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="alasan" class="form-label">Alasan Pembatalan</label>
                    <textarea class="form-control" id="alasan" name="alasan" rows="4" required></textarea>
                    @error('alasan')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.borrows.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-danger">Batalkan Peminjaman</button>
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