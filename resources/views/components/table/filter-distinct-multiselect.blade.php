{{--
    Multiselect for distinct string values: typeahead input (Enter adds a needle) + checkbox-style list (OR semantics).

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
    'inputType' => 'search',
    'inputmode' => null,
])
@include('tableui::components.table.filter-typeahead-multiselect', [
    'wireModelPath' => $wireModelPath,
    'fieldId' => $fieldId,
    'ariaLabelledby' => $ariaLabelledby,
    'acOpts' => $acOpts,
    'inputType' => $inputType,
    'inputmode' => $inputmode,
])
