@extends('layouts.app')

@section('content')
<style>
    .container {
        max-width: 1000px;
        margin: 0 auto;
        background: #fff;
        padding: 24px;
        border-radius: 12px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    .section-title {
        font-size: 20px;
        font-weight: bold;
        color: #333;
        margin-bottom: 20px;
    }

    .detail-box {
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 20px;
    }

    .detail-text {
        flex: 1;
        min-width: 250px;
        color: #444;
    }

    .detail-text p {
        margin: 8px 0;
    }

    .detail-image {
        flex-shrink: 0;
        width: 100px;
        height: 100px;
        border: 1px solid #ccc;
        border-radius: 8px;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f9f9f9;
    }

    .detail-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
        color: #333;
        margin-top: 10px;
    }

    th, td {
        padding: 10px;
        border: 1px solid #ddd;
        text-align: left;
    }

    th {
        background: #f1f1f1;
        font-weight: bold;
        text-transform: uppercase;
        font-size: 12px;
    }

    .actions {
        text-align: center;
        display: flex;
        justify-content: center;
        gap: 8px;
    }

    .actions a {
        text-decoration: none;
        border: none;
        background: none;
        cursor: pointer;
        font-size: 16px;
        padding: 4px 8px;
        color: #007bff;
        transition: color 0.2s;
    }

    .actions a:hover {
        color: #0056b3;
    }

    .btn-add, .btn-back {
        display: inline-block;
        padding: 8px 16px;
        text-decoration: none;
        border-radius: 8px;
        margin-bottom: 20px;
        transition: background-color 0.2s, box-shadow 0.3s, transform 0.3s;
    }

    .btn-add {
        background-color: #4C51BF;
        color: white;
    }

    .btn-add:hover {
        background-color: #3b3f9b;
        box-shadow: 0 4px 12px rgba(76, 81, 191, 0.3);
        transform: translateY(-2px);
    }

    .btn-back {
        background-color: #6B7280;
        color: white;
        margin-right: 10px;
    }

    .btn-back:hover {
        background-color: #5a6268;
        box-shadow: 0 4px 12px rgba(107, 114, 128, 0.3);
        transform: translateY(-2px);
    }
</style>

<div class="container">
    {{-- DETAIL BARANG INDUK --}}
    <div>
        <h2 class="section-title">📦 Detail Barang Induk</h2>
        <div class="detail-box">
            <div class="detail-text">
                <p><strong>Nama:</strong> {{ $barang->nama }}</p>
                <p><strong>Tipe:</strong> {{ $barang->tipe }}</p>
                <p><strong>Kategori:</strong> {{ $barang->kategori->nama_kategori ?? '-' }}</p>
                <p><strong>Stok Total:</strong> {{ $barang->stok }}</p>
            </div>
            <div class="detail-image">
                @if ($barang->foto)
                    <img src="{{ asset('storage/' . $barang->foto) }}" alt="Foto Barang Induk">
                @else
                    <span style="color:#aaa; font-size: 12px;">Tidak ada foto</span>
                @endif
            </div>
        </div>
    </div>

    {{-- TABEL UNIT BARANG --}}
    <div style="margin-top: 40px;">
        <h2 class="section-title">🧾 Daftar Unit Barang</h2>
        <div style="margin-bottom: 20px;">
            <a href="{{ route('admin.barang.index') }}" class="btn-back">← Kembali ke Daftar Barang</a>
            <a href="{{ route('admin.barang.units.create', $barang->id) }}" class="btn-add">+ Tambah Unit Barang</a>
        </div>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Foto</th>
                    <th>Kode</th>
                    <th>Kondisi</th>
                    <th>Status</th>
                    <th>Lokasi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($barang->unitBarangs as $unit)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            @if ($unit->foto)
                                <img src="{{ asset('storage/' . $unit->foto) }}" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;" alt="Foto Unit">
                            @else
                                @if ($barang->foto)
                                    <img src="{{ asset('storage/' . $barang->foto) }}" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;" alt="Foto Barang Induk">
                                @else
                                    <span style="color:#999; font-style: italic;">-</span>
                                @endif
                            @endif
                        </td>
                        <td>{{ $unit->kode_barang }}</td>
                        <td>{{ $unit->kondisi }}</td>
                        <td>{{ $unit->status }}</td>
                        <td>
                            @if($unit->status === 'Dipinjam' && $unit->detailBorrows->isNotEmpty())
                                {{ $unit->detailBorrows->first()->borrow->lokasi ?? $unit->lokasi }}
                            @else
                                {{ $unit->lokasi }}
                            @endif
                        </td>
                        <td class="actions">
                            <a href="{{ route('admin.barang.units.edit', ['barang' => $barang->id, 'unit' => $unit->id]) }}" class="edit" title="Edit Unit">✏️</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; color: #999; font-style: italic;">
                            Belum ada unit barang.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection