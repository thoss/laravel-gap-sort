<?php

namespace Thoss\GapSort;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Thoss\GapSort\Resources\SortResourceRegistrar;

class GapSortServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('gap-sort')
            ->hasConfigFile();

        $this->resourceRegistrar();
    }

    protected function resourceRegistrar()
    {
        if (false === config('gap-sort.resource_registrar_with_sort')) {
            return;
        }

        $this->app->bind('Illuminate\Routing\ResourceRegistrar', function () {
            return new SortResourceRegistrar($this->app['router']);
        });
    }
}
