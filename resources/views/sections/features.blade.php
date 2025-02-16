<section id="features" class="relative py-20 bg-white overflow-hidden">
    <!-- Decorative Background -->
    <div class="absolute inset-0">
        <div class="absolute inset-0 bg-[#020238]/5 mask-radial-faded"></div>
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[800px] bg-gradient-to-b from-[#FF6600]/20 to-transparent opacity-30 blur-3xl"></div>
    </div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Header -->
        <div class="text-center mb-16 space-y-4">
            <span class="text-[#FF6600] font-semibold">Fitur Unggulan</span>
            <h2 class="text-3xl md:text-4xl font-bold text-[#020238]">
                Keunggulan Platform Kami
            </h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                Sistem pengiriman yang aman dan terpercaya dengan berbagai fitur unggulan untuk memudahkan transaksi Anda
            </p>
        </div>

        <!-- Main Features Grid -->
        <div class="grid md:grid-cols-3 gap-8 mb-16">
            <template x-for="(feature, index) in [
                {
                    icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
                    title: 'Sistem Terverifikasi',
                    description: 'Verifikasi multi-layer untuk merchant dan kurir menjamin keamanan transaksi'
                },
                {
                    icon: 'M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7',
                    title: 'Tracking Real-time',
                    description: 'Pantau status pengiriman secara real-time dengan GPS tracking'
                },
                {
                    icon: 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z',
                    title: 'Multi-Payment',
                    description: 'Berbagai metode pembayaran untuk kemudahan transaksi Anda'
                }
            ]">
                <div class="group relative bg-white p-8 rounded-2xl shadow-lg transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                    <!-- Icon -->
                    <div class="absolute -top-6 left-6">
                        <div class="w-12 h-12 bg-gradient-to-br from-[#020238] to-[#FF6600] rounded-xl flex items-center justify-center transform -rotate-6 group-hover:rotate-0 transition-transform duration-300">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path :d="feature.icon" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                            </svg>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="pt-8">
                        <h3 class="text-xl font-bold text-[#020238] mb-4" x-text="feature.title"></h3>
                        <p class="text-gray-600" x-text="feature.description"></p>
                    </div>

                    <!-- Decorative Elements -->
                    <div class="absolute inset-0 rounded-2xl border border-gray-100 transition-all duration-300 group-hover:border-[#FF6600]/30"></div>
                    <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-br from-white/5 to-transparent rounded-2xl opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>
                </div>
            </template>
        </div>

        <!-- Additional Features Grid -->
        <div class="grid md:grid-cols-4 gap-8">
            <template x-for="(stat, index) in [
                {
                    icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                    number: '24/7',
                    label: 'Layanan Support',
                    description: 'Tim support kami siap membantu Anda kapanpun'
                },
                {
                    icon: 'M13 10V3L4 14h7v7l9-11h-7z',
                    number: '< 60',
                    label: 'Menit Pengantaran',
                    description: 'Pengiriman cepat ke lokasi tujuan'
                },
                {
                    icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
                    number: '100%',
                    label: 'Aman & Terpercaya',
                    description: 'Jaminan keamanan transaksi Anda'
                },
                {
                    icon: 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z',
                    number: '4.9/5',
                    label: 'Rating Pengguna',
                    description: 'Kepuasan pengguna adalah prioritas kami'
                }
            ]">
                <div class="group relative bg-white p-6 rounded-2xl shadow-lg transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                    <!-- Icon -->
                    <div class="w-12 h-12 bg-[#020238] rounded-xl flex items-center justify-center mb-4 group-hover:bg-[#FF6600] transition-colors duration-300">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path :d="stat.icon" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                        </svg>
                    </div>

                    <!-- Content -->
                    <div class="space-y-2">
                        <div class="text-2xl font-bold text-[#020238]" x-text="stat.number"></div>
                        <div class="text-[#FF6600] font-semibold" x-text="stat.label"></div>
                        <p class="text-sm text-gray-600" x-text="stat.description"></p>
                    </div>

                    <!-- Hover Effect -->
                    <div class="absolute inset-0 rounded-2xl border border-gray-100 transition-all duration-300 group-hover:border-[#FF6600]/30"></div>
                </div>
            </template>
        </div>

        <!-- Feature Highlight -->
        <div class="mt-20 relative bg-gradient-to-r from-[#020238] to-[#FF6600] p-8 md:p-12 rounded-3xl overflow-hidden">
            <!-- Decorative Background -->
            <div class="absolute inset-0">
                <div class="absolute inset-0 bg-gradient-to-r from-[#020238] to-[#FF6600] opacity-90"></div>
                <div class="absolute inset-0 bg-[url('/pattern.svg')] opacity-20"></div>
            </div>

            <!-- Content -->
            <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-8">
                <div class="text-center md:text-left">
                    <h3 class="text-2xl md:text-3xl font-bold text-white mb-4">
                        Siap Mencoba Fitur Kami?
                    </h3>
                    <p class="text-white/80 max-w-xl">
                        Daftar sekarang dan nikmati kemudahan pengiriman dengan berbagai fitur unggulan kami
                    </p>
                </div>
                <a href="/register" class="inline-flex items-center justify-center bg-white text-[#020238] px-8 py-4 rounded-full font-semibold hover:bg-[#FF6600] hover:text-white transition-all duration-300 transform hover:scale-105 group">
                    <span>Mulai Sekarang</span>
                    <svg class="w-5 h-5 ml-2 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</section>
