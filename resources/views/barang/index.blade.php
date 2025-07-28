@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-4 fw-semibold" style="color: #2c3e50;">📦 Data Barang</h4>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="mb-3">
        <a href="{{ route(auth('admin')->check() ? 'admin.barang.create' : 'barang.create') }}" class="btn btn-primary" style="background-color: #4F46E5; border: none;">
            + Tambah Barang
        </a>
        <a href="{{ route(auth('admin')->check() ? 'admin.barang.export.pdf' : 'barang.export.pdf') }}" class="btn btn-danger me-2">Export PDF</a>
        <a href="{{ route(auth('admin')->check() ? 'admin.barang.export.excel' : 'barang.export.excel') }}" class="btn btn-success">Export Excel</a>
    </div>

    <div class="card shadow-sm border-0 rounded" style="background-color: #ffffff;">
        <div class="card-body">
            <table id="barangTable" class="table table-striped align-middle text-sm">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Foto</th>
                        <th>Nama</th>
                        <th>Tipe</th>
                        <th>Kategori</th>
                        <th>Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @if($data->isEmpty())
                        <tr>
                            <td colspan="7" class="text-center">No data available</td>
                        </tr>
                    @else
                        @foreach($data as $key => $item)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>
                                    @if($item->foto)
                                        <img src="{{ asset('storage/' . $item->foto) }}" width="60" height="60" style="object-fit: cover; border-radius: 8px;" alt="Foto">
                                    @else
                                        <span class="badge bg-secondary">No Photo</span>
                                    @endif
                                </td>
                                <td>{{ $item->nama }}</td>
                                <td>{{ $item->tipe }}</td>
                                <td>{{ $item->kategori->nama_kategori ?? 'N/A' }}</td>
                                <td>{{ $item->stok }}</td>
                                <td>
                                    <a href="{{ route('items.index', $item->id) }}" class="btn btn-sm btn-outline-info" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route(auth('admin')->check() ? 'admin.barang.edit' : 'barang.edit', $item->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form action="{{ route(auth('admin')->check() ? 'admin.barang.destroy' : 'barang.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin hapus?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- DataTables CSS -->
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        $('#barangTable').DataTable({
            paging: true,
            lengthChange: false,
            searching: true,
            ordering: true,
            info: true,
            autoWidth: false
        });
    });
</script>
@endsection