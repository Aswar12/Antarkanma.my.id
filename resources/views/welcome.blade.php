<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description"
        content="Antarkanma - Platform pengiriman terpercaya yang menghubungkan pelanggan dengan merchant lokal terbaik di Segeri, Ma'rang, dan Mandalle">
    <meta name="keywords" content="antarkanma, delivery service, pengiriman, merchant lokal, segeri, ma'rang, mandalle">
    <title>{{ config('app.name', 'Antarkanma') }} - Platform Pengiriman Terpercaya</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts and Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Custom Styles -->
    <style>
        [x-cloak] {
            display: none !important;
        }

        @keyframes float {
            0% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-20px);
            }

            100% {
                transform: translateY(0px);
            }
        }

        .animate-float {
            animation: float 6s ease-in-out infinite;
        }

        @keyframes slide-up {
            0% {
                transform: translateY(100px);
                opacity: 0;
            }

            100% {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .animate-slide-up {
            animation: slide-up 0.5s ease-out forwards;
        }

        .mask-radial-faded {
            mask-image: radial-gradient(circle at center, black, transparent 80%);
        }

        .clip-path-diagonal {
            clip-path: polygon(0 0, 100% 0, 100% 85%, 0 100%);
        }
    </style>
</head>

<body class="antialiased font-sans bg-gray-50"
    x-data="{
        mobileMenuOpen: false,
        scrolled: false,
        activeSection: 'home',
        showBackToTop: false,
        statistics: {{ Js::from(app(App\Http\Controllers\StatisticsController::class)->getHomeStatistics()) }}
    }"
    @scroll.window="
          scrolled = window.pageYOffset > 20;
          showBackToTop = window.pageYOffset > 500;
          activeSection =
              window.pageYOffset < 500 ? 'home' :
              window.pageYOffset < 1000 ? 'features' :
              window.pageYOffset < 1500 ? 'merchants' :
              window.pageYOffset < 2000 ? 'delivery' : 'team';
      ">

    <!-- Navigation -->
    @include('sections.navigation')

    <main class="overflow-hidden">
        <!-- Hero Section -->
        @include('sections.hero')

        <!-- Features Section -->
        @include('sections.features')

        <!-- Merchant Section -->
        @include('sections.merchant')

        <!-- Delivery Section -->
        @include('sections.delivery')

        <!-- Team Section -->
        @include('sections.team')
    </main>

    <!-- Footer -->
    @include('sections.footer')

    <!-- Back to Top Button -->
    <button x-cloak x-show="showBackToTop" @click="window.scrollTo({top: 0, behavior: 'smooth'})"
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-10"
        x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-10"
        class="fixed bottom-8 right-8 bg-[#FF6600] text-white p-3 rounded-full shadow-lg hover:bg-[#020238] transition-colors duration-300 focus:outline-none z-50">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
        </svg>
    </button>

    <!-- Preload Images -->
    <div class="hidden">
        <img src="{{ asset('images/Logo_NoFont.png') }}" alt="Preload Logo">
        <img src="{{ asset('images/husain.jpeg') }}" alt="Preload Team">
        <img src="{{ asset('images/akbar.jpeg') }}" alt="Preload Team">
        <img src="{{ asset('images/ichal.jpeg') }}" alt="Preload Team">
        <img src="{{ asset('images/firman.jpeg') }}" alt="Preload Team">
    </div>
</body>

</html>
