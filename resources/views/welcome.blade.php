<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="Penerimaan Peserta Didik Baru SD Kristen Diakui Rantai Damai" />
    <title>PPDB | SD Kristen Diakui Rantai Damai</title>

    {{-- Favicon --}}
    <link rel="icon" type="image/png" href="{{ asset('images/logo-sd-kristen-diakui-rantai-damai.png') }}">

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    {{-- Vite (Tailwind CSS) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        .glass-nav {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.8);
        }
        .hero-pattern {
            background-color: #0f4c2a;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
    </style>
</head>

<body class="font-sans antialiased text-slate-800 bg-slate-50" 
      x-data="{ 
          mobileMenuOpen: false, 
          loginModalOpen: {{ (session('login_modal') || session('error') || ($errors->any() && !old('name') && !old('email_reset'))) ? 'true' : 'false' }}, 
          registerModalOpen: {{ ($errors->any() && old('name')) ? 'true' : 'false' }}, 
          forgotPasswordModalOpen: {{ (session('forgot_error') || ($errors->any() && old('email_reset'))) ? 'true' : 'false' }}, 
          scrolled: false 
      }" 
      @scroll.window="scrolled = (window.pageYOffset > 20)">

    @include('components.landing.navbar')

    {{-- Global Alert --}}
    <div class="fixed top-24 left-0 right-0 z-40 max-w-2xl mx-auto px-4">
        @include('layout.alert')
    </div>

    @include('components.landing.hero')
    @include('components.landing.profil')
    @include('components.landing.informasi')
    @include('components.landing.alur')
    @include('components.landing.kontak')
    @include('components.landing.footer')
    @include('components.landing.modals')

    {{-- Centralized Native Scroll-Triggered Transitions --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Check prefers-reduced-motion: if enabled, reveal everything instantly without animation
            const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            if (prefersReduced) {
                document.querySelectorAll('.reveal, .reveal-left, .reveal-scale, .flow-step, .flow-arrow').forEach(el => {
                    el.classList.add('visible', 'flow-visible');
                });
                return;
            }

            // Centralized Intersection Observer
            const observer = new IntersectionObserver((entries, obs) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const el = entry.target;
                        const delay = parseInt(el.dataset.delay || 0, 10);
                        setTimeout(() => {
                            el.classList.add('visible');
                            if (el.classList.contains('flow-step')) {
                                el.classList.add('flow-visible');
                            }
                        }, delay);
                        obs.unobserve(el);
                    }
                });
            }, { 
                threshold: 0.15, 
                rootMargin: '0px 0px -40px 0px' 
            });

            document.querySelectorAll('.reveal, .reveal-left, .reveal-scale, .flow-step, .flow-arrow').forEach(el => {
                observer.observe(el);
            });
        });
    </script>
</body>
</html>