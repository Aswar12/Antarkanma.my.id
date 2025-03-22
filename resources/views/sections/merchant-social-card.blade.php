<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 justify-items-center">
    @foreach ($merchants as $merchant)
        <!-- Instagram-Optimized Card -->
        <div id="merchant-card-{{ $merchant->id }}"
            class="relative w-[300px] h-[300px] mx-auto group perspective">

            <!-- Card Inner Container with 3D Effect -->
            <div class="relative w-full h-full transition-all duration-700 transform-style-preserve-3d group-hover:rotate-y-12">
                <!-- Card Front -->
                <div class="absolute inset-0 bg-gradient-to-br from-[#020238] via-[#1a1a4b] to-[#FF6600] rounded-2xl overflow-hidden shadow-2xl">
                    <!-- Animated Gradient Border -->
                    <div class="absolute inset-[2px] bg-gradient-to-br from-[#020238] to-[#FF6600] rounded-2xl overflow-hidden">
                        <!-- Animated Background Pattern -->
                        <div class="absolute inset-0 opacity-30">
                            <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'0.4\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E\')] animate-pattern"></div>
                        </div>

                        <!-- Glass Effect Overlay -->
                        <div class="absolute inset-0 bg-white/5 backdrop-blur-[2px]"></div>

                        <!-- Content Container -->
                        <div class="relative h-full flex flex-col items-center p-6">
                            <!-- Merchant Logo with Glow Effect -->
                            <div class="relative -mt-2 group-hover:scale-105 transition-transform duration-500">
                                <!-- Animated Glow -->
                                <div class="absolute inset-0 bg-gradient-to-r from-[#FF6600] via-[#020238] to-[#FF6600] rounded-full blur-2xl opacity-75 animate-glow"></div>
                                <!-- Logo Container -->
                                <div class="relative w-36 h-36 rounded-full border-4 border-white/80 overflow-hidden shadow-[0_0_30px_rgba(255,102,0,0.3)] transform transition-all duration-700 hover:scale-110">
                                    <img src="{{ $merchant->logo_url }}"
                                         alt="{{ $merchant->name }}"
                                         class="w-full h-full object-cover"
                                         onerror="this.src='https://dev.antarkanmaa.my.id/images/default-merchant.png'">
                                </div>
                            </div>

                            <!-- Merchant Info -->
                            <div class="mt-6 text-center z-10 space-y-4">
                                <!-- Name with Gradient Text -->
                                <h3 class="text-2xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-white via-white to-white/80 tracking-wide">
                                    {{ $merchant->name }}
                                </h3>

                                <!-- Stats Container with Glass Effect -->
                                <div class="flex items-center justify-center gap-4">
                                    <div class="flex items-center bg-white/10 backdrop-blur-md rounded-xl px-4 py-2 shadow-lg">
                                        <svg class="w-5 h-5 text-[#FF6600]" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                        <span class="ml-2 font-bold text-white text-lg">{{ number_format($merchant->rating ?? 0, 1) }}</span>
                                    </div>
                                    <div class="bg-white/10 backdrop-blur-md rounded-xl px-4 py-2 shadow-lg">
                                        <span class="text-[#FF6600] font-bold text-lg">{{ $merchant->total_orders ?? 0 }}</span>
                                        <span class="text-white/90">Orders</span>
                                    </div>
                                </div>

                                <!-- Address with Icon -->
                                <div class="flex items-center justify-center gap-2 text-white/90 px-4 py-2 bg-white/10 backdrop-blur-md rounded-xl">
                                    <svg class="w-5 h-5 text-[#FF6600] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span class="truncate font-medium">{{ $merchant->address }}</span>
                                </div>
                            </div>

                            <!-- Enhanced Footer -->
                            <div class="absolute bottom-0 left-0 right-0 h-14 bg-gradient-to-r from-[#020238] to-[#FF6600]/80 backdrop-blur-sm flex items-center justify-center">
                                <div class="flex items-center gap-3">
                                    <img src="https://is3.cloudhost.id/antarkanma/merchants/logos/merchant-1-1742271423.png" alt="Antarkanma Logo" class="h-8 w-8 rounded-full object-cover ring-2 ring-white/50">
                                    <span class="text-lg font-bold text-white tracking-wider">Antarkanma</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Floating Download Button -->
            <button onclick="downloadCard({{ $merchant->id }})"
                class="absolute -bottom-4 left-1/2 transform -translate-x-1/2 p-2.5 rounded-full bg-gradient-to-r from-[#020238] to-[#FF6600] hover:from-[#FF6600] hover:to-[#020238] transition-all duration-300 shadow-lg group">
                <div class="bg-white rounded-full p-2 transform transition-transform duration-300 group-hover:scale-110">
                    <svg class="w-4 h-4 text-[#FF6600]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                </div>
            </button>
        </div>
    @endforeach
