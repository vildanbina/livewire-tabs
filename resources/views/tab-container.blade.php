<div>
    <form wire:submit.prevent="save">
        @include('livewire-tabs::tabs-header')
        <div class="container p-4">
            {{ $this->getCurrentTab() }}
        </div>

        {{ $this->tabFooter() }}
    </form>
</div>
