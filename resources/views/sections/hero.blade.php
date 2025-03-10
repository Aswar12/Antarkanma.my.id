<section id="home" class="relative min-h-screen pt-32 pb-20 bg-[#020238] overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0">
        <!-- Gradient Orbs -->
        <div class="absolute -top-1/2 -right-1/2 w-[1000px] h-[1000px] rounded-full bg-[#FF6600]/20 blur-3xl"></div>
        <div class="absolute -bottom-1/2 -left-1/2 w-[1000px] h-[1000px] rounded-full bg-[#020238]/40 blur-3xl"></div>

        <!-- Animated Patterns -->
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjIiIGZpbGw9IiNGRjY2MDAiIGZpbGwtb3BhY2l0eT0iMC4xIi8+PC9zdmc+')] opacity-20"></div>
    </div>

    <!-- Content -->
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <!-- Hero Content -->
            <div class="text-center lg:text-left space-y-8">
                <!-- Badge -->
                <div class="inline-flex items-center space-x-2 bg-white/10 backdrop-blur-md px-4 py-2 rounded-full">
                    <span class="animate-pulse w-2 h-2 rounded-full bg-[#FF6600]"></span>
                    <span class="text-white text-sm">Platform Pengiriman #1 di Segeri, Ma'rang, dan Mandalle</span>
                </div>

                <!-- Main Heading -->
                <div class="space-y-4">
                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold">
                        <span class="text-white block mb-2">Solusi Pengiriman</span>
                        <span class="text-[#FF6600] relative inline-block">
                            Terpercaya
                            <svg class="absolute -bottom-2 left-0 w-full" viewBox="0 0 200 8" fill="none">
                                <path d="M1 5.5C47.6667 2.33333 154.4 -2.4 199 6" stroke="#FF6600" stroke-width="2"/>
                            </svg>
                        </span>
                    </h1>
                    <p class="text-lg text-gray-300 max-w-xl mx-auto lg:mx-0">
                        Platform pengiriman yang menghubungkan pelanggan dengan merchant lokal terbaik.
                        Aman, cepat, dan terpercaya dengan sistem verifikasi multi-layer.
                    </p>
                </div>

                <!-- CTA Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                    <!-- Primary CTA -->
                    <a href="/register"
                       class="group relative inline-flex items-center justify-center bg-[#FF6600] text-white px-8 py-3 rounded-full overflow-hidden transition-all duration-300 transform hover:scale-105 hover:shadow-lg">
                        <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-56 group-hover:h-56"></span>
                        <span class="relative flex items-center space-x-2">
                            <span>Mulai Sekarang</span>
                            <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </span>
                    </a>

                    <!-- Secondary CTA -->
                    <a href="#features"
                       class="group relative inline-flex items-center justify-center border-2 border-white text-white px-8 py-3 rounded-full overflow-hidden transition-all duration-300">
                        <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-56 group-hover:h-56"></span>
                        <span class="relative flex items-center space-x-2 group-hover:text-[#020238]">
                            <span>Pelajari Lebih Lanjut</span>
                            <svg class="w-5 h-5 transform group-hover:translate-y-1 transition-transform"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 9l-7 7-7-7"/>
                            </svg>
                        </span>
                    </a>
                </div>
            </div>

            <!-- Hero Stats -->
            <div class="relative">
                <!-- Floating Elements -->
                <div class="absolute -top-20 -left-20 w-40 h-40 bg-[#FF6600]/20 rounded-full blur-3xl animate-pulse"></div>
                <div class="absolute -bottom-20 -right-20 w-40 h-40 bg-[#020238]/40 rounded-full blur-3xl animate-pulse"></div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-2 gap-6">
                    <div class="group bg-white/10 backdrop-blur-md p-6 rounded-xl transform transition-all duration-300 hover:scale-105 hover:bg-white/20">
                        <div class="text-[#FF6600] text-3xl font-bold mb-2" x-text="statistics.merchants_count + '+'"></div>
                        <div class="text-white">Merchant Aktif</div>
                        <div class="absolute inset-0 rounded-xl border border-white/20 transition-all duration-300 group-hover:border-[#FF6600]/50"></div>
                        <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-br from-white/5 to-transparent rounded-xl opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>
                    </div>
                    <div class="group bg-white/10 backdrop-blur-md p-6 rounded-xl transform transition-all duration-300 hover:scale-105 hover:bg-white/20">
                        <div class="text-[#FF6600] text-3xl font-bold mb-2" x-text="statistics.users_count + '+'"></div>
                        <div class="text-white">Pengguna Terdaftar</div>
                        <div class="absolute inset-0 rounded-xl border border-white/20 transition-all duration-300 group-hover:border-[#FF6600]/50"></div>
                        <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-br from-white/5 to-transparent rounded-xl opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>
                    </div>
                    <div class="group bg-white/10 backdrop-blur-md p-6 rounded-xl transform transition-all duration-300 hover:scale-105 hover:bg-white/20">
                        <div class="text-[#FF6600] text-3xl font-bold mb-2" x-text="statistics.courier_count + '+'"></div>
                        <div class="text-white">Kurir Aktif</div>
                        <div class="absolute inset-0 rounded-xl border border-white/20 transition-all duration-300 group-hover:border-[#FF6600]/50"></div>
                        <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-br from-white/5 to-transparent rounded-xl opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>
                    </div>
                    <div class="group bg-white/10 backdrop-blur-md p-6 rounded-xl transform transition-all duration-300 hover:scale-105 hover:bg-white/20">
                        <div class="text-[#FF6600] text-3xl font-bold mb-2" x-text="statistics.cities_served"></div>
                        <div class="text-white">Kota Terlayani</div>
                        <div class="absolute inset-0 rounded-xl border border-white/20 transition-all duration-300 group-hover:border-[#FF6600]/50"></div>
                        <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-br from-white/5 to-transparent rounded-xl opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scroll Indicator -->
    <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 flex flex-col items-center space-y-2">
        <span class="text-white/60 text-sm">Scroll untuk melihat lebih</span>
        <div class="w-6 h-10 border-2 border-white/20 rounded-full p-1">
            <div class="w-1.5 h-1.5 bg-white rounded-full animate-bounce mx-auto"></div>
        </div>
    </div>
</section>
