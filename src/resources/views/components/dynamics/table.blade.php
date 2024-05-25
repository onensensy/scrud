@props([
    'list' => [],
    'type' => 'list',
    'align' => 'middle',
    'onclick' => 'triggerAllModals',
    'selectAllId' => 'selectAllCheck',
    'paginate' => true,
])


<table class="table table-sm align-{{ $align }} mb-0">
    <thead class="table-light">
        {{ $thead }}
    </thead>
    <tbody class="font-size-12">
        {{ $tbody }}
    </tbody>
</table>
@if ($paginate)
    <x-scrud::dynamics.lists.list-footer :items="$list" />
@endif
