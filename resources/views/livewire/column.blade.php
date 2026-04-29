@php
    $tag = $header ? 'th' : 'td';
@endphp
<{{ $tag }}
    {{ $attributes->class($header ? ['table-ui__th'] : ['table-ui__td']) }}
    @if ($header)
        scope="col"
    @endif
>{{ $content }}</{{ $tag }}>