</div>

<!-- Scripts -->
<script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
<script>
    async function downloadCard(merchantId) {
        const card = document.getElementById(`merchant-card-${merchantId}`);

        try {
            // Show loading state
            const button = document.querySelector(`button[onclick="downloadCard(${merchantId})"]`);
            const originalText = button.innerHTML;
            button.innerHTML = '<div class="animate-spin h-4 w-4 border-2 border-[#FF6600] border-t-transparent rounded-full"></div>';
            button.disabled = true;

            // Create a clone of the card for processing
            const clone = card.cloneNode(true);
            clone.style.transform = 'none';

            // Set Instagram dimensions (1080x1080)
            clone.style.width = '1080px';
            clone.style.height = '1080px';
            clone.classList.remove('w-[300px]', 'h-[300px]');

            // Scale up the content
            const contentScale = 1080 / 300;
            const content = clone.querySelector('.relative.h-full.flex');
            if (content) {
                content.style.transform = `scale(${contentScale})`;
                content.style.transformOrigin = 'center';
            }

            // Position off-screen
            clone.style.position = 'fixed';
            clone.style.left = '-9999px';
            clone.style.top = '0';
            document.body.appendChild(clone);

            // Wait for images to load
            const images = clone.getElementsByTagName('img');
            await Promise.all([...images].map(img => {
                if (img.complete) return Promise.resolve();
                return new Promise(resolve => {
                    img.onload = resolve;
                    img.onerror = resolve;
                });
            }));

            // Capture with html2canvas
            const canvas = await html2canvas(clone, {
                width: 1080,
                height: 1080,
                scale: 2,
                useCORS: true,
                backgroundColor: null,
                logging: false
            });

            // Convert to JPG
            const dataUrl = canvas.toDataURL('image/jpeg', 1.0);

            // Create download link
            const link = document.createElement('a');
            link.download = `merchant-card-${merchantId}.jpg`;
            link.href = dataUrl;
            link.click();

            // Clean up
            document.body.removeChild(clone);

            // Restore button state
            button.innerHTML = originalText;
            button.disabled = false;
        } catch (error) {
            console.error('Error generating image:', error);
            alert('Failed to generate image. Please try again.');

            // Restore button state on error
            const button = document.querySelector(`button[onclick="downloadCard(${merchantId})"]`);
            button.innerHTML = originalText;
            button.disabled = false;
        }
    }
</script>

<style>
    @keyframes pattern {
        0% { background-position: 0 0; }
        100% { background-position: 60px 60px; }
    }
    .animate-pattern {
        animation: pattern 20s linear infinite;
    }
    @keyframes glow {
        0%, 100% { opacity: 0.5; }
        50% { opacity: 0.8; }
    }
    .animate-glow {
        animation: glow 2s ease-in-out infinite;
    }
    .perspective {
        perspective: 1000px;
    }
    .transform-style-preserve-3d {
        transform-style: preserve-3d;
    }
</style>
