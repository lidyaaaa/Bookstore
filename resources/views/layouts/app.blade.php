<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'BookStore — Toko Buku')</title>

    {{-- Google Fonts: Plus Jakarta Sans & JetBrains Mono --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@500;600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/simperpus.css') }}?v={{ time() }}" rel="stylesheet">
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-glass py-2 no-print">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2 text-slate-900 me-4" href="{{ route('home') }}">
                <span class="brand-logo-box">
                    <i class="bi bi-bag-check-fill fs-5"></i>
                </span>
                <div>
                    <div class="lh-1 fw-extrabold" style="font-size: 1.05rem;">BookStore</div>
                    <div class="text-slate-600 fw-medium" style="font-size: 0.68rem;">Toko Buku</div>
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
                <strong class="text-slate-900">BookStore</strong> &mdash; Sistem Informasi Toko Buku &amp; Transaksi Online
            </div>
            <div>
                Katalog Buku &bull; Keranjang Belanja &bull; Transaksi Terpadu
            </div>
        </div>
    </footer>

    {{-- Floating WhatsApp Chat Button --}}
    <a href="https://wa.me/6287866984662?text={{ rawurlencode('Halo Admin BookStore, saya ingin bertanya seputar belanja dan pesanan buku.') }}"
       target="_blank"
       rel="noopener noreferrer"
       class="wa-floating-btn no-print"
       title="Chat Admin WhatsApp"
       aria-label="Chat Admin via WhatsApp">
        <svg class="wa-icon" viewBox="0 0 24 24" width="28" height="28" fill="currentColor">
            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
        </svg>
        <span class="wa-text">Chat Admin</span>
    </a>

    <style>
        .wa-floating-btn {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 1050;
            display: inline-flex;
            align-items: center;
            background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
            color: #ffffff !important;
            padding: 12px 16px;
            border-radius: 9999px;
            box-shadow: 0 10px 25px -5px rgba(37, 211, 102, 0.45), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
            font-weight: 600;
            font-size: 0.95rem;
        }
        .wa-floating-btn:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 16px 30px -4px rgba(37, 211, 102, 0.55), 0 10px 12px -5px rgba(0, 0, 0, 0.15);
            color: #ffffff !important;
        }
        .wa-floating-btn .wa-icon {
            flex-shrink: 0;
            transition: transform 0.3s ease;
        }
        .wa-floating-btn:hover .wa-icon {
            transform: rotate(10deg);
        }
        .wa-floating-btn .wa-text {
            margin-left: 10px;
            white-space: nowrap;
            letter-spacing: 0.01em;
            transition: opacity 0.3s ease;
        }
        @media (max-width: 576px) {
            .wa-floating-btn {
                bottom: 18px;
                right: 18px;
                padding: 12px;
            }
            .wa-floating-btn .wa-text {
                display: none;
            }
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        window.addEventListener('pageshow', function (event) {
            if (event.persisted || (window.performance && window.performance.getEntriesByType('navigation')[0]?.type === 'back_forward')) {
                window.location.reload();
            }
        });
    </script>
</body>

</html>
