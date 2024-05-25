@props(['message', 'placement' => 'top', 'color' => '', 'icon' => 'fa fa-info-circle'])
<span class="btn p-0" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $message }}">
    <i class="{{ $icon }} text-{{ $color }}"></i>
</span>
{{-- <button type="button" class="btn btn-lg btn-danger" data-bs-toggle="popover" data-bs-title="Popover title"
    data-bs-content="And here's some amazing content. It's very engaging. Right?">Click to toggle popover</button> --}}