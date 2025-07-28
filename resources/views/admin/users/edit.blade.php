<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .form-wrapper {
            background: #fff;
            padding: 25px;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            gap: 30px;
            width: 850px;
            max-width: 99%;
            box-sizing: border-box;
        }

        .photo-section {
            text-align: center;
            width: 200px;
        }

        .photo-section img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #ddd;
            margin-bottom: 10px;
        }

        .form-section {
            flex: 1;
        }

        .form-section h2 {
            font-size: 20px;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .form-section label {
            font-weight: 500;
            font-size: 14px;
            margin-top: 10px;
            display: block;
        }

        .form-section input,
        .form-section select {
            width: 88%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 7px;
            font-size: 14px;
            margin-bottom: 12px;
            background: white;
        }

        .btn-submit {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            background: linear-gradient(to right, #4facfe, #00f2fe);
            color: white;
            cursor: pointer;
            transition: 0.3s ease;
        }

        .btn-submit:hover {
            background: linear-gradient(to right, #00c6fb, #005bea);
        }

        .input-file {
            font-size: 12px;
            margin-top: 5px;
        }

        .alert-danger {
            color: red;
            margin-bottom: 15px;
        }

        @media (max-width: 600px) {
            .form-wrapper {
                flex-direction: column;
                align-items: center;
                width: 95%;
            }

            .photo-section {
                width: 100%;
            }

            .form-section input,
            .form-section select {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <form action="{{ route('admin.manajemen-user.update', $user->id) }}" method="POST" enctype="multipart/form-data" class="form-wrapper">
        @csrf
        @method('PUT')

        <div class="photo-section">
            <img id="preview" src="{{ $user->photo ? asset('storage/profiles/' . $user->photo) : asset('storage/profiles/default.jpg') }}" alt="Profile Preview">
            <div class="input-file">
                <input type="file" name="photo" accept="image/*" onchange="previewImage(event)">
            </div>
        </div>

        <div class="form-section">
            <h2>Edit User</h2>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <label for="name">Name</label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" required>

            <label for="email">Email</label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" required>

            <label for="password">Password (Biarkan kosong jika tidak diubah)</label>
            <input type="password" name="password">

            <label for="password_confirmation">Confirm Password</label>
            <input type="password" name="password_confirmation">

            <label for="role">Role</label>
            <select name="role" required>
                <option value="" disabled>-- Pilih Role --</option>
                <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="user" {{ old('role', $user->role) == 'user' ? 'selected' : '' }}>User</option>
            </select>

            <button type="submit" class="btn-submit">Update User</button>
        </div>
    </form>

    <script>
        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function() {
                document.getElementById('preview').src = reader.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>
</body>
</html>
