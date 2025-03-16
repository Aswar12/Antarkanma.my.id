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

            <div class="px-3 py-2 space-y-2">
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
