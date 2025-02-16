<section id="delivery" class="relative py-20 bg-white overflow-hidden">
    <!-- Decorative Background -->
    <div class="absolute inset-0">
        <div class="absolute inset-0 bg-[#020238]/5 mask-radial-faded"></div>
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[800px] bg-gradient-to-b from-[#FF6600]/20 to-transparent opacity-30 blur-3xl"></div>
    </div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Header -->
        <div class="text-center mb-16 space-y-4">
            <span class="text-[#FF6600] font-semibold">Proses Pengiriman</span>
            <h2 class="text-3xl md:text-4xl font-bold text-[#020238]">
                Pengiriman Cepat dan Aman
            </h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                Kami memastikan setiap pengiriman dilakukan dengan cepat dan aman, memberikan pengalaman terbaik bagi pelanggan.
            </p>
        </div>

        <!-- Delivery Process Steps -->
        <div class="grid md:grid-cols-3 gap-8 mb-16">
            <template x-for="(step, index) in [
                {
                    number: '01',
                    title: 'Pesan',
                    description: 'Pilih produk dari merchant dan lakukan pemesanan melalui aplikasi.'
                },
                {
                    number: '02',
                    title: 'Proses',
                    description: 'Merchant mempersiapkan pesanan dan kurir mengambilnya.'
                },
                {
                    number: '03',
                    title: 'Pengantaran',
                    description: 'Kurir mengantarkan pesanan ke alamat yang ditentukan.'
                }
            ]">
                <div class="group relative bg-white/10 backdrop-blur-md p-8 rounded-2xl transition-all duration-300 hover:bg-white/20">
                    <!-- Step Number -->
                    <div class="absolute -top-6 -left-6 w-12 h-12 bg-[#FF6600] rounded-full flex items-center justify-center text-white font-bold text-xl transform -rotate-12 group-hover:rotate-0 transition-transform duration-300">
                        <span x-text="step.number"></span>
                    </div>

                    <!-- Content -->
                    <h4 class="text-lg font-semibold text-[#020238] mb-2" x-text="step.title"></h4>
                    <p class="text-gray-600 text-sm" x-text="step.description"></p>
                </div>
            </template>
        </div>

        <!-- Delivery CTA -->
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
                        Bergabunglah Sebagai Kurir
                    </h3>
                    <p class="text-white/80 max-w-xl">
                        Dapatkan penghasilan tambahan dengan menjadi kurir Antarkanma. Daftar sekarang dan mulai mengantarkan!
                    </p>
                </div>
                <a href="/courier/register" class="inline-flex items-center justify-center bg-white text-[#020238] px-8 py-4 rounded-full font-semibold hover:bg-[#FF6600] hover:text-white transition-all duration-300 transform hover:scale-105 group">
                    <span>Daftar Sebagai Kurir</span>
                    <svg class="w-5 h-5 ml-2 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</section>
