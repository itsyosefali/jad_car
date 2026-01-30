<x-filament-panels::page>
    <div class="space-y-6">
        @if($this->getHeaderWidgets())
            <x-filament-widgets::widgets
                :widgets="$this->getHeaderWidgets()"
                :columns="$this->getHeaderWidgetsColumns()"
            />
        @endif

        {{ $this->table }}
    </div>
</x-filament-panels::page>
