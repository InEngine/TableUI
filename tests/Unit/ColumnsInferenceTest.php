<?php

declare(strict_types=1);

use InEngine\TableUI\Columns;
use InEngine\TableUI\ColumnTypes\Complex\EmailColumn;
use InEngine\TableUI\ColumnTypes\Complex\MoneyColumn;
use InEngine\TableUI\ColumnTypes\Primitives\BooleanColumn;
use InEngine\TableUI\ColumnTypes\Primitives\IdColumn;
use InEngine\TableUI\ColumnTypes\Primitives\NumberColumn;
use InEngine\TableUI\ColumnTypes\Primitives\StringColumn;
use InEngine\TableUI\ColumnTypes\Primitives\TextColumn;
use InEngine\TableUI\ColumnTypes\Primitives\TimestampColumn;
use InEngine\TableUI\Rendering\ColumnRendererRegistry;
use InEngine\TableUI\Tests\Fixtures\SkuColumn;
use InEngine\TableUI\Tests\Fixtures\SkuColumnRenderer;

it('infers email for varchar-family columns when the name hints email and the sample validates', function (): void {
    $columns = Columns::fromAttributeKeys(
        ['contact_email' => 'string'],
        ['contact_email' => 'hi@example.com']
    );

    expect($columns->items()[0])->toBeInstanceOf(EmailColumn::class);
});

it('does not infer email for varchar-family columns when the sample is not a valid address', function (): void {
    $columns = Columns::fromAttributeKeys(
        ['contact_email' => 'string'],
        ['contact_email' => 'not-valid']
    );

    expect($columns->items()[0])->toBeInstanceOf(StringColumn::class);
});

it('infers string column when schema is varchar-family and no complex upgrade applies', function (): void {
    $columns = Columns::fromAttributeKeys(
        ['title' => 'string'],
        ['title' => 'Hello']
    );

    expect($columns->items()[0])->toBeInstanceOf(StringColumn::class);
});

it('infers email for virtual attributes using conservative legacy names', function (): void {
    $columns = Columns::fromAttributeKeys(
        ['billing_email' => null],
        ['billing_email' => 'any']
    );

    expect($columns->items()[0])->toBeInstanceOf(EmailColumn::class);
});

it('infers boolean columns for is_* keys', function (): void {
    $columns = Columns::fromAttributeKeys(
        ['is_active' => 'integer'],
        ['is_active' => 1]
    );

    expect($columns->items()[0])->toBeInstanceOf(BooleanColumn::class);
});

it('infers boolean for has_* when the sample is flag-like', function (): void {
    $columns = Columns::fromAttributeKeys(
        ['has_subscription' => 'integer'],
        ['has_subscription' => 1]
    );

    expect($columns->items()[0])->toBeInstanceOf(BooleanColumn::class);
});

it('infers number for integer counts when not boolean-like', function (): void {
    $columns = Columns::fromAttributeKeys(
        ['has_children' => 'integer'],
        ['has_children' => 3]
    );

    expect($columns->items()[0])->toBeInstanceOf(NumberColumn::class);
});

it('infers boolean when schema column type is boolean', function (): void {
    $columns = Columns::fromAttributeKeys(
        ['legacy_flag' => 'boolean'],
        ['legacy_flag' => 0]
    );

    expect($columns->items()[0])->toBeInstanceOf(BooleanColumn::class);
});

it('infers boolean for former_* keys when the sample is a tinyint-style 0/1 flag', function (): void {
    $columns = Columns::fromAttributeKeys(
        ['former_student' => 'tinyint'],
        ['former_student' => 1]
    );

    expect($columns->items()[0])->toBeInstanceOf(BooleanColumn::class);
});

it('treats Laravel MySQL tinyint as a numeric schema type for non-boolean names', function (): void {
    $columns = Columns::fromAttributeKeys(
        ['slot_index' => 'tinyint'],
        ['slot_index' => 5]
    );

    expect($columns->items()[0])->toBeInstanceOf(NumberColumn::class);
});

it('infers money for numeric schema types with monetary attribute names', function (): void {
    $columns = Columns::fromAttributeKeys(
        ['line_amount' => 'integer'],
        ['line_amount' => 4200]
    );

    expect($columns->items()[0])->toBeInstanceOf(MoneyColumn::class);
});

it('infers id for foreign-key integer columns', function (): void {
    $columns = Columns::fromAttributeKeys(
        ['order_id' => 'integer'],
        ['order_id' => 99]
    );

    expect($columns->items()[0])->toBeInstanceOf(IdColumn::class);
});

it('infers text for long-text schema types', function (): void {
    $columns = Columns::fromAttributeKeys(
        ['body' => 'text'],
        ['body' => 'hello']
    );

    expect($columns->items()[0])->toBeInstanceOf(TextColumn::class);
});

it('infers date-only timestamp columns when the schema type is date', function (): void {
    $columns = Columns::fromAttributeKeys(
        ['starts_on' => 'date'],
        ['starts_on' => '2026-04-29']
    );

    $column = $columns->items()[0];

    expect($column)->toBeInstanceOf(TimestampColumn::class)
        ->and($column->isDateOnly())->toBeTrue();
});

it('infers registered custom columns when ParticipatesInColumnInference matches', function (): void {
    config()->set('tableui.columns', [SkuColumn::class]);
    config()->set('tableui.renderers', [SkuColumnRenderer::class]);

    new ColumnRendererRegistry;

    $columns = Columns::fromAttributeKeys(
        ['internal_sku' => 'string'],
        ['internal_sku' => 'ABC-1']
    );

    expect($columns->items()[0])->toBeInstanceOf(SkuColumn::class);
});
