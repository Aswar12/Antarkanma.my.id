<x-filament-panels::page>
    <x-filament-widgets::widgets
        :widgets="$this->getVisibleHeaderWidgets()"
        :columns="$this->getHeaderWidgetsColumns()"
    />

    <div class="fi-page-content">
        <x-filament-widgets::widgets
            :widgets="$this->getVisibleFooterWidgets()"
            :columns="$this->getFooterWidgetsColumns()"
        />
    </div>
</x-filament-panels::page>
