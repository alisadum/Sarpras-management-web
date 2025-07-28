@extends('layouts.app')

@section('title', 'Laporan Pengembalian')

@section('content')
<style>
    :root {
        --primary: #007bff;
        --secondary: #6c757d;
        --success: #28a745;
        --warning: #ffc107;
        --danger: #dc3545;
        --light: #f8f9fa;
        --dark: #343a40;
        --border-radius: 8px;
        --shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    body {
        font-family: 'Poppins', sans-serif;
        background: #f7fafc;
        color: var(--dark);
        line-height: 1.6;
        margin: 0;
    }

    .page-header {
        background: #fff;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        padding: 15px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .page-header .title {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--dark);
    }

    .table-container {
        background: #fff;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        padding: 15px;
    }

    .table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .table th {
        background: #f8f9fa;
        padding: 10px;
        text-align: left;
        font-weight: 600;
        font-size: 0.9rem;
        border-bottom: 2px solid #dee2e6;
    }

    .table td {
        padding: 10px;
        font-size: 0.875rem;
        vertical-align: middle;
        border-bottom: 1px solid #dee2e6;
    }

    .table td img {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: 4px;
    }

    .table .status {
        font-weight: 500;
    }

    .table .status.pending {
        color: #ffc107;
    }

    .table .status.completed {
        color: #28a745;
    }

    .table .status.rejected {
        color: #dc3545;
    }

    .table .actions button {
        background: none;
        border: none;
        cursor: pointer;
        padding: 5px;
        font-size: 0.875rem;
    }

    .table .actions .btn-view {
        color: #6c757d;
    }

    .pagination-pills {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        padding: 10px 0;
        gap: 5px;
    }

    .pagination-pills .page-link {
        padding: 8px 12px !important;
        font-size: 0.875rem !important;
        min-width: 40px !important;
        text-align: center;
        border-radius: 20px !important;
        border: 1px solid #dee2e6 !important;
        color: var(--dark) !important;
        background: #fff !important;
        transition: all 0.3s ease !important;
    }

    .pagination-pills .page-item {
        margin: 0 2px;
    }

    .pagination-pills .page-item.active .page-link {
        background-color: var(--primary) !important;
        border-color: var(--primary) !important;
        color: #fff !important;
        transform: scale(1.05);
    }

    .pagination-pills .page-item.disabled .page-link {
        color: #6c757d !important;
        background: #e9ecef !important;
        cursor: not-allowed !important;
        opacity: 0.65;
    }

    .pagination-pills .page-link:hover {
        background: #f8f9fa !important;
        transform: scale(1.05);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .alert {
        padding: 10px 15px;
        border-radius: var(--border-radius);
        margin-bottom: 15px;
        position: relative;
        font-size: 0.875rem;
        font-weight: 500;
        color: #fff;
        display: flex;
        align-items: center;
        gap: 5px;
        animation: slideIn 0.3s ease; /* Maintain slide animation */
    }

    .alert.success {
        background: var(--success);
    }

    .alert.error {
        background: var(--danger);
    }

    .alert-close {
        background: none;
        border: none;
        color: #fff;
        font-size: 1rem;
        cursor: pointer;
    }

    .modal-content {
        border-radius: var(--border-radius);
    }

    .modal-header {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--dark);
        border-bottom: 1px solid #dee2e6;
    }

    .modal-body {
        font-size: 0.875rem;
        color: #333;
    }

    @keyframes slideIn {
        from {
            transform: translateY(-10px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            gap: 10px;
        }
        .table th, .table td {
            font-size: 0.75rem;
            padding: 8px;
        }
        .pagination-pills {
            flex-direction: column;
            gap: 5px;
        }
        .pagination-pills .page-link {
            padding: 6px 10px !important;
            font-size: 0.75rem !important;
            min-width: 30px !important;
        }
    }
</style>

<div class="container">
    <div class="page-header">
        <div class="title">Laporan Pengembalian</div>
        <div class="user-info">
            <span>{{ Auth::user()->name ?? 'Admin' }}</span>
            <a href="{{ route('admin.profil') }}">
                <img src="{{ Auth::user()->photo ? asset('storage/' . Auth::user()->photo) : asset('storage/foto-profil/login.jpg') }}" class="rounded-circle" alt="Admin" style="width: 30px; height: 30px; object-fit: cover;">
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert success" role="alert">
            <i class="bi bi-check-circle" aria-label="Success"></i> {{ session('success') }}
            <button type="button" class="alert-close" aria-label="Close">✕</button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert error" role="alert">
            <i class="bi bi-exclamation-circle" aria-label="Error"></i> {{ session('error') }}
            <button type="button" class="alert-close" aria-label="Close">✕</button>
        </div>
    @endif
    @if ($errors->any())
        <div class="alert error" role="alarm">
            <i class="bi bi-exclamation-circle" aria-label="Error"></i>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="alert-close" aria-label="Close">✕</button>
        </div>
    @endif

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th></th>
                    <th>Image</th>
                    <th>Nama User</th>
                    <th>Nama Barang</th>
                    <th>Tgl Pinjam</th>
                    <th>Tgl Kembali</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($returnLogs as $log)
                    <tr>
                        <td></td>
                        <td>
                            @if ($log->borrow->barang->foto)
                                <img src="{{ asset('storage/' . $log->borrow->barang->foto) }}" alt="{{ $log->borrow->barang->nama }}" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                            @else
                                <img src="{{ asset('storage/default-image.jpg') }}" alt="Default" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                            @endif
                        </td>
                        <td>{{ $log->user->name ?? '-' }}</td>
                        <td>{{ $log->borrow->barang->nama ?? '-' }}</td>
                        <td>{{ $log->tanggal_pinjam ? \Carbon\Carbon::parse($log->tanggal_pinjam)->format('d-m-Y H:i') : '-' }}</td>
                        <td>{{ $log->tanggal_kembali ? \Carbon\Carbon::parse($log->tanggal_kembali)->format('d-m-Y H:i') : '-' }}</td>
                        <td>
                            <span class="status {{ $log->status->value }}">{{ ucfirst($log->status->value) }}</span>
                        </td>
                        <td class="actions">
                            <button type="button" class="btn-view" data-bs-toggle="modal" data-bs-target="#detailModal{{ $log->id }}" aria-label="View Details">
                                <i class="bi bi-eye"></i>
                            </button>
                            @if ($log->status->value === 'pending')
                                <form action="{{ route('admin.return.approveOrReject', $log->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="status" value="completed">
                                    <button type="submit" class="btn-create" aria-label="Approve Return">
                                        <i class="bi bi-check-circle"></i>
                                    </button>
                                </form>
                                <button type="button" class="btn-delete" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $log->id }}" aria-label="Reject Return">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            @endif
                        </td>
                    </tr>

                    <!-- Modal Detail -->
                    <div class="modal fade" id="detailModal{{ $log->id }}" tabindex="-1" aria-labelledby="detailModalLabel{{ $log->id }}" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="detailModalLabel{{ $log->id }}">Detail Pengembalian #{{ $log->id }}</h5>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close Modal">✕</button>
                                </div>
                                <div class="modal-body">
                                    <p><strong>User ID:</strong> {{ $log->user_id ?? '-' }}</p>
                                    <p><strong>Nama User:</strong> {{ $log->user->name ?? '-' }}</p>
                                    <p><strong>Nama Barang:</strong> {{ $log->borrow->barang->nama ?? '-' }}</p>
                                    <p><strong>Tanggal Pinjam:</strong> {{ $log->tanggal_pinjam ? \Carbon\Carbon::parse($log->tanggal_pinjam)->format('d-m-Y H:i') : '-' }}</p>
                                    <p><strong>Tanggal Kembali:</strong> {{ $log->tanggal_kembali ? \Carbon\Carbon::parse($log->tanggal_kembali)->format('d-m-Y H:i') : '-' }}</p>
                                    <p><strong>Terlambat:</strong>
                                        @if ($log->terlambat > 0)
                                            @if ($log->terlambat >= 1)
                                                {{ floor($log->terlambat) }} hari
                                            @else
                                                {{ floor($log->terlambat * 24) }} jam
                                            @endif
                                        @else
                                            Tidak
                                        @endif
                                    </p>
                                    <p><strong>Status:</strong> {{ ucfirst($log->status->value) }}</p>
                                    @if ($log->borrow)
                                        <p><strong>Status Peminjaman:</strong> {{ ucfirst($log->borrow->status) }}</p>
                                    @endif
                                    <p><strong>Detail Unit:</strong></p>
                                    @forelse ($log->detailReturns as $detail)
                                        <p>
                                            Unit: {{ $detail->unitBarang->kode_barang ?? '-' }}<br>
                                            Kerusakan: {{ $detail->kerusakan ?? 'Tidak ada' }}<br>
                                            @if ($detail->foto_kerusakan && \Storage::disk('public')->exists($detail->foto_kerusakan))
                                                <strong>Foto Kerusakan:</strong><br>
                                                <img src="{{ asset('storage/' . $detail->foto_kerusakan) }}" alt="Foto Kerusakan {{ $detail->unitBarang->kode_barang ?? 'Unit' }}" style="max-width: 100%; height: auto; margin-top: 10px; border: 1px solid #ddd; border-radius: var(--border-radius);">
                                            @else
                                                <strong>Foto Kerusakan:</strong> Tidak ada foto kerusakan.
                                            @endif
                                        </p>
                                    @empty
                                        <p>Tidak ada detail unit.</p>
                                    @endforelse
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close Modal">Tutup</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Reject -->
                    <div class="modal fade" id="rejectModal{{ $log->id }}" tabindex="-1" aria-labelledby="rejectModalLabel{{ $log->id }}" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="rejectModalLabel{{ $log->id }}">Tolak Pengembalian #{{ $log->id }}</h5>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close Modal">✕</button>
                                </div>
                                <form action="{{ route('admin.return.approveOrReject', $log->id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="status" value="rejected">
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label for="alasan{{ $log->id }}" class="form-label">Alasan Penolakan:</label>
                                            <textarea class="form-control @error('alasan') is-invalid @enderror" id="alasan{{ $log->id }}" name="alasan" rows="4" required>{{ old('alasan') }}</textarea>
                                            @error('alasan')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Cancel Reject">Batal</button>
                                        <button type="submit" class="btn btn-danger" aria-label="Confirm Reject">Tolak</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 20px; color: #6c757d;">
                            <i class="bi bi-inbox"></i> Belum ada pengembalian.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <nav aria-label="Page navigation">
            <ul class="pagination pagination-pills justify-content-end">
                <li class="page-item {{ $returnLogs->onFirstPage() ? 'disabled' : '' }}">
                    <a class="page-link" href="{{ $returnLogs->previousPageUrl() }}" tabindex="-1" aria-label="Previous">
                        <span aria-hidden="true">« Prev</span>
                    </a>
                </li>
                @for ($i = 1; $i <= $returnLogs->lastPage(); $i++)
                    <li class="page-item {{ $returnLogs->currentPage() == $i ? 'active' : '' }}">
                        <a class="page-link" href="{{ $returnLogs->url($i) }}">{{ $i }}</a>
                    </li>
                @endfor
                <li class="page-item {{ $returnLogs->hasMorePages() ? '' : 'disabled' }}">
                    <a class="page-link" href="{{ $returnLogs->nextPageUrl() }}" aria-label="Next">
                        <span aria-hidden="true">Next »</span>
                    </a>
                </li>
            </ul>
            <div class="text-muted mt-2">Showing {{ $returnLogs->firstItem() }} to {{ $returnLogs->lastItem() }} of {{ $returnLogs->total() }} entries</div>
        </nav>
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