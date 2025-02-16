<section id="merchants" class="relative py-20 bg-[#020238] overflow-hidden">
    <!-- Decorative Background -->
    <div class="absolute inset-0">
        <!-- Gradient Overlay -->
        <div class="absolute top-0 left-0 w-full h-full bg-[#FF6600]/10 mix-blend-multiply"></div>
        <div class="absolute inset-0 bg-gradient-to-b from-[#020238] via-transparent to-[#020238]"></div>

        <!-- Animated Pattern -->
        <div class="absolute inset-0">
            <div class="absolute inset-0 opacity-30">
                <div class="h-full w-full bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjIiIGZpbGw9IiNGRjY2MDAiIGZpbGwtb3BhY2l0eT0iMC4xIi8+PC9zdmc+')] bg-repeat"></div>
            </div>
        </div>
    </div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Header -->
        <div class="text-center mb-16 space-y-4">
            <span class="text-[#FF6600] font-semibold">Mitra Merchant</span>
            <h2 class="text-3xl md:text-4xl font-bold text-white">
                Bergabung Sebagai Merchant
            </h2>
            <p class="text-lg text-gray-300 max-w-3xl mx-auto">
                Tingkatkan penjualan Anda dengan bergabung bersama ratusan merchant sukses lainnya
            </p>
        </div>

        <!-- Merchant Benefits -->
        <div class="grid md:grid-cols-3 gap-8 mb-16">
            <template x-for="(benefit, index) in [
                {
                    icon: 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6',
                    title: 'Tingkatkan Omset',
                    description: 'Jangkau lebih banyak pelanggan dan tingkatkan penjualan Anda dengan sistem pengiriman yang handal'
                },
                {
                    icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                    title: 'Analisis Bisnis',
                    description: 'Pantau performa bisnis Anda dengan dashboard analitik yang lengkap dan mudah dipahami'
                },
                {
                    icon: 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z',
                    title: 'Pembayaran Mudah',
                    description: 'Terima pembayaran dengan berbagai metode yang aman dan terpercaya'
                }
            ]">
                <div class="group relative bg-white/10 backdrop-blur-md p-8 rounded-2xl transition-all duration-300 hover:bg-white/20">
                    <!-- Icon -->
                    <div class="w-14 h-14 bg-[#FF6600] rounded-xl flex items-center justify-center mb-6 transform group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path :d="benefit.icon" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                        </svg>
                    </div>

                    <!-- Content -->
                    <h3 class="text-xl font-bold text-white mb-4" x-text="benefit.title"></h3>
                    <p class="text-gray-300" x-text="benefit.description"></p>

                    <!-- Decorative Elements -->
                    <div class="absolute inset-0 rounded-2xl border border-white/20 transition-all duration-300 group-hover:border-[#FF6600]/50"></div>
                </div>
            </template>
        </div>

        <!-- Process Steps -->
        <div class="mb-20">
            <h3 class="text-2xl font-bold text-white text-center mb-12">
                Proses Bergabung yang Mudah
            </h3>

            <div class="grid md:grid-cols-4 gap-8">
                <template x-for="(step, index) in [
                    {
                        number: '01',
                        title: 'Daftar',
                        description: 'Isi formulir pendaftaran dengan data lengkap usaha Anda'
                    },
                    {
                        number: '02',
                        title: 'Verifikasi',
                        description: 'Tim kami akan memverifikasi kelengkapan data Anda'
                    },
                    {
                        number: '03',
                        title: 'Pelatihan',
                        description: 'Dapatkan pelatihan penggunaan dashboard merchant'
                    },
                    {
                        number: '04',
                        title: 'Mulai Berjualan',
                        description: 'Terima pesanan dan mulai tingkatkan penjualan Anda'
                    }
                ]">
                    <div class="relative group">
                        <!-- Step Number -->
                        <div class="absolute -top-6 -left-6 w-12 h-12 bg-[#FF6600] rounded-full flex items-center justify-center text-white font-bold text-xl transform -rotate-12 group-hover:rotate-0 transition-transform duration-300">
                            <span x-text="step.number"></span>
                        </div>

                        <!-- Content -->
                        <div class="bg-white/10 backdrop-blur-md p-6 rounded-xl pt-8">
                            <h4 class="text-lg font-semibold text-white mb-2" x-text="step.title"></h4>
                            <p class="text-gray-300 text-sm" x-text="step.description"></p>
                        </div>

                        <!-- Connector Line (except for last item) -->
                        <template x-if="index < 3">
                            <div class="hidden md:block absolute top-1/2 -right-4 w-8 border-t-2 border-dashed border-[#FF6600]/30"></div>
                        </template>
                    </div>
                </template>
            </div>
        </div>

        <!-- Merchant CTA -->
        <div class="relative bg-gradient-to-r from-[#020238] to-[#FF6600] p-8 md:p-12 rounded-3xl overflow-hidden">
            <!-- Decorative Background -->
            <div class="absolute inset-0">
                <div class="absolute inset-0 bg-gradient-to-r from-[#020238] to-[#FF6600] opacity-90"></div>
                <div class="absolute inset-0 bg-[url('/pattern.svg')] opacity-20"></div>
            </div>

            <!-- Content -->
            <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-8">
                <div class="text-center md:text-left">
                    <h3 class="text-2xl md:text-3xl font-bold text-white mb-4">
                        Siap Bergabung Dengan Kami?
                    </h3>
                    <p class="text-white/80 max-w-xl">
                        Daftarkan bisnis Anda sekarang dan nikmati berbagai keuntungan menarik sebagai merchant Antarkanma
                    </p>
                </div>
                <a href="/merchant/register" class="inline-flex items-center justify-center bg-white text-[#020238] px-8 py-4 rounded-full font-semibold hover:bg-[#FF6600] hover:text-white transition-all duration-300 transform hover:scale-105 group">
                    <span>Daftar Sebagai Merchant</span>
                    <svg class="w-5 h-5 ml-2 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</section>
