@props([
    'wizardNav' => false,
    'id' => 'modal',
    'size' => 'sm',
    'heading' => 'Default Heading',
    'has_loader' => false,
    'has_error' => true,
])
<div>
    <div id="{{ $id }}" wire:ignore.self class="modal fade bs-example-modal-xl" tabindex="-1" role="dialog"
        aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog  modal-{{ $size }}">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="myLargeModalLabel">{{ $heading }}</h5>
                    <button type="button" wire:click.prevent="_resetComponent" class="btn-close"
                        data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                @if ($has_loader)
                    <x-scrud::dynamics.progress-loader />
                @endif
                @if ($has_error)
                    {{-- @livewire('dynamics.session-errors') --}}
                @endif
                {{ $wizardNav }}
                <div class="modal-body">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
</div>
