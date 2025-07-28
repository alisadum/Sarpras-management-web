@extends('layouts.app')

@section('title', 'Profil Admin')

@section('content')
<div class="container" style="max-width: 700px;">
    <div class="card shadow-sm p-4">
        <h4 class="mb-4">👤 Profil Admin</h4>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(!$user)
            <div class="alert alert-danger">User tidak ditemukan. Silakan login ulang.</div>
        @else
            @php
                $isPublic = $user->photo && Str::startsWith($user->photo, 'images/');
                $photoUrl = $user->photo
                    ? ($isPublic ? asset($user->photo) : asset('storage/' . $user->photo))
                    : asset('storage/foto-profil/login.jpg');
            @endphp

            {{-- FOTO PROFIL --}}
            <div class="text-center mb-4">
                <img src="{{ $photoUrl }}"
                     class="rounded-circle shadow"
                     style="width: 120px; height: 120px; object-fit: cover;"
                     alt="Foto Profil">
            </div>

            <form method="POST" action="{{ route('admin.profil.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label fw-semibold">Nama</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}"
                           class="form-control @error('name') is-invalid @enderror">
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}"
                           class="form-control @error('email') is-invalid @enderror">
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Foto Profil</label>
                    <input type="file" name="photo"
                           class="form-control @error('photo') is-invalid @enderror">
                    @error('photo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Simpan Perubahan
                </button>
            </form>
        @endif
    </div>
</div>
@endsection
