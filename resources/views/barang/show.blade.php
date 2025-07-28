@extends('layouts.app')

@section('title', 'Detail Barang')
@section('header', 'Detail Barang')

@section('content')
<style>
    .card {
        background: #fff;
        padding: 20px;
        border-radius: 6px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
        max-width: 800px;
        margin: auto;
    }

    .card h2 {
        font-size: 24px;
        margin-bottom: 10px;
    }

    .card p {
        color: #444;
        margin-bottom: 5px;
    }

    .table-container {
        margin-top: 30px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }

    th, td {
        padding: 10px;
        border: 1px solid #ccc;
        text-align: left;
        font-size: 14px;
    }

    th {
        background-color: #f0f0f0;
    }

    .no-data {
        margin-top: 10px;
        color: #888;
    }
</style>

<div class="card">
    <h2>{{ $barang->nama }}</h2>
{{ $barang->kategori->nama_kategori ?? '-' }}
    <p><strong>Stok:</strong> {{ $barang->stok }}</p>

    <div class="table-container">
        <h3>Unit Barang</h3>

        @if($barang->unitBarangs->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>Kode Unit</th>
                        <th>Kondisi</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($barang->unitBarangs as $unit)
                        <tr>
                            <td>{{ $unit->kode }}</td>
                            <td>{{ $unit->kondisi }}</td>
                            <td>{{ $unit->status }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="no-data">Tidak ada unit untuk barang ini.</p>
        @endif
    </div>
</div>
@endsection
