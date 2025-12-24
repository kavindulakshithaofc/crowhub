<x-filament-panels::page>
    <form wire:submit.prevent="send" class="space-y-6">
        {{ $this->form }}

        <x-filament::button type="submit" icon="heroicon-o-paper-airplane">
            Send SMS
        </x-filament::button>
    </form>
</x-filament-panels::page>
