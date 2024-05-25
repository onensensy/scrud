@props([
    'hasWizard' => false,
    'step' => 1,
    'total_steps' => 1,
    'align' => 'end',
    'action' => 'submit',
    'showLoader' => true,
    'hasSubmit' => true,
    'backToModal',
    'submit' => 'Submit',
    'hasCustom' => false,
    'hasButtonGroup' => true,
    'hasOr' => true,
])
<div class="modal-footer m-0">
    @if ($hasWizard)
        <div class="d-flex justify-content-{{ $align }}">
            @if ($hasButtonGroup)
                <div class="btn-group @if ($hasOr) btn-group-example @endif" role="group"
                    aria-label="Button group">
            @endif

            @if ($hasCustom)
                {{ $slot }}
            @endif

            @if ($step > 1)
                <button wire:loading.remove wire:target="previousStep"
                    class="btn btn-outline-primary @if ($hasOr) w-sm @endif"
                    wire:click='previousStep' aria-label="Previous">Previous</button>
                <div wire:loading wire:target="previousStep">
                    <x-scrud::dynamics.loader-button color="primary" />
                </div>
            @endif

            @if ($step < $total_steps)
                <button wire:loading.remove wire:target="nextStep"
                    class="btn btn-primary @if ($hasOr) w-sm @endif" wire:click='nextStep'
                    aria-label="Next">Next</button>
                <div wire:loading wire:target="nextStep">
                    <x-scrud::dynamics.loader-button />
                </div>
            @endif

            @if ($step == $total_steps)
                <button wire:loading.remove wire:target="submit"
                    class="btn btn-primary @if ($hasOr) w-sm @endif" wire:click='submit'
                    aria-label="Submit">Submit</button>
                <div wire:loading wire:target="submit">
                    <x-scrud::dynamics.loader-button />
                </div>
            @endif

            @if ($hasButtonGroup)
        </div>
    @endif
</div>
@else
<div class="d-flex justify-content-{{ $align }}">
    @if ($hasButtonGroup)
        <div class="btn-group @if ($hasOr) btn-group-example @endif" role="group"
            aria-label="Button group">
    @endif
    <button
        class="btn btn-outline-primary mr-2 @if ($hasOr) w-sm @endif
    "@if (isset($backToModal)) wire:click.prevent='{{ $backToModal }}'@else wire:click.prevent='_resetComponent' data-bs-dismiss="modal" aria-label="Close" @endif>Cancel</button>
    @if ($showLoader)
        <div wire:loading wire:target="{{ $action }}">
            <x-scrud::dynamics.loader-button />
        </div>
    @endif
    @if (!$hasButtonGroup)
        @if ($hasCustom)
            {{ $slot }}
        @else
            @if ($hasSubmit)
                <button wire:loading.remove wire:target="{{ $action }}"
                    class="btn btn-primary mx-4 @if ($hasOr) w-sm @endif"
                    wire:click="{{ $action }}">{{ $submit }}</button>
            @endif
        @endif
    @endif
    @if ($hasButtonGroup)
        @if ($hasSubmit)
            <button wire:loading.remove wire:target="{{ $action }}"
                class="btn btn-primary @if ($hasOr) w-sm @endif"
                wire:click="{{ $action }}">{{ $submit }}</button>
        @endif
    @endif
    @if ($hasButtonGroup)
</div>
@endif
</div>
@endif
</div>
