@props([
    'hasLoader' => false,
    'customProcessmessage' => null, //Custom Processing Message
    'color' => 'primary',
    'hasCustom' => false,
    'hasIcon' => false,
    'icon',
    'hasSubmit' => true,
    'action' => 'submit',
    'text',
    'isOutlined' => false,
    'size' => false,
    'isDisabled' => false,
    'isAppended' => true,
    'key' => null,
])

<div class="{{ $isAppended ? 'input-group-append' : '' }}">

    @if ($hasLoader)
        <div wire:loading wire:target="{{ $action }}">
            @if ($customProcessmessage != null)
                <x-scrud::dynamics.loader-button :message="$customProcessmessage" />
            @else
                <x-scrud::dynamics.loader-button />
            @endif
        </div>
    @endif

    @if ($hasCustom)
        {{ $slot }}
    @else
        @if ($hasSubmit)
            <button wire:loading.remove @disabled($isDisabled) wire:target="{{ $action }}"
                @if (!is_null($key)) wire:key="{{ $key }}" @endif
                class="btn {{ $size == 'sm' ? 'btn-sm' : '' }} btn-{{ $isOutlined ? 'outline-' : '' }}{{ $color }} {{ $isAppended ? '' : 'mx-4' }}"
                wire:click="{{ $action }}">
                @if ($hasIcon)
                    <i class="{{ $icon }}"></i>
                @endif
                {{ $text }}
            </button>
        @endif
    @endif
</div>
