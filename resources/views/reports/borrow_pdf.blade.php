<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Peminjaman</title>
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
    </style>
</head>
<body>
    <h2>Laporan Peminjaman</h2>
    <table>
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
            @foreach ($borrows as $borrow)
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
            @endforeach
        </tbody>
    </table>
</body>
</html>
