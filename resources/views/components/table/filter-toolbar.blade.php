{{-- Vertical stack of filters (legacy / alternate layout); table UI uses {@see filter-field} per column in {@see filter-row.blade.php}. --}}
@foreach ($filterDefinitions as $filterIndex => $def)
    @include('tableui::components.table.filter-field', ['def' => $def, 'filterIndex' => $filterIndex])
@endforeach
