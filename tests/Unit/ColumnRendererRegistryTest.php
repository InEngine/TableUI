<?php

declare(strict_types=1);

use InEngine\TableUI\ColumnTypes\Column;
use InEngine\TableUI\ColumnTypes\Complex\EmailColumn;
use InEngine\TableUI\ColumnTypes\Complex\MoneyColumn;
use InEngine\TableUI\ColumnTypes\Complex\PhoneColumn;
use InEngine\TableUI\ColumnTypes\Primitives\BooleanColumn;
use InEngine\TableUI\ColumnTypes\Primitives\EnumColumn;
use InEngine\TableUI\ColumnTypes\Primitives\IdColumn;
use InEngine\TableUI\ColumnTypes\Primitives\NumberColumn;
use InEngine\TableUI\ColumnTypes\Primitives\StringColumn;
use InEngine\TableUI\ColumnTypes\Primitives\TextColumn;
use InEngine\TableUI\ColumnTypes\Primitives\TimestampColumn;
use InEngine\TableUI\Rendering\BooleanColumnRenderer;
use InEngine\TableUI\Rendering\ColumnRendererRegistry;
use InEngine\TableUI\Rendering\EmailColumnRenderer;
use InEngine\TableUI\Rendering\EnumColumnRenderer;
use InEngine\TableUI\Rendering\GenericColumnRenderer;
use InEngine\TableUI\Rendering\IdColumnRenderer;
use InEngine\TableUI\Rendering\MoneyColumnRenderer;
use InEngine\TableUI\Rendering\NumberColumnRenderer;
use InEngine\TableUI\Rendering\PhoneColumnRenderer;
use InEngine\TableUI\Rendering\StringColumnRenderer;
use InEngine\TableUI\Rendering\TextColumnRenderer;
use InEngine\TableUI\Rendering\TimestampColumnRenderer;
use InEngine\TableUI\Tests\Fixtures\SkuColumn;
use InEngine\TableUI\Tests\Fixtures\SkuColumnRenderer;

it('resolves renderers by column concrete type', function (): void {
    $registry = new ColumnRendererRegistry;

    expect($registry->rendererFor(new Column('x')))->toBeInstanceOf(GenericColumnRenderer::class)
        ->and($registry->rendererFor(new BooleanColumn('is_active')))->toBeInstanceOf(BooleanColumnRenderer::class)
        ->and($registry->rendererFor(new EnumColumn('status')))->toBeInstanceOf(EnumColumnRenderer::class)
        ->and($registry->rendererFor(new StringColumn('name')))->toBeInstanceOf(StringColumnRenderer::class)
        ->and($registry->rendererFor(new TextColumn('body')))->toBeInstanceOf(TextColumnRenderer::class)
        ->and($registry->rendererFor(new TimestampColumn('created_at')))->toBeInstanceOf(TimestampColumnRenderer::class)
        ->and($registry->rendererFor(new NumberColumn('qty')))->toBeInstanceOf(NumberColumnRenderer::class)
        ->and($registry->rendererFor(new IdColumn('user_id')))->toBeInstanceOf(IdColumnRenderer::class)
        ->and($registry->rendererFor(new MoneyColumn('total')))->toBeInstanceOf(MoneyColumnRenderer::class)
        ->and($registry->rendererFor(new EmailColumn('email')))->toBeInstanceOf(EmailColumnRenderer::class)
        ->and($registry->rendererFor(new PhoneColumn('phone')))->toBeInstanceOf(PhoneColumnRenderer::class);
});

it('resolves config-registered column renderers', function (): void {
    config()->set('tableui.columns', [SkuColumn::class]);
    config()->set('tableui.renderers', [SkuColumnRenderer::class]);

    $registry = new ColumnRendererRegistry;

    expect($registry->rendererFor(new SkuColumn('internal_sku')))->toBeInstanceOf(SkuColumnRenderer::class);
});

it('rejects renderers that do not extend the abstract renderer base', function (): void {
    config()->set('tableui.renderers', [stdClass::class]);

    expect(fn () => new ColumnRendererRegistry)->toThrow(InvalidArgumentException::class);
});

it('rejects invalid column_types.boolean shape', function (): void {
    config()->set('tableui.column_types', [
        'boolean' => 'not-an-array',
    ]);

    expect(fn () => new ColumnRendererRegistry)->toThrow(InvalidArgumentException::class);
});

it('rejects non-boolean column_types.boolean.show_false', function (): void {
    config()->set('tableui.column_types', [
        'boolean' => [
            'show_false' => 'yes',
            'true' => ['icon' => 'check', 'color' => 'green-600'],
            'false' => ['icon' => 'x-mark', 'color' => 'red-600'],
        ],
    ]);

    expect(fn () => new ColumnRendererRegistry)->toThrow(InvalidArgumentException::class, 'show_false');
});
