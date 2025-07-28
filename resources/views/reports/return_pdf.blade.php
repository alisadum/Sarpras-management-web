<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pengembalian</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        h2 {
            text-align: center;
            color: #1a3c6d;
        }
        .badge-terlambat {
            color: #dc3545;
            font-weight: bold;
        }
        .badge-tidak-terlambat {
            color: #28a745;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h2>Laporan Pengembalian</h2>
    <table>
        <thead>
            <tr>
                <th>Nama User</th>
                <th>Nama Barang</th>
                <th>Kode Barang</th>
                <th>Tgl Pinjam</th>
                <th>Tgl Kembali</th>
                <th>Terlambat</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($returnLogs as $log)
                <tr>
                    <td>{{ $log->user->name ?? '-' }}</td>
                    <td>{{ $log->nama_barang ?? '-' }}</td>
                    <td>
                        @if ($log->detailReturns->isNotEmpty())
                            {{ $log->detailReturns->pluck('unitBarang.kode_barang')->filter()->implode(', ') }}
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $log->tanggal_pinjam ? \Carbon\Carbon::parse($log->tanggal_pinjam)->format('d-m-Y') : '-' }}</td>
                    <td>{{ $log->tanggal_kembali ? \Carbon\Carbon::parse($log->tanggal_kembali)->format('d-m-Y') : '-' }}</td>
                    <td>
                        <span class="{{ $log->terlambat > 0 ? 'badge-terlambat' : 'badge-tidak-terlambat' }}">
                            {{ $log->terlambat > 0 ? 'Terlambat ' . $log->terlambat . ' hari' : 'Tidak Terlambat' }}
                        </span>
                    </td>
                    <td>{{ ucfirst($log->status->value) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
