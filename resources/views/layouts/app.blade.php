<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'SIMPerpus — Perpustakaan Digital Modern')</title>

    {{-- Google Fonts: Plus Jakarta Sans & JetBrains Mono --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@500;600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/simperpus.css') }}" rel="stylesheet">
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-glass py-2 no-print">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2 text-slate-900 me-4" href="{{ route('home') }}">
                <span class="brand-logo-box">
                    <i class="bi bi-journal-bookmark-fill fs-5"></i>
                </span>
                <div>
                    <div class="lh-1 fw-extrabold" style="font-size: 1.05rem;">SIMPerpus</div>
                    <div class="text-slate-600 fw-medium" style="font-size: 0.68rem;">Digital Library System</div>
                </div>
            </a>

            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <i class="bi bi-list fs-3 text-slate-900"></i>
            </button>

            <div class="collapse navbar-collapse" id="navbarMain">
                {{-- Navigasi Terpisah Sesuai Role --}}
                @auth
                    @if(auth()->user()->role === 'admin')
                        @include('layouts.partials.navbar-admin')
                    @else
                        @include('layouts.partials.navbar-user')
                    @endif
                @else
                    @include('layouts.partials.navbar-guest')
                @endauth
            </div>
        </div>
    </nav>

    <main class="container py-4 flex-grow-1">
        @include('layouts.partials.flash-alerts')

        @yield('content')
    </main>

    <footer class="bg-white border-top py-3 mt-5 no-print">
        <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center text-slate-600 small">
            <div>
                <strong class="text-slate-900">SIMPerpus</strong> &mdash; Sistem Informasi Manajemen Perpustakaan Modern
            </div>
            <div>
                OPAC Digital &bull; Sirkulasi Terpadu &bull; Standar Portofolio USK RPL
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
