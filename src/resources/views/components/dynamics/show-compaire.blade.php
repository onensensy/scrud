@props(['compaire' => false, 'isBool' => false])
<p>
    @if ($compaire['is_update'])
        <span class="text-{{ $compaire['is_compairable'] ? 'primary' : '' }}">{{ $compaire['old'] ?? '--' }}</span>
        <br>
        @if ($compaire['is_compairable'])
            <span class="text-danger">{{ $isBool ? bool_output($compaire['new']) : $compaire['new'] ?? '--' }}</span>
        @endif
    @else
        <span class="">{{ $isBool ? bool_output($compaire['old']) : $compaire['old'] ?? '--' }}</span>
    @endif
</p>
