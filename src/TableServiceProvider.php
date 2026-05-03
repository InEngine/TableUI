<?php

namespace InEngine\TableUI;

use InEngine\TableUI\Commands\TableCommand;
use InEngine\TableUI\Livewire\Column as ColumnLivewireComponent;
use InEngine\TableUI\Livewire\TableView as TableLivewireComponent;
use InEngine\TableUI\Rendering\ColumnRendererRegistry;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class TableServiceProvider extends PackageServiceProvider
{
    public function packageRegistered(): void
    {
        $this->app->singleton(ColumnRendererRegistry::class, fn (): ColumnRendererRegistry => new ColumnRendererRegistry);
    }

    /**
     * @return void
     */
    public function packageBooted(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'tableui');

        $this->publishes([
            __DIR__.'/../resources/css/tableui.css' => resource_path('css/vendor/tableui.css'),
        ], 'tableui-css');

        if (class_exists(Livewire::class)) {
            Livewire::component('tableui.table', TableLivewireComponent::class);
            Livewire::component('tableui.column', ColumnLivewireComponent::class);
        }
    }

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('tableui')
            ->hasConfigFile()
            ->hasViews()
            ->hasCommand(TableCommand::class);
    }
}
