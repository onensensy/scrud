<!-- resources/views/livewire/wizard-navigation.blade.php -->

@props([
    'hasIcons' => false,
    'steps',
    'step',
])
<ul class="nav nav-justified bg-light mb-4 nav-custom" role="tablist">
    @foreach ($steps as $index => $label)
        <li class="d-flex nav-item {{ $step == $index + 1 ? 'nav-item-custom' : '' }} waves-effect waves-light"
            role="presentation">
            <a wire:click="specificStep({{ $index + 1 }})"
                class="nav-link {{ $step >= $index + 1 ? 'active bg-primary' : '' }} d-flex justify-content-center align-items-center"
                role="tab" aria-selected="{{ $step == $index + 1 ? 'true' : 'false' }}">
                <div class="d-flex align-items-center">
                    @if ($hasIcons)
                        <span class="d-block d-sm-none"><i class="{{ $icons[$index] }}"></i></span>
                    @endif
                    <button
                        class="step-circle {{ $step >= $index + 1 ? 'bg-warning text-primary' : 'bg-primary text-white' }} d-flex align-items-center justify-content-center text-primary mx-2 ">
                        {{ $index + 1 }}
                    </button>
                    <span
                        class="d-none d-sm-block fw-bold {{ $step >= $index + 1 ? ' text-secondary' : 'text-primary' }} font-size-14">
                        {{ $label }}
                    </span>
                </div>
            </a>
        </li>
    @endforeach
</ul>
