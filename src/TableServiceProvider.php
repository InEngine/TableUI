<?php

namespace InEngine\Table;

use InEngine\Table\Commands\TableCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class TableServiceProvider extends PackageServiceProvider
{
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
            ->hasMigration('create_tableui_table')
            ->hasCommand(TableCommand::class);
    }
}
