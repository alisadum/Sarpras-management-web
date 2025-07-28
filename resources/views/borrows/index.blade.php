@extends('layouts.app')

@section('content')
<div class="container">
    <div class="page-header">
        <div class="title">Peminjaman Sarana & Prasarana</div>
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
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($borrows as $borrow)
                    @php
                        $isExpired = ($borrow->status == 'assigned') && \Carbon\Carbon::parse($borrow->tanggal_kembali)->isPast();
                        $displayStatus = $isExpired ? 'expired' : $borrow->status;
                    @endphp
                    <tr>
                        <td></td>
                        <td>
                            @if ($borrow->barang->foto)
                                <img src="{{ asset('storage/' . $borrow->barang->foto) }}" alt="{{ $borrow->barang->nama }}" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                            @else
                                <img src="{{ asset('storage/default-image.jpg') }}" alt="Default" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                            @endif
                        </td>
                        <td>{{ $borrow->user->name ?? '-' }}</td>
                        <td>{{ $borrow->barang->nama ?? '-' }}</td>
                        <td>{{ \Carbon\Carbon::parse($borrow->tanggal_pinjam)->format('d-m-Y H:i') }}</td>
                        <td>{{ \Carbon\Carbon::parse($borrow->tanggal_kembali)->format('d-m-Y H:i') }}</td>
                        <td>
                            <span class="status {{ $displayStatus }}">{{ ucfirst($displayStatus) }}</span>
                        </td>
                        <td class="actions">
                            <button type="button" class="btn-view" data-bs-toggle="modal" data-bs-target="#detailModal{{ $borrow->id }}" aria-label="View Details">
                                <i class="bi bi-eye"></i>
                            </button>
                            @if ($borrow->status === 'pending')
                                <a href="{{ route('admin.borrows.assign', $borrow->id) }}" class="btn-create" aria-label="Assign Unit">
                                    <i class="bi bi-check-circle"></i>
                                </a>
                                <a href="{{ route('admin.borrows.edit', $borrow->id) }}" class="btn-edit" aria-label="Edit Borrow">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" class="btn-delete" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $borrow->id }}" aria-label="Reject Borrow">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            @endif
                        </td>
                    </tr>

                    <!-- Modal Detail -->
                    <div class="modal fade" id="detailModal{{ $borrow->id }}" tabindex="-1" aria-labelledby="detailModalLabel{{ $borrow->id }}" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="detailModalLabel{{ $borrow->id }}">Detail Peminjaman #{{ $borrow->id }}</h5>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close Modal">✕</button>
                                </div>
                                <div class="modal-body">
                                    @php
                                        $details = \App\Models\DetailBorrow::where('borrow_id', $borrow->id)->with(['unitBarang'])->get();
                                        $returnLogs = \App\Models\ReturnLog::where('borrow_id', $borrow->id)->with('unitBarang')->get();
                                    @endphp
                                    <p><strong>ID Peminjaman:</strong> {{ $borrow->id }}</p>
                                    <p><strong>User ID:</strong> {{ $borrow->user_id }}</p>
                                    <p><strong>Nama User:</strong> {{ $borrow->user->name ?? '-' }}</p>
                                    <p><strong>Barang ID:</strong> {{ $borrow->barang_id }}</p>
                                    <p><strong>Nama Barang:</strong> {{ $borrow->barang->nama ?? '-' }}</p>
                                    <p><strong>Kode Barang:</strong>
                                        @if ($details->isNotEmpty())
                                            {{ implode(', ', $details->pluck('unitBarang.kode_barang')->filter()->toArray()) }}
                                        @elseif ($returnLogs->isNotEmpty())
                                            {{ implode(', ', $returnLogs->pluck('unitBarang.kode_barang')->filter()->toArray()) }}
                                        @else
                                            -
                                        @endif
                                    </p>
                                    <p><strong>Tanggal Pinjam:</strong> {{ \Carbon\Carbon::parse($borrow->tanggal_pinjam)->format('d-m-Y H:i') }}</p>
                                    <p><strong>Tanggal Kembali:</strong> {{ \Carbon\Carbon::parse($borrow->tanggal_kembali)->format('d-m-Y H:i') }}</p>
                                    <p><strong>Lokasi:</strong> {{ $borrow->lokasi ?? '-' }}</p>
                                    <p><strong>Deskripsi:</strong> {{ $borrow->deskripsi ?? '-' }}</p>
                                    <p><strong>Status:</strong> {{ ucfirst($displayStatus) }}</p>
                                    @if ($borrow->alasan_reject)
                                        <p class="text-danger"><strong>Alasan Ditolak:</strong> {{ $borrow->alasan_reject }}</p>
                                    @endif
                                    <p><strong>Status Pengembalian:</strong>
                                        @if ($returnLogs->isNotEmpty())
                                            <ul>
                                                @foreach ($returnLogs as $log)
                                                    <li>
                                                        {{ $log->unitBarang->kode_barang ?? '-' }}:
                                                        {{ $log->terlambat > 0 ? 'Terlambat (' . $log->terlambat . ' hari)' : 'Tidak Terlambat' }}
                                                        (Tgl Kembali: {{ \Carbon\Carbon::parse($log->tanggal_kembali)->format('d-m-Y H:i') }})
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <p>Belum ada pengembalian.</p>
                                        @endif
                                    </p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close Modal">Tutup</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Reject -->
                    <div class="modal fade" id="rejectModal{{ $borrow->id }}" tabindex="-1" aria-labelledby="rejectModalLabel{{ $borrow->id }}" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="rejectModalLabel{{ $borrow->id }}">Tolak Peminjaman #{{ $borrow->id }}</h5>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close Modal">✕</button>
                                </div>
                                <form action="{{ route('admin.borrows.reject', $borrow->id) }}" method="POST">
                                    @csrf
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label for="alasan{{ $borrow->id }}" class="form-label">Alasan Penolakan:</label>
                                            <textarea class="form-control" id="alasan{{ $borrow->id }}" name="alasan" rows="4" required></textarea>
                                            @error('alasan')
                                                <span class="text-danger">{{ $message }}</span>
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
                            <i class="bi bi-inbox"></i> Belum ada peminjaman.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <nav aria-label="Page navigation">
            <ul class="pagination pagination-pills justify-content-end">
                <li class="page-item {{ $borrows->onFirstPage() ? 'disabled' : '' }}">
                    <a class="page-link" href="{{ $borrows->previousPageUrl() }}" tabindex="-1" aria-label="Previous">
                        <span aria-hidden="true">« Prev</span>
                    </a>
                </li>
                @for ($i = 1; $i <= $borrows->lastPage(); $i++)
                    <li class="page-item {{ $borrows->currentPage() == $i ? 'active' : '' }}">
                        <a class="page-link" href="{{ $borrows->url($i) }}">{{ $i }}</a>
                    </li>
                @endfor
                <li class="page-item {{ $borrows->hasMorePages() ? '' : 'disabled' }}">
                    <a class="page-link" href="{{ $borrows->nextPageUrl() }}" aria-label="Next">
                        <span aria-hidden="true">Next »</span>
                    </a>
                </li>
            </ul>
            <div class="text-muted mt-2">Showing {{ $borrows->firstItem() }} to {{ $borrows->lastItem() }} of {{ $borrows->total() }} entries</div>
        </nav>
    </div>
</div>

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

    .page-header .actions {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .page-header .btn {
        padding: 6px 12px;
        font-size: 0.875rem;
        border: none;
        border-radius: var(--border-radius);
        cursor: pointer;
        transition: background 0.2s;
    }

    .page-header .btn-add {
        background: #28a745;
        color: #fff;
    }

    .page-header .btn-add:hover {
        background: #218838;
    }

    .page-header .btn-delete {
        background: #dc3545;
        color: #fff;
    }

    .page-header .btn-delete:hover {
        background: #c82333;
    }

    .page-header .btn-action {
        background: #6c757d;
        color: #fff;
    }

    .page-header .btn-action:hover {
        background: #5a6268;
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

    .table .status.expired {
        color: #dc3545;
    }

    .table .status.returned {
        color: #28a745;
    }

    .table .actions button {
        background: none;
        border: none;
        cursor: pointer;
        padding: 5px;
        font-size: 0.875rem;
    }

    .table .actions .btn-create {
        color: #28a745;
    }

    .table .actions .btn-edit {
        color: #007bff;
    }

    .table .actions .btn-delete {
        color: #dc3545;
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
