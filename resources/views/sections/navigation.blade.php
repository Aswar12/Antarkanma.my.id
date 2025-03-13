<nav class="fixed w-full z-50 transition-all duration-300" x-data="{ mobileMenuOpen: false }"
    :class="{ 'bg-[#020238]/95 backdrop-blur-md shadow-lg': scrolled, 'bg-transparent': !scrolled }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-20">
            <!-- Logo -->
            <div class="flex items-center">
                <a href="/" class="flex items-center space-x-3 group">
                    <img src="{{ asset('images/Logo_NoFont.png') }}" alt="Antarkanma Logo"
                        class="h-12 w-auto transform transition-all duration-300 group-hover:scale-110">
                    <span class="text-2xl font-bold text-white">AntarkanMa</span>
                </a>
            </div>

            <!-- Desktop Navigation -->
            <div class="hidden md:flex items-center space-x-8">
                <template
                    x-for="(item, index) in [
                    { name: 'Fitur', href: '#features' },
                    { name: 'Cerita', href: '#story' },
                    { name: 'Merchant', href: '#merchants' },
                    { name: 'Pengiriman', href: '#delivery' },
                    { name: 'Tim Kami', href: '#team' }
                ]">
                    <a :href="item.href" class="relative text-white group"
                        :class="{ 'text-[#FF6600]': activeSection === item.href.substring(1) }">
                        <span x-text="item.name" class="relative z-10"></span>
                        <span
                            class="absolute bottom-0 left-0 w-0 h-0.5 bg-[#FF6600] transition-all duration-300 group-hover:w-full"></span>
                    </a>
                </template>

                <div class="flex items-center space-x-4">
                    <!-- WhatsApp Button -->
                    <a href="https://wa.me/6287812379186" target="_blank"
                        class="flex items-center space-x-2 bg-green-500 text-white px-4 py-2 rounded-full hover:bg-green-600 transition-all duration-300 transform hover:scale-105 hover:shadow-lg">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                        </svg>
                        <span>Chat Admin</span>
                    </a>

                    <!-- Download APK Button -->
                    <a href="https://drive.google.com/drive/folders/1s9lTrOIK4JAoNAJ909F7UVymROf9-eMW?usp=sharing"
                        target="_blank"
                        class="flex items-center space-x-2 bg-[#FF6600] text-white px-4 py-2 rounded-full hover:bg-[#FF6600]/90 transition-all duration-300 transform hover:scale-105 hover:shadow-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        <span>Download APK</span>
                    </a>

                    @auth
                        <a href="{{ url('/dashboard') }}"
                            class="text-white hover:text-[#FF6600] transition-colors duration-300">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                            class="text-white hover:text-[#FF6600] transition-colors duration-300 relative group">
                            <span>Masuk</span>
                            <span
                                class="absolute bottom-0 left-0 w-0 h-0.5 bg-[#FF6600] transition-all duration-300 group-hover:w-full"></span>
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}"
                                class="bg-[#FF6600] text-white px-6 py-2 rounded-full hover:bg-[#FF6600]/90 transition-all duration-300 transform hover:scale-105 hover:shadow-lg">
                                Daftar
                            </a>
                        @endif
                    @endauth
                </div>
            </div>

            <!-- Mobile Menu Button -->
            <div class="md:hidden flex items-center">
                <button @click="mobileMenuOpen = !mobileMenuOpen"
                    class="text-white p-2 rounded-lg hover:bg-white/10 transition-colors duration-300">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path x-show="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div x-show="mobileMenuOpen" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-4" class="md:hidden bg-[#020238] border-t border-white/10">
        <div class="px-2 pt-2 pb-3 space-y-1">
            <template
                x-for="(item, index) in [
                { name: 'Fitur', href: '#features' },
                { name: 'Cerita', href: '#story' },
                { name: 'Merchant', href: '#merchants' },
                { name: 'Pengiriman', href: '#delivery' },
                { name: 'Tim Kami', href: '#team' }
            ]">
                <a :href="item.href" @click="mobileMenuOpen = false"
                    class="block px-3 py-2 text-white hover:bg-[#FF6600]/10 rounded-md transition-colors duration-300"
                    :class="{ 'bg-[#FF6600]/10': activeSection === item.href.substring(1) }">
                    <span x-text="item.name"></span>
                </a>
            </template>

            <!-- Mobile WhatsApp and Download Buttons -->
            <div class="px-3 py-2 space-y-2">
                <a href="https://wa.me/6287812379186" target="_blank"
                    class="flex items-center space-x-2 bg-green-500 text-white px-4 py-2 rounded-full hover:bg-green-600 transition-all duration-300">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                    </svg>
                    <span>Chat Admin</span>
                </a>

                <a href="https://bit.ly/43N1Prs" target="_blank"
                    class="flex items-center space-x-2 bg-[#FF6600] text-white px-4 py-2 rounded-full hover:bg-[#FF6600]/90 transition-all duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span>Download APK</span>
                </a>

                @auth
                    <a href="{{ url('/dashboard') }}"
                        class="block text-white hover:text-[#FF6600] transition-colors duration-300">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}"
                        class="block text-white hover:text-[#FF6600] transition-colors duration-300">
                        Masuk
                    </a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                            class="block w-full text-center bg-[#FF6600] text-white px-4 py-2 rounded-full hover:bg-[#FF6600]/90 transition-all duration-300 transform hover:scale-105">
                            Daftar
                        </a>
                    @endif
                @endauth
            </div>
        </div>
    </div>
</nav>
