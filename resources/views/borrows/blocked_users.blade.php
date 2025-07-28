<!-- resources/views/borrows/blocked_users.blade.php -->
@extends('layouts.app')

@section('title', 'User Diblokir')

@section('content')
<style>
    :root {
        --primary: #4a90e2;
        --success: #28a745;
        --danger: #dc3545;
        --text: #2d3748;
        --background: #f7fafc;
        --card-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
        --border-radius: 12px;
        --transition: all 0.3s ease;
    }

    .container {
        max-width: 1280px;
        margin: 2rem auto;
        padding: 0 1rem;
    }

    h2 {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 1.5rem;
        color: var(--text);
    }

    .alert {
        padding: 1rem 1.5rem;
        border-radius: var(--border-radius);
        margin-bottom: 1.5rem;
        font-size: 0.9rem;
        font-weight: 500;
        color: #fff;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        animation: slideIn 0.3s ease;
    }

    .alert.success {
        background: var(--success);
    }

    .alert.error {
        background: var(--danger);
    }

    .alert-close {
        position: absolute;
        top: 50%;
        right: 1rem;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #fff;
        font-size: 1rem;
        cursor: pointer;
        transition: var(--transition);
    }

    .alert-close:hover {
        opacity: 0.7;
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

    .btn-success {
        background: var(--success);
        color: #fff;
    }

    .btn-success:hover {
        background: #218838;
        transform: translateY(-2px);
    }

    .card {
        background: #fff;
        border-radius: var(--border-radius);
        box-shadow: var(--card-shadow);
        overflow: hidden;
        margin-top: 1.5rem;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
    }

    .table th,
    .table td {
        padding: 1rem;
        text-align: left;
        border-bottom: 1px solid #e2e8f0;
        font-size: 0.9rem;
    }

    .table th {
        background: #f8fafc;
        font-weight: 600;
        color: var(--text);
        text-transform: uppercase;
        font-size: 0.85rem;
    }

    .table tr:hover {
        background: #edf2f7;
    }

    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .empty-state {
        text-align: center;
        padding: 3rem;
        color: #718096;
        font-size: 1rem;
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
        .container {
            padding: 0 0.5rem;
        }
        .table th,
        .table td {
            padding: 0.75rem;
            font-size: 0.85rem;
        }
        .btn {
            padding: 0.6rem 1rem;
            font-size: 0.85rem;
        }
    }
</style>

<div class="container">
    <h2>User Diblokir</h2>

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
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Email</th>
                        <th>Alasan Blokir</th>
                        <th>Tanggal Blokir</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($blockedUsers as $user)
                        <tr>
                            <td>{{ $user->user->email ?? 'Email tidak tersedia' }}</td>
                            <td>{{ $user->reason }}</td>
                            <td>{{ \Carbon\Carbon::parse($user->tanggal_block)->format('d-m-Y H:i') }}</td>
                            <td>
                                <a href="{{ route('admin.borrows.unblock', $user->user_id) }}" class="btn btn-success btn-sm" onclick="return confirm('Unblock user ini?')">
                                    <i class="bi bi-unlock"></i> Unblock
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="empty-state">
                                <i class="bi bi-inbox"></i> Belum ada user diblokir.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
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