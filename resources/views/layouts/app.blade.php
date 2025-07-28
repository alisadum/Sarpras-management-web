<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard Admin')</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            margin: 0;
            background-color: #f5f7fa;
            font-family: 'Poppins', sans-serif;
        }

        .sidebar {
            width: 250px;
            height: 100vh;
            background: linear-gradient(to bottom, #4a90e2, #357ABD);
            position: fixed;
            top: 0;
            left: 0;
            color: #fff;
            display: flex;
            flex-direction: column;
            padding: 25px 15px;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .sidebar h4 {
            font-weight: 600;
            margin-bottom: 40px;
            text-align: center;
            font-size: 1.2rem;
        }

        .nav-link {
            color: #e0f0ff;
            padding: 12px 15px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-radius: 12px;
            font-size: 15px;
            transition: background 0.3s, color 0.3s;
            text-decoration: none;
            margin-bottom: 5px;
        }

        .nav-link.active,
        .nav-link:hover {
            background-color: #285eac;
            color: #ffffff;
        }

        .nav-link i {
            font-size: 1.2rem;
        }

        .dropdown-menu {
            background-color: #357ABD;
            border: none;
            border-radius: 8px;
            margin-left: 10px;
            padding: 0;
        }

        .dropdown-item {
            color: #e0f0ff;
            font-size: 14px;
            padding: 10px 20px;
            border-radius: 8px;
        }

        .dropdown-item:hover {
            background-color: #285eac;
            color: #ffffff;
        }

        .logout-btn {
            margin-top: auto;
            padding-top: 20px;
            width: 100%;
        }

        .logout-btn button {
            width: 100%;
            background: #dc3545;
            border: none;
            color: #fff;
            padding: 10px;
            border-radius: 12px;
            transition: background 0.3s;
        }

        .logout-btn button:hover {
            background: #c82333;
        }

        .content {
            margin-left: 250px;
            padding: 20px 15px;
            min-width: 0;
            transition: margin-left 0.3s;
        }

        .search-bar {
            margin-bottom: 20px;
            max-width: 400px;
        }

        .search-bar .form-control {
            border-radius: 12px;
            font-size: 0.9rem;
        }

        .search-bar .btn {
            border-radius: 12px;
            padding: 0.5rem 1rem;
        }

        .submenu {
            list-style: none;
            padding-left: 30px;
            margin: 0;
            display: none;
        }

        .submenu.active {
            display: block;
        }

        .submenu li a {
            display: block;
            padding: 10px 15px;
            font-size: 14px;
            color: #e0f0ff;
            text-decoration: none;
            border-radius: 8px;
            transition: background 0.3s, color 0.3s;
        }

        .submenu li a:hover,
        .submenu li a.active {
            background-color: #285eac;
            color: #ffffff;
        }

        @media (max-width: 600px) {
            .content {
                margin-left: 0;
                padding: 10px;
            }

            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s;
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .search-bar {
                max-width: 100%;
            }
        }
    </style>
    @yield('styles')
</head>
<body>
    @if (!isset($rendered))
        <?php $rendered = true; ?>
        <div class="sidebar">
            <h4> SARPRAS MANAGEMENT</h4>
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->is('admin/dashboard') ? 'active' : '' }}">
                <i class="bi bi-house-door-fill"></i> Dashboard
            </a>
            <div class="dropdown">
                <a href="#" class="nav-link {{ request()->is('kategori*', 'barang*', 'admin/manajemen-user*') ? 'active' : '' }}" data-bs-toggle="dropdown">
                    <i class="bi bi-database-fill"></i> Pendataan
                </a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ route('kategori.index') }}">Kategori Barang</a></li>
                    <li><a class="dropdown-item" href="{{ route('barang.index') }}">Data Barang</a></li>
                    <li><a class="dropdown-item" href="{{ route('admin.manajemen-user.index') }}">Manajemen User</a></li>
                </ul>
            </div>
            <div class="nav-link-group">
                <div class="nav-link {{ request()->is('admin/borrows*') ? 'active' : '' }}" onclick="toggleSubmenu('submenu-peminjaman')">
                    <i class="bi bi-arrow-left-right"></i> Peminjaman
                    <i class="bi bi-chevron-down ms-auto" id="arrow-icon-peminjaman"></i>
                </div>
                <ul class="submenu" id="submenu-peminjaman" style="{{ request()->is('admin/borrows*') ? 'display: block;' : 'display: none;' }}">
                    <li><a href="{{ route('admin.borrows.index') }}" class="{{ request()->is('admin/borrows') ? 'active' : '' }}">Daftar Peminjaman</a></li>
                    <li><a href="{{ route('admin.borrows.notifications') }}" class="{{ request()->is('admin/borrows/notifications') ? 'active' : '' }}">Notifikasi</a></li>
                </ul>
            </div>
            <a href="{{ route('admin.return.index') }}" class="nav-link {{ request()->is('admin/return') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-text-fill"></i> Pengembalian
            </a>
            <div class="dropdown">
                <a href="#" class="nav-link {{ request()->is('admin/reports*') ? 'active' : '' }}" data-bs-toggle="dropdown">
                    <i class="bi bi-bar-chart-fill"></i> Laporan
                </a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ route('admin.reports.borrows') }}">Peminjaman</a></li>
                    <li><a class="dropdown-item" href="{{ route('admin.reports.returns') }}">Pengembalian</a></li>
                </ul>
            </div>
            <a href="{{ route('admin.profil') }}" class="nav-link {{ request()->is('admin/profil') ? 'active' : '' }}">
                <i class="bi bi-person-circle"></i> Profil
            </a>
            <div class="logout-btn">
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="btn">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </button>
                </form>
            </div>
        </div>

        <div class="content">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (
                request()->is('admin/borrows*') ||
                request()->is('admin/return*') ||
                request()->is('admin/reports/borrows') ||
                request()->is('admin/reports/returns')
            )
                <div class="search-bar">
                    <form action="{{ url()->current() }}" method="GET" class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Cari berdasarkan nama user atau barang..." value="{{ request('search') }}">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i>
                        </button>
                    </form>
                </div>
            @endif

            @yield('content')
        </div>
    @endif

    <!-- jQuery & Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

    <script>
        function toggleSubmenu(id = 'submenu-peminjaman') {
            const submenu = document.getElementById(id);
            const arrow = document.getElementById(`arrow-icon-${id.split('-')[1]}`);
            if (submenu.style.display === 'block') {
                submenu.style.display = 'none';
                arrow.classList.remove('bi-chevron-up');
                arrow.classList.add('bi-chevron-down');
            } else {
                submenu.style.display = 'block';
                arrow.classList.remove('bi-chevron-down');
                arrow.classList.add('bi-chevron-up');
            }
        }

        const sidebar = document.querySelector('.sidebar');
        const content = document.querySelector('.content');
        if (window.innerWidth <= 600) {
            sidebar.style.transform = 'translateX(-100%)';
            content.style.marginLeft = '0';
        }
        window.addEventListener('resize', () => {
            if (window.innerWidth <= 600) {
                sidebar.style.transform = 'translateX(-100%)';
                content.style.marginLeft = '0';
            } else {
                sidebar.style.transform = 'translateX(0)';
                content.style.marginLeft = '250px';
            }
        });
    </script>
    @yield('scripts')
</body>
</html>
