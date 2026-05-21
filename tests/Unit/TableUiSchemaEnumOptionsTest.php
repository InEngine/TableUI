<?php

declare(strict_types=1);

use InEngine\TableUI\Support\TableUiSchemaEnumOptions;

it('parses mysql enum type definitions into value label maps', function (): void {
    $reflection = new ReflectionClass(TableUiSchemaEnumOptions::class);
    $method = $reflection->getMethod('parseEnumTypeDefinition');
    $method->setAccessible(true);

    /** @var array<string, string>|null $options */
    $options = $method->invoke(null, "enum('male','female')");

    expect($options)->toMatchArray([
        'male' => 'Male',
        'female' => 'Female',
    ]);
});
