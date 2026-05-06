{{--
    Multiselect for distinct string values (same UI as enum multiselect).

    @var string $wireModelPath  Livewire path, e.g. filterValues.user_name
    @var string $fieldId
    @var string|null $ariaLabelledby
    @var iterable<mixed, mixed> $acOpts  Distinct suggestion strings from the column
--}}
@props([
    'wireModelPath',
    'fieldId',
    'ariaLabelledby' => null,
    'acOpts' => [],
])
@php
    $distinctOpts = [];
    foreach ($acOpts as $_v) {
        $distinctOpts[(string) $_v] = (string) $_v;
    }
@endphp
@include('tableui::components.table.filter-enum-multiselect', [
    'wireModelPath' => $wireModelPath,
    'fieldId' => $fieldId,
    'enumOptions' => $distinctOpts,
    'ariaLabelledby' => $ariaLabelledby,
])
