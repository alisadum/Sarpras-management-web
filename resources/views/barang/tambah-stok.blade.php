@extends('layouts.app')
@section('content')
<div class="container">
    <h4 class="mb-4">📦 Tambah Stok - {{ $barang->nama }}</h4>
    
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.barang.storeTambahStok', $barang->id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Stok Saat Ini</label>
                            <input type="number" class="form-control" value="{{ $barang->stok }}" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jumlah Tambahan Unit <span class="text-danger">*</span></label>
                            <input type="number" name="jumlah_tambahan" class="form-control @error('jumlah_tambahan') is-invalid @enderror" min="1" required>
                            @error('jumlah_tambahan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('items.index', $barang->id) }}" class="btn btn-secondary me-md-2">Batal</a>
                            <button type="submit" class="btn btn-primary">Tambah Stok</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection