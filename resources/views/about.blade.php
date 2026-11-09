@extends('layouts.app')

@section('title', 'About Us — BookStore')

@section('content')
<div class="py-4">
    {{-- Hero Section --}}
    <div class="p-5 mb-5 rounded-4 text-white position-relative overflow-hidden shadow" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
        <div class="position-absolute end-0 top-50 translate-middle-y opacity-10 pe-5 d-none d-md-block">
            <i class="bi bi-book fs-1" style="font-size: 14rem !important;"></i>
        </div>
        <div class="position-relative" style="max-width: 680px; z-index: 2;">
            <span class="badge bg-primary bg-opacity-25 text-white border border-primary border-opacity-25 px-3 py-2 rounded-pill fw-semibold mb-3">
                <i class="bi bi-award-fill me-1"></i> Tentang Toko Buku Digital Kami
            </span>
            <h1 class="display-5 fw-extrabold mb-3 tracking-tight">Menghubungkan Pembaca dengan Jendela Dunia</h1>
            <p class="fs-6 text-slate-300 mb-4 lh-lg">
                <strong>BookStore</strong> hadir sebagai platform toko buku modern yang berdedikasi menyediakan ragam literatur bermutu tinggi. Mulai dari karya fiksi inspiratif, buku sains & teknologi, hingga pengembangan diri untuk memperkaya wawasan masyarakat Indonesia.
            </p>
            <div class="d-flex flex-wrap gap-3">
                <a href="{{ route('books.index') }}" class="btn btn-primary px-4 py-2 rounded-pill fw-semibold shadow-sm">
                    <i class="bi bi-shop me-1"></i> Jelajahi Koleksi Buku
                </a>
                <a href="#keunggulan" class="btn btn-outline-light px-4 py-2 rounded-pill fw-semibold">
                    <i class="bi bi-stars me-1"></i> Pelajari Keunggulan
                </a>
            </div>
        </div>
    </div>

    {{-- Visi & Misi --}}
    <div class="row g-4 mb-5">
        <div class="col-md-6">
            <div class="card h-100 border-0 shadow-sm rounded-4 p-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-3 p-3 bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-eye-fill fs-3"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0 text-slate-900">Visi Kami</h4>
                        <span class="small text-muted">Aspirasi jangka panjang BookStore</span>
                    </div>
                </div>
                <p class="text-slate-600 mb-0 lh-lg">
                    Menjadi penyedia literatur dan platform toko buku digital paling terpercaya, lengkap, dan mudah diakses di Indonesia yang menginspirasi lahirnya generasi cerdas, kritis, dan berpengetahuan luas.
                </p>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100 border-0 shadow-sm rounded-4 p-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-3 p-3 bg-success bg-opacity-10 text-success">
                        <i class="bi bi-bullseye fs-3"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0 text-slate-900">Misi Kami</h4>
                        <span class="small text-muted">Langkah nyata yang kami wujudkan</span>
                    </div>
                </div>
                <ul class="text-slate-600 mb-0 lh-lg ps-3">
                    <li>Menyediakan kurasi buku terbitan lokal & internasional berkualitas dengan harga terjangkau.</li>
                    <li>Menghadirkan pengalaman belanja buku online yang aman, cepat, dan transparan.</li>
                    <li>Mendukung budaya literasi dengan layanan pelanggan yang ramah serta responsif.</li>
                </ul>
            </div>
        </div>
    </div>

    {{-- 3 Kartu Keunggulan --}}
    <div id="keunggulan" class="mb-5 pt-2">
        <div class="text-center mb-4">
            <h3 class="fw-bold text-slate-900">Mengapa Memilih BookStore?</h3>
            <p class="text-slate-600">Komitmen kami untuk memberikan pengalaman berbelanja terbaik bagi setiap pecinta buku</p>
        </div>

        <div class="row g-4">
            {{-- Card 1 --}}
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm rounded-4 p-4 text-center transition-hover">
                    <div class="mx-auto rounded-circle d-flex align-items-center justify-content-center mb-3 shadow-sm" style="width: 72px; height: 72px; background: #e0f2fe; color: #0284c7;">
                        <i class="bi bi-journal-bookmark-fill fs-2"></i>
                    </div>
                    <h5 class="fw-bold text-slate-900 mb-2">Koleksi Lengkap</h5>
                    <p class="text-slate-600 small mb-0 lh-base">
                        Ribuan judul dari berbagai genre populer: Fiksi, Sastra, Teknologi, Bisnis, Komik, hingga Buku Akademik terbitan penerbit terkemuka.
                    </p>
                </div>
            </div>

            {{-- Card 2 --}}
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm rounded-4 p-4 text-center transition-hover">
                    <div class="mx-auto rounded-circle d-flex align-items-center justify-content-center mb-3 shadow-sm" style="width: 72px; height: 72px; background: #fef3c7; color: #d97706;">
                        <i class="bi bi-lightning-charge-fill fs-2"></i>
                    </div>
                    <h5 class="fw-bold text-slate-900 mb-2">Pemesanan Cepat</h5>
                    <p class="text-slate-600 small mb-0 lh-base">
                        Alur keranjang belanja praktis, konfirmasi transaksi instan, integrasi berbagai metode pembayaran, dan pencatatan struk otomatis.
                    </p>
                </div>
            </div>

            {{-- Card 3 --}}
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm rounded-4 p-4 text-center transition-hover">
                    <div class="mx-auto rounded-circle d-flex align-items-center justify-content-center mb-3 shadow-sm" style="width: 72px; height: 72px; background: #dcfce7; color: #16a34a;">
                        <i class="bi bi-headset fs-2"></i>
                    </div>
                    <h5 class="fw-bold text-slate-900 mb-2">Layanan Responsif</h5>
                    <p class="text-slate-600 small mb-0 lh-base">
                        Tim admin kami siap membantu pertanyaan katalog, ketersediaan stok, pengiriman pesanan, hingga bantuan kendala akun Anda.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Banner Ajakan Kontak Admin --}}
    <div class="card border-0 shadow rounded-4 p-4 p-lg-5 text-white" style="background: linear-gradient(135deg, #0284c7 0%, #2563eb 100%);">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <h3 class="fw-extrabold mb-2 text-white">Butuh Rekomendasi Buku atau Bantuan Pesanan?</h3>
                <p class="text-white-50 mb-0 fs-6">
                    Hubungi tim Customer Support BookStore kapan saja. Kami siap melayani konsultasi pembelian buku dan pengiriman pesanan Anda secara langsung via WhatsApp.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="https://wa.me/6287866984662?text={{ rawurlencode('Halo Admin BookStore, saya ingin bertanya seputar pemesanan buku.') }}" 
                   target="_blank" 
                   rel="noopener noreferrer" 
                   class="btn btn-light px-4 py-3 rounded-pill fw-bold text-primary shadow">
                    <i class="bi bi-whatsapp text-success fs-5 me-1 align-middle"></i> Chat Admin Sekarang
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
