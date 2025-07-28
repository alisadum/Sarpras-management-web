@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
<style>
    body {
        background: #f8fafc !important;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    }

    .dashboard-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 24px;
    }

    .page-header {
        margin-bottom: 32px;
    }

    .page-title {
        font-size: 2rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 8px;
    }

    .page-subtitle {
        color: #64748b;
        font-size: 1.1rem;
    }

    /* Stats Cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 24px;
        margin-bottom: 32px;
    }

    .stat-card {
        background: #fff;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border: 1px solid #e2e8f0;
        transition: all 0.2s ease;
        position: relative;
        overflow: hidden;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--accent-color);
    }

    .stat-card.users::before { --accent-color: #3b82f6; }
    .stat-card.borrowed::before { --accent-color: #f59e0b; }
    .stat-card.total::before { --accent-color: #10b981; }
    .stat-card.available::before { --accent-color: #8b5cf6; }

    .stat-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
    }

    .stat-title {
        font-size: 0.95rem;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        opacity: 0.9;
    }

    .stat-icon.users { background: #dbeafe; color: #3b82f6; }
    .stat-icon.borrowed { background: #fef3c7; color: #f59e0b; }
    .stat-icon.total { background: #d1fae5; color: #10b981; }
    .stat-icon.available { background: #e9d5ff; color: #8b5cf6; }

    .stat-value {
        font-size: 2.5rem;
        font-weight: 800;
        color: #1e293b;
        margin-bottom: 8px;
        line-height: 1;
    }

    .stat-description {
        color: #64748b;
        font-size: 0.9rem;
    }

    /* Content Grid */
    .content-grid {
        display: grid;
        grid-template-columns: 1fr 400px;
        gap: 32px;
    }

    /* History Section */
    .history-card {
        background: #fff;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border: 1px solid #e2e8f0;
    }

    .card-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .history-item {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 16px 0;
        border-bottom: 1px solid #f1f5f9;
        transition: all 0.2s ease;
    }

    .history-item:last-child {
        border-bottom: none;
    }

    .history-item:hover {
        background: #f8fafc;
        margin: 0 -16px;
        padding: 16px;
        border-radius: 8px;
    }

    .history-avatar {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-weight: 600;
        font-size: 0.9rem;
        flex-shrink: 0;
    }

    .history-content {
        flex: 1;
        min-width: 0;
    }

    .history-title {
        font-size: 0.95rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 4px;
    }

    .history-subtitle {
        font-size: 0.85rem;
        color: #64748b;
    }

    .history-time {
        font-size: 0.8rem;
        color: #94a3b8;
        font-weight: 500;
    }

    /* Notifications Section */
    .notifications-section {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    .notification-alert {
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        color: #fff;
        border-radius: 16px;
        padding: 24px;
        position: relative;
        overflow: hidden;
    }

    .notification-alert::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
    }

    .alert-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 12px;
    }

    .alert-avatar {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }

    .alert-title {
        font-size: 1.1rem;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .alert-subtitle {
        font-size: 0.9rem;
        opacity: 0.9;
    }

    .notifications-card {
        background: #fff;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border: 1px solid #e2e8f0;
    }

    .notification-item {
        display: flex;
        align-items: flex-start;
        gap: 16px;
        padding: 16px 0;
        border-bottom: 1px solid #f1f5f9;
        transition: all 0.2s ease;
    }

    .notification-item:last-child {
        border-bottom: none;
    }

    .notification-item:hover {
        background: #f8fafc;
        margin: 0 -16px;
        padding: 16px;
        border-radius: 8px;
    }

    .notification-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .notification-icon.info { color: #3b82f6; background: #dbeafe; }
    .notification-icon.success { color: #10b981; background: #d1fae5; }
    .notification-icon.warning { color: #f59e0b; background: #fef3c7; }
    .notification-icon.error { color: #ef4444; background: #fee2e2; }

    .notification-content {
        flex: 1;
        min-width: 0;
    }

    .notification-title {
        font-size: 0.95rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 4px;
    }

    .notification-message {
        font-size: 0.85rem;
        color: #64748b;
        margin-bottom: 6px;
        line-height: 1.4;
    }

    .notification-time {
        font-size: 0.8rem;
        color: #94a3b8;
        font-weight: 500;
    }

    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #94a3b8;
    }

    .empty-state i {
        font-size: 3rem;
        margin-bottom: 16px;
        opacity: 0.5;
    }

    .empty-state p {
        font-size: 1rem;
        margin: 0;
    }

    .view-all-btn {
        background: #f8fafc;
        color: #4f46e5;
        border: none;
        padding: 10px 16px;
        border-radius: 8px;
        font-size: 0.9rem;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease;
        margin-top: 16px;
    }

    .view-all-btn:hover {
        background: #e2e8f0;
        color: #4338ca;
        text-decoration: none;
    }

    /* Responsive Design */
    @media (max-width: 1200px) {
        .content-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .dashboard-container {
            padding: 16px;
        }

        .stats-grid {
            grid-template-columns: 1fr;
            gap: 16px;
        }

        .page-title {
            font-size: 1.75rem;
        }

        .stat-value {
            font-size: 2rem;
        }
    }
</style>

<div class="dashboard-container">
    <!-- Page Header -->
    <div class="page-header">
        <h1 class="page-title">Dashboard Admin</h1>
        <p class="page-subtitle">Selamat datang kembali, {{ auth()->guard('admin')->user()->name ?? 'Admin' }}! Monitor dan kelola sistem inventaris Anda dengan mudah.</p>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card users">
            <div class="stat-header">
                <div class="stat-title">Total Pengguna</div>
                <div class="stat-icon users">
                    <i class="bi bi-people-fill"></i>
                </div>
            </div>
            <div class="stat-value">{{ \App\Models\User::count() }}</div>
            <div class="stat-description">Pengguna terdaftar</div>
        </div>

        <div class="stat-card borrowed">
            <div class="stat-header">
                <div class="stat-title">Barang Dipinjam</div>
                <div class="stat-icon borrowed">
                    <i class="bi bi-box-arrow-right"></i>
                </div>
            </div>
            <div class="stat-value">{{ \App\Models\Borrow::whereIn('status', ['pending', 'assigned'])->count() }}</div>
            <div class="stat-description">Sedang dipinjam</div>
        </div>

        <div class="stat-card total">
            <div class="stat-header">
                <div class="stat-title">Total Barang</div>
                <div class="stat-icon total">
                    <i class="bi bi-boxes"></i>
                </div>
            </div>
            <div class="stat-value">{{ \App\Models\Barang::sum('stok') }}</div>
            <div class="stat-description">Unit inventaris</div>
        </div>

        <div class="stat-card available">
            <div class="stat-header">
                <div class="stat-title">Barang Tersedia</div>
                <div class="stat-icon available">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
            </div>
            <div class="stat-value">{{ \App\Models\UnitBarang::where('status', 'Tersedia')->count() }}</div>
            <div class="stat-description">Siap dipinjam</div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="content-grid">
        <!-- Recent Borrowing History -->
        <div class="history-card">
            <h3 class="card-title">
                <i class="bi bi-clock-history"></i>
                Riwayat Peminjaman Terbaru
            </h3>

            @php
                $recentBorrows = \App\Models\Borrow::with(['user', 'barang'])
                    ->whereIn('status', ['pending', 'assigned'])
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get();
            @endphp

            @if($recentBorrows->count() > 0)
                @foreach($recentBorrows as $borrow)
                    <div class="history-item">
                        <div class="history-avatar">
                            {{ substr($borrow->user->name ?? 'U', 0, 2) }}
                        </div>
                        <div class="history-content">
                            <div class="history-title">{{ $borrow->user->name ?? 'Unknown User' }}</div>
                            <div class="history-subtitle">meminjam {{ $borrow->barang->nama ?? 'Unknown Item' }} ({{ $borrow->jumlah_unit }} unit)</div>
                        </div>
                        <div class="history-time">
                            {{ \Carbon\Carbon::parse($borrow->created_at)->diffForHumans() }}
                        </div>
                    </div>
                @endforeach

                <a href="{{ route('admin.borrows.index') }}" class="view-all-btn">
                    Lihat Semua Riwayat
                    <i class="bi bi-arrow-right"></i>
                </a>
            @else
                <div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    <p>Belum ada riwayat peminjaman</p>
                </div>
            @endif
        </div>

        <!-- Notifications Section -->
        <div class="notifications-section">
            <!-- Notification Alert -->
            <div class="notification-alert">
                <div class="alert-header">
                    <div class="alert-avatar">
                        <i class="bi bi-bell-fill"></i>
                    </div>
                    <div>
                        <div class="alert-title">Notifikasi Aktif</div>
                        <div class="alert-subtitle">
                            {{ \App\Models\Notification::where('user_id', auth()->guard('admin')->user()->id)->where('is_read', false)->count() }} notifikasi belum dibaca
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Notifications -->
            <div class="notifications-card">
                <h3 class="card-title">
                    <i class="bi bi-bell"></i>
                    Notifikasi Terbaru
                </h3>

                @php
                    $recentNotifications = \App\Models\Notification::with(['user', 'borrow.barang'])
                        ->where('user_id', auth()->guard('admin')->user()->id)
                        ->orderBy('tanggal_notif', 'desc')
                        ->limit(5)
                        ->get();
                @endphp

                @if($recentNotifications->count() > 0)
                    @foreach($recentNotifications as $notification)
                        <div class="notification-item">
                            <div class="notification-icon {{ $notification->type }}">
                                @switch($notification->type)
                                    @case('warning')
                                        <i class="bi bi-exclamation-triangle-fill"></i>
                                        @break
                                    @case('success')
                                        <i class="bi bi-check-circle-fill"></i>
                                        @break
                                    @case('error')
                                        <i class="bi bi-x-circle-fill"></i>
                                        @break
                                    @default
                                        <i class="bi bi-info-circle-fill"></i>
                                @endswitch
                            </div>
                            <div class="notification-content">
                                <div class="notification-title">{{ $notification->title ?? 'Notifikasi' }}</div>
                                <div class="notification-message">{{ \Illuminate\Support\Str::limit($notification->message ?? '', 80) }}</div>
                                <div class="notification-time">{{ \Carbon\Carbon::parse($notification->tanggal_notif)->diffForHumans() }}</div>
                            </div>
                        </div>
                    @endforeach

                    <a href="{{ route('admin.borrows.notifications') }}" class="view-all-btn">
                        Lihat Semua Notifikasi
                        <i class="bi bi-arrow-right"></i>
                    </a>
                @else
                    <div class="empty-state">
                        <i class="bi bi-bell-slash"></i>
                        <p>Belum ada notifikasi</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- Bootstrap Icons CDN -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
@endsection