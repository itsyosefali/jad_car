<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">
                اختيار الشهر
            </x-slot>
            <x-filament::input.wrapper>
                <input type="month" wire:model.live="selectedMonth" class="w-full rounded-lg border-gray-300" />
            </x-filament::input.wrapper>
        </x-filament::section>

        @if($this->getHeaderWidgets())
            <x-filament-widgets::widgets
                :widgets="$this->getHeaderWidgets()"
                :columns="$this->getHeaderWidgetsColumns()"
            />
        @endif

        {{ $this->table }}
    </div>
</x-filament-panels::page>
