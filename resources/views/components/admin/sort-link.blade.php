{{--
    Komponen header kolom tabel yang bisa di-sort.
    Seluruh parameter query (search, filter, dsb) tetap dibawa saat berpindah sort.
--}}
@props([
    'column',
    'label',
    'route',
    'sort' => null,
    'direction' => 'asc',
])

@php
    $isActive = $sort === $column;
    $nextDirection = $isActive && $direction === 'asc' ? 'desc' : 'asc';
    $url = route($route, array_merge(request()->query(), [
        'sort' => $column,
        'direction' => $nextDirection,
    ]));
@endphp

<a href="{{ $url }}" class="text-dark text-nowrap">
    {{ $label }}
    @if ($isActive)
        <i class="fas fa-sort-{{ $direction === 'asc' ? 'up' : 'down' }} text-primary"></i>
    @else
        <i class="fas fa-sort text-muted"></i>
    @endif
</a>
