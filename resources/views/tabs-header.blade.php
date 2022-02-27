<div class="border-b border-gray-200 dark:border-gray-700">
    <ul class="flex flex-wrap -mb-px">
        @foreach($tabInstances as $tabInstance)
            @include('livewire-tabs::tab-header')
        @endforeach
    </ul>
</div>
