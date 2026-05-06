<?php

namespace InEngine\TableUI\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use InEngine\TableUI\Rendering\ColumnRendererRegistry;
use InEngine\TableUI\TableServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'InEngine\\TableUI\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );

        config()->set('tableui.columns', []);
        config()->set('tableui.renderers', []);
        config()->set('tableui.actions', []);
        config()->set('tableui.filter_definitions', []);

        $tableuiDefaults = require dirname(__DIR__).'/config/tableui.php';
        config()->set('tableui.theme', $tableuiDefaults['theme'] ?? []);
        config()->set('tableui.column_types', $tableuiDefaults['column_types']);
        config()->set('tableui.scrollbars', $tableuiDefaults['scrollbars'] ?? [
            'horizontal' => 'auto',
            'vertical' => 'auto',
        ]);
        config()->set('tableui.pagination', $tableuiDefaults['pagination'] ?? 25);

        $this->app->forgetInstance(ColumnRendererRegistry::class);
    }

    protected function getPackageProviders($app)
    {
        return [
            LivewireServiceProvider::class,
            TableServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
        config()->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
    }
}
