@extends('layouts.app')

@section('title', 'Laporan Peminjaman')

@section('content')
<style>
    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
        font-family: 'Poppins', sans-serif;
    }

    h2 {
        color: #1a3c6d;
        font-size: 28px;
        margin-bottom: 20px;
        font-weight: 600;
    }

    .card {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        padding: 20px;
        margin-bottom: 20px;
    }

    .table-responsive {
        overflow-x: auto;
        border-radius: 8px;
    }

    .table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .table th, .table td {
        padding: 14px;
        text-align: left;
        border-bottom: 1px solid #e9ecef;
        font-size: 14px;
    }

    .table th {
        background-color: #f1f4f8;
        color: #1a3c6d;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 12px;
    }

    .table tr:last-child td {
        border-bottom: none;
    }

    .table tr:hover {
        background-color: #f8f9fa;
    }

    .btn-primary {
        background-color: #007bff;
        color: white;
        padding: 8px 16px;
        border-radius: 6px;
        border: none;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .btn-primary:hover {
        background-color: #0056b3;
    }

    .btn-secondary {
        background-color: #6c757d;
        color: white;
        padding: 8px 16px;
        border-radius: 6px;
        border: none;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .btn-secondary:hover {
        background-color: #5a6268;
    }

    .filter-form {
        margin-bottom: 20px;
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
    }

    .filter-form select, .filter-form input {
        padding: 8px;
        border-radius: 6px;
        border: 1px solid #ced4da;
        font-size: 14px;
    }

    /* Pagination Styling: Rounded Pills */
    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 10px 0;
        gap: 5px;
    }

    .pagination .page-item {
        margin: 0 2px;
    }

    .pagination .page-link {
        padding: 8px 12px !important;
        font-size: 0.875rem !important;
        min-width: 40px !important;
        text-align: center;
        border-radius: 20px !important;
        border: 1px solid #dee2e6 !important;
        color: #6c757d !important;
        background: #fff !important;
        transition: all 0.3s ease !important;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .pagination .page-link:hover {
        background: #f8f9fa !important;
        transform: scale(1.05);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .pagination .page-item.active .page-link {
        background-color: #007bff !important;
        border-color: #007bff !important;
        color: #fff !important;
        transform: scale(1.05);
    }

    .pagination .page-item.disabled .page-link {
        color: #6c757d !important;
        background: #e9ecef !important;
        cursor: not-allowed !important;
        opacity: 0.65;
    }

    /* Custom Icons for Previous and Next */
    .pagination .page-item .page-link .bi {
        font-size: 1rem;
        margin: 0 2px;
    }

    .pagination .page-item.previous .page-link {
        padding-left: 8px !important;
    }

    .pagination .page-item.next .page-link {
        padding-right: 8px !important;
    }

    .empty-state {
        text-align: center;
        padding: 40px;
        color: #6c757d;
        font-size: 16px;
    }

    .empty-state i {
        font-size: 32px;
        display: block;
        margin-bottom: 15px;
        color: #adb5bd;
    }
</style>

<div class="container">
    <h2>Laporan Peminjaman</h2>

    <div class="card">
        <div class="filter-form">
            <form method="GET" action="{{ route('admin.reports.borrows') }}">
                <select name="status">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="assigned" {{ request('status') === 'assigned' ? 'selected' : '' }}>Assigned</option>
                    <option value="returned" {{ request('status') === 'returned' ? 'selected' : '' }}>Returned</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
                <input type="date" name="start_date" value="{{ request('start_date') }}">
                <input type="date" name="end_date" value="{{ request('end_date') }}">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="{{ route('admin.reports.borrows', array_merge(request()->query(), ['export' => 'excel'])) }}" class="btn btn-secondary">Export Excel</a>
                <a href="{{ route('admin.reports.borrows', array_merge(request()->query(), ['export' => 'pdf'])) }}" class="btn btn-secondary">Export PDF</a>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nama User</th>
                        <th>Nama Barang</th>
                        <th>Kode Barang</th>
                        <th>Tgl Pinjam</th>
                        <th>Tgl Kembali</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($borrows as $borrow)
                        <tr>
                            <td>{{ $borrow->user->name ?? '-' }}</td>
                            <td>{{ $borrow->barang->nama ?? '-' }}</td>
                            <td>
                                @forelse ($borrow->details as $detail)
                                    {{ $detail->unitBarang->kode_barang ?? '-' }}<br>
                                @empty
                                    -
                                @endforelse
                            </td>
                            <td>{{ $borrow->tanggal_pinjam ? \Carbon\Carbon::parse($borrow->tanggal_pinjam)->format('d-m-Y') : '-' }}</td>
                            <td>{{ $borrow->tanggal_kembali ? \Carbon\Carbon::parse($borrow->tanggal_kembali)->format('d-m-Y') : '-' }}</td>
                            <td>{{ ucfirst($borrow->status->value ?? $borrow->status) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-state">
                                <i class="bi bi-inbox"></i> Belum ada data peminjaman.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination">
            {{ $borrows->appends(request()->query())->links('vendor.pagination.custom') }}
        </div>
    </div>
</div>
@endsection