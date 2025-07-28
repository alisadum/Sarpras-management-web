@extends('layouts.app')

@section('title', 'Notifikasi')

@section('content')
<style>
    .notifications-table {
        background: #fff;
        border-radius: 15px;
        padding: 15px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    .table {
        width: 100%;
        border-collapse: collapse;
    }

    .table th,
    .table td {
        padding: 12px;
        text-align: left;
        font-size: 0.9rem;
        color: #7f8c8d;
    }

    .table th {
        background: #f5f7fa;
        font-weight: 500;
    }

    .table td {
        border-bottom: 1px solid #ddd;
    }

    .table .badge {
        padding: 5px 10px;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: 500;
    }

    .badge-unread {
        background: #e74c3c;
        color: #fff;
    }

    .badge-read {
        background: #2ecc71;
        color: #fff;
    }

    .empty-state {
        text-align: center;
        padding: 30px;
        color: #7f8c8d;
        font-size: 1rem;
    }
</style>

<div class="notifications-table">
    <h3 class="mb-3" style="color: #2c3e50; font-weight: 600;">Notifikasi</h3>
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    <table class="table">
        <thead>
            <tr>
                <th>User</th>
                <th>Peminjaman</th>
                <th>Pesan</th>
                <th>Status</th>
                <th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($notifications as $notification)
                <tr>
                    <td>{{ $notification->user ? $notification->user->name : '-' }}</td>
                    <td>{{ $notification->borrow && $notification->borrow->barang ? $notification->borrow->barang->nama : '-' }}</td>
                    <td>{{ $notification->message }}</td>
                    <td><span class="badge {{ $notification->is_read ? 'badge-read' : 'badge-unread' }}">{{ $notification->is_read ? 'Dibaca' : 'Belum Dibaca' }}</span></td>
                    <td>{{ \Carbon\Carbon::parse($notification->tanggal_notif)->format('d-m-Y H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="empty-state">
                        <i class="bi bi-inbox"></i> Belum ada notifikasi.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection