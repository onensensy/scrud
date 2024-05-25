@props(['compaire' => false])
<p>
    @if ($compaire['is_update'])
        <span class="text-{{ $compaire['is_compairable'] ? 'primary' : '' }}">{{ $compaire['old'] ?? '--' }}</span>
        <br>
        @if ($compaire['is_compairable'])
            <span class="text-danger">{{ $compaire['new'] ?? '--' }}</span>
        @endif
    @else
        <span class="">{{ $compaire['old'] ?? '--' }}</span>
    @endif
</p>
