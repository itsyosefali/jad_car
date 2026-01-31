<x-filament-panels::page>
    <div class="space-y-6">
        @if ($this->getHeaderWidgets())
            <x-filament-widgets::widgets
                :widgets="$this->getHeaderWidgets()"
                :columns="$this->getHeaderWidgetsColumns()"
            />
        @endif
    </div>
</x-filament-panels::page>
