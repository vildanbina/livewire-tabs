<?php

namespace Vildanbina\LivewireTabs;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LivewireTabsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('livewire-tabs')
            ->hasConfigFile()
            ->hasViews();
    }
}
