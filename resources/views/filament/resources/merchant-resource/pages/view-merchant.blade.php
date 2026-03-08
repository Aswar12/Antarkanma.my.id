<x-filament-panels::page>
    <x-filament::section>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Logo -->
            <div class="flex items-center gap-4">
                @if($record->logo)
                    <img src="{{ $record->logo_url }}" alt="{{ $record->name }}" class="w-16 h-16 rounded object-cover">
                @else
                    <div class="w-16 h-16 rounded bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                        <x-heroicon-o-building-storefront class="w-10 h-10 text-gray-400" />
                    </div>
                @endif
                <div>
                    <h2 class="text-xl font-bold">{{ $record->name }}</h2>
                    <p class="text-sm text-gray-500">{{ $record->owner->name ?? '-' }}</p>
                </div>
            </div>

            <!-- Status -->
            <div class="flex items-center justify-end">
                <x-filament::badge :color="$record->status === 'active' ? 'success' : 'danger'">
                    {{ $record->status === 'active' ? 'Active' : 'Inactive' }}
                </x-filament::badge>
            </div>
        </div>
    </x-filament::section>

    <!-- Merchant Details -->
    <x-filament::section>
        <x-slot name="heading">
            Merchant Information
        </x-slot>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Name</label>
                <p class="mt-1">{{ $record->name }}</p>
            </div>

            <div>
                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Phone Number</label>
                <p class="mt-1">{{ $record->phone_number ?? '-' }}</p>
            </div>

            <div class="md:col-span-2">
                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Address</label>
                <p class="mt-1">{{ $record->address }}</p>
            </div>

            @if($record->description)
                <div class="md:col-span-2">
                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Description</label>
                    <p class="mt-1">{{ $record->description }}</p>
                </div>
            @endif

            <div>
                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Latitude</label>
                <p class="mt-1">{{ $record->latitude }}</p>
            </div>

            <div>
                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Longitude</label>
                <p class="mt-1">{{ $record->longitude }}</p>
            </div>
        </div>
    </x-filament::section>

    <!-- Operating Hours -->
    <x-filament::section>
        <x-slot name="heading">
            Operating Hours
        </x-slot>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Opening Time</label>
                <p class="mt-1">{{ $record->opening_time?->format('H:i') ?? '-' }}</p>
            </div>

            <div>
                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Closing Time</label>
                <p class="mt-1">{{ $record->closing_time?->format('H:i') ?? '-' }}</p>
            </div>

            <div>
                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Operating Days</label>
                <p class="mt-1">{{ $record->operating_days ? implode(', ', $record->operating_days) : '-' }}</p>
            </div>
        </div>
    </x-filament::section>

    <!-- QRIS -->
    @if($record->qris_url)
        <x-filament::section>
            <x-slot name="heading">
                QRIS Payment
            </x-slot>

            <div class="flex justify-center">
                <img src="{{ $record->qris_url_full }}" alt="QRIS" class="max-w-[150px] h-auto rounded shadow">
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>
