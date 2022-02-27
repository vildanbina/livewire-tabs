<?php

namespace Vildanbina\LivewireTabs\Concerns;

use Vildanbina\LivewireTabs\Contracts\TabForm;

trait BelongsToLivewire
{
    protected TabForm $livewire;

    public function setLivewire(TabForm $livewire): static
    {
        $this->livewire = $livewire;

        return $this;
    }

    public function getLivewire(): TabForm
    {
        return $this->livewire;
    }
}
