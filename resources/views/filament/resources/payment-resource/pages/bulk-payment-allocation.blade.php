<x-filament-panels::page>
    <form wire:submit="submit">
        {{ $this->form }}

        <x-filament-actions::modals />
    </form>
</x-filament-panels::page>
