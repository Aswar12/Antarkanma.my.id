<x-filament-panels::page class="filament-dashboard-page">
    <!-- Stats Overview -->
    <div class="mb-8">
        {{ $this->getHeaderWidgets()[0] }}
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 lg:gap-8 mb-8">
        <!-- Line Chart -->
        <div class="filament-widget bg-white dark:bg-gray-800 shadow rounded-xl">
            {{ $this->getHeaderWidgets()[1] }}
        </div>
        <!-- Pie Chart -->
        <div class="filament-widget bg-white dark:bg-gray-800 shadow rounded-xl">
            {{ $this->getHeaderWidgets()[2] }}
        </div>
    </div>

    <!-- Tables Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-8">
        @foreach ($this->getFooterWidgets() as $widget)
            <div class="filament-widget bg-white dark:bg-gray-800 shadow rounded-xl">
                {{ $widget }}
            </div>
        @endforeach
    </div>
</x-filament-panels::page>
