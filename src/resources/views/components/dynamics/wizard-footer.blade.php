@props([
    'step' => 1,
    'total_steps',
    'sent' => false,
])
<div>
    <div class="d-flex justify-content-end">
        @if (!$sent)
            @if ($step > 1)
                <button class="theme-btn btn-three mr-2" wire:click='previousStep'>Previous</button>
            @endif
            @if ($step < $total_steps)
                @if (Session::has('error') || Session::has('success'))
                @else
                    <button class="theme-btn btn-one mx-4" wire:click='nextStep'>Next</button>
                @endif
            @endif
            @if ($step == $total_steps)
                @if (Session::has('error') || Session::has('success'))
                    <button class="{{ Session::has('error') ? 'btn btn-danger' : 'theme-btn btn-one' }}"
                        disabled">{{ Session::has('error') ? Session::get('error') : Session::get('success') }}</button>
                @else
                    <div wire:loading wire:target='submit'>
                        <x-scrud::dynamics.loader-button />
                    </div>
                    <button wire:loading.remove wire:target='submit' wire:click='submit' class="theme-btn btn-one mx-2"
                        type="submit">Submit</button>
                @endif
            @endif

        @endif
        {{-- <button class="theme-btn btn-three mr-2" wire:click='previousStep'>Previous</button> --}}
    </div>

</div>
