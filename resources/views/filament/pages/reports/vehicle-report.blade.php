<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">
                اختيار المركبة
            </x-slot>
            <x-filament::input.wrapper>
                <select wire:model.live="selectedVehicle" class="w-full rounded-lg border-gray-300">
                    <option value="">-- اختر مركبة --</option>
                    @foreach(\App\Models\Vehicle::all() as $vehicle)
                        <option value="{{ $vehicle->id }}">{{ $vehicle->رقم_اللوحة }} - {{ $vehicle->الصنف }}</option>
                    @endforeach
                </select>
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
