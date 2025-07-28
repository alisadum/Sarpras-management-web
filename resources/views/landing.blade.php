<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Blast | Admin Portal</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    html {
      scroll-behavior: smooth;
    }

    body {
      font-family: 'Inter', sans-serif;
      background: linear-gradient(135deg, #f0f6ff, #bfdbfe);
      color: #1e3a8a;
      line-height: 1.6;
    }

    h2, h3, h4 {
      font-family: 'Playfair Display', serif;
      color: #1e3a8a;
    }

    header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 20px 80px;
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(12px);
      position: sticky;
      top: 0;
      z-index: 100;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .logo h2 {
      font-size: 1.8rem;
      color: #60a5fa;
    }

    header nav {
      display: flex;
      gap: 25px;
    }

    header nav a {
      text-decoration: none;
      color: #1e3a8a;
      font-weight: 600;
      font-size: 0.95rem;
      position: relative;
      transition: color 0.3s ease;
    }

    header nav a::after {
      content: '';
      position: absolute;
      left: 0;
      bottom: -6px;
      width: 0;
      height: 2px;
      background: #60a5fa;
      transition: width 0.3s ease;
    }

    header nav a:hover::after {
      width: 100%;
    }

    header nav a:hover {
      color: #60a5fa;
    }

    .btn-login {
      background: linear-gradient(45deg, #60a5fa, #93c5fd);
      color: white;
      padding: 10px 24px;
      border-radius: 50px;
      text-decoration: none;
      font-weight: 600;
      font-size: 0.95rem;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .btn-login:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(96, 165, 250, 0.3);
    }

    .hero {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 80px 80px;
      min-height: 85vh;
      background: url('data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"%3E%3Cpath fill="%2360a5fa" fill-opacity="0.1" d="M0,192L80,186.7C160,181,320,171,480,181.3C640,192,800,224,960,213.3C1120,203,1280,149,1360,122.7L1440,96L1440,320L1360,320C1280,320,1120,320,960,320C800,320,640,320,480,320C320,320,160,320,80,320L0,320Z"%3E%3C/path%3E%3C/svg%3E') no-repeat bottom;
      background-size: cover;
    }

    .hero-text {
      max-width: 550px;
    }

    .hero-text h1 {
      font-size: 3.2rem;
      font-family: 'Playfair Display', serif;
      color: #1e3a8a;
      line-height: 1.2;
      margin-bottom: 20px;
    }

    .hero-text h1 span {
      color: #60a5fa;
    }

    .hero-text p {
      font-size: 1.1rem;
      color: #475569;
      margin-bottom: 30px;
    }

    .hero-text a {
      background: linear-gradient(45deg, #60a5fa, #93c5fd);
      color: white;
      padding: 14px 32px;
      border-radius: 50px;
      text-decoration: none;
      font-weight: 600;
      font-size: 1rem;
      box-shadow: 0 6px 20px rgba(96, 165, 250, 0.3);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .hero-text a:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 24px rgba(96, 165, 250, 0.4);
    }

    .hero-image img {
      width: 500px;
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
      animation: float 5s ease-in-out infinite;
    }

    @keyframes float {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-15px); }
    }

    section {
      padding: 80px 80px;
    }

    .about, .features, .contact {
      text-align: center;
    }

    .about h2, .features h2, .contact h2 {
      font-size: 2.5rem;
      margin-bottom: 20px;
    }

    .about p {
      max-width: 700px;
      margin: 0 auto;
      font-size: 1.1rem;
      color: #475569;
    }

    .about img {
      max-width: 600px;
      margin-top: 40px;
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    .features .cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 30px;
      margin-top: 40px;
    }

    .card {
      background: #f0f6ff;
      border-radius: 20px;
      padding: 30px;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .card:hover {
      transform: translateY(-10px);
      box-shadow: 0 12px 32px rgba(0, 0, 0, 0.12);
    }

    .card i {
      font-size: 2.8rem;
      color: #60a5fa;
      margin-bottom: 20px;
    }

    .card h4 {
      font-size: 1.5rem;
      margin-bottom: 12px;
    }

    .card p {
      font-size: 0.95rem;
      color: #475569;
    }

    .contact p {
      max-width: 600px;
      margin: 0 auto;
      font-size: 1.1rem;
      color: #475569;
      line-height: 1.8;
    }

    .contact p a {
      color: #60a5fa;
      text-decoration: none;
      font-weight: 600;
    }

    .contact p a:hover {
      text-decoration: underline;
    }

    @media (max-width: 1024px) {
      .hero {
        flex-direction: column;
        text-align: center;
        padding: 60px 40px;
      }

      .hero-image img {
        width: 80%;
        margin-top: 40px;
      }

      .hero-text {
        max-width: 100%;
      }
    }

    @media (max-width: 768px) {
      header {
        flex-direction: column;
        gap: 20px;
        padding: 20px 40px;
      }

      header nav {
        flex-direction: column;
        gap: 15px;
      }

      section {
        padding: 60px 20px;
      }

      .hero-text h1 {
        font-size: 2.5rem;
      }

      .hero-image img {
        width: 100%;
      }
    }
  </style>
</head>
<body>

<header>
  <div class="logo"><h2>Blast</h2></div>
  <nav>
    <a href="#about">About</a>
    <a href="#features">Fitur</a>
    <a href="#contact">Contact</a>
  </nav>
  <a href="{{ route('admin.login') }}" class="btn-login">Login</a>
</header>

<section class="hero" id="home">
  <div class="hero-text">
    <h1>Sistem Sarpras<br><span>di Ujung Jari Kamu 💡</span></h1>
    <p>Kelola aset sekolah dengan cepat, rapi, dan real-time melalui platform digital kami.</p>
    <a href="{{ route('admin.login') }}">Login Admin</a>
  </div>
  <div class="hero-image">
    <img src="images/landingg.png" alt="Sarpras Illustration">
  </div>
</section>

<section class="about" id="about">
  <h2>Tentang Blast</h2>
  <p>
    Blast adalah platform digitalisasi sarana dan prasarana sekolah. Kami membantu kamu mengelola aset sekolah dengan cepat, rapi, dan real-time, sehingga operasional sekolah jadi lebih efisien.
  </p>
  <img src="images/about.png" alt="Tentang Blast">
</section>

<section class="features" id="features">
  <h2>Fitur Unggulan</h2>
  <div class="cards">
    <div class="card">
      <i class="fas fa-boxes"></i>
      <h4>Manajemen Inventaris</h4>
      <p>Lacak dan kelola semua aset sekolah dalam satu dashboard yang intuitif dan mudah digunakan.</p>
    </div>
    <div class="card">
      <i class="fas fa-chart-line"></i>
      <h4>Laporan Real-Time</h4>
      <p>Dapatkan data akurat dan analisis kebutuhan sekolah secara instan dan transparan.</p>
    </div>
    <div class="card">
      <i class="fas fa-bolt"></i>
      <h4>Notifikasi Cerdas</h4>
      <p>Pengingat otomatis untuk perawatan, penggantian barang, atau pengadaan baru.</p>
    </div>
  </div>
</section>

<section class="contact" id="contact">
  <h2>Kontak Kami</h2>
  <p>
    Punya pertanyaan atau ide kolaborasi? Kami siap mendengar!<br><br>
    📧 Email: <a href="mailto:alisa@gmail.com">alisa@gmail.com</a><br>
    📞 Telepon: <a href="tel:+6278900011009">+62 789 0001 1009</a><br>
    🏢 Alamat: Taruna Bhakti, Depok
  </p>
</section>

</body>
</html>