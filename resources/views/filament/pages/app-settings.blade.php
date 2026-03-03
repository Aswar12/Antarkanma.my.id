<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}
        
        <div class="fi-form-actions mt-6">
            <div class="fi-flex fi-justify-end fi-gap-4">
                <x-filament::button type="submit" color="success">
                    <x-filament::icon icon="heroicon-o-check" class="w-4 h-4 mr-2" />
                    Save Settings
                </x-filament::button>
            </div>
        </div>
    </form>
</x-filament-panels::page>
