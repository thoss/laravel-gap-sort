<?php

namespace Thoss\GapSort;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Thoss\GapSort\Resource\SortResourceRegistrar;

class GapSortServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-gap-sort')
            ->hasConfigFile();
    }

    protected function resourceRegistrar()
    {
        if (false === config('da-helper.resource_registrar')) {
            return;
        }

        $this->app->bind('Illuminate\Routing\ResourceRegistrar', function () {
            return new SortResourceRegistrar($this->app['router']);
        });
    }
}
