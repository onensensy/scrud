@props([
    // 'name',
    'model',
    'binding' => 'live',
    'placeholder' => null,
    'type',
    'hasLabel' => true,
    'label',
    'col' => 6,
    'class' => 'px-2',
    'required' => false,
    'option' => [],
    'optionCall' => null,
    'optionIsArray' => false,
    'fileMultiple' => false,
    'checkbox_inline' => false,
    'checkbox_label' => true,
    'checkbox_text' => 'Yes',
    'checked' => false,
    'checkbox_type' => 'checkbox', # toggle | checkbox
    'checkbox_size' => 'md', # toggle | checkbox
    'textarea_cols' => 2,
    'textarea_rows' => 1,
    'append' => null,
    'info' => null,
    'display' => 'row',
    'gap' => '4',
    'driver' => 'session', # session | livewire
    'value' => null,
    'disabled' => false,
    'preview' => null,
    'previewLimit' => false,
    'previewMaxWidth' => '300px',
    'previewMaxHeight' => '300px',
    'select2' => false,
    'formSwitch' => false,
])

@php
    $label = $hasLabel ? $label : '';
    $pre = $type == 'select' ? 'Select' : 'Enter';

    $placeholder = isset($placeholder) ? $placeholder : $pre . ' ' . $label;
@endphp

<div class="col-md-{{ $col }} {{ $class }}">
    <div class="form-group">
        @if ($hasLabel)
            <label class="mb-0 p-0">{{ $label }}</label>
        @endif
        @if (!is_null($info))
            <x-dynamics.tooltips message="{{ $info }}" />
        @endif
        @if (!is_null($append))
            <div class="input-group">
        @endif
        {{-- Select Inputs --}}
        @if ($type === 'select')
            @if ($select2)
                <div wire:ignore>
            @endif
            <select id="select2-{{ $model }}" wire:model.{{ $binding }}="{{ $model }}"
                @disabled($disabled) placeholder="{{ $placeholder }}" id="{{ $model }}"
                name="{{ $model }}" class="form-select select2">
                <option value="">{{ $placeholder ?? 'Select an option' }}</option>
                @foreach ($option as $opt)
                    <option value="{{ $optionIsArray ? $opt : $opt->id }}"
                        @if (($optionIsArray ? $opt : $opt->id) === $value) selected @endif>
                        @if (!$optionIsArray)
                            @if (is_null($optionCall))
                                {{ $opt->name }}
                            @else
                                {{ $opt->$optionCall }}
                            @endif
                        @else
                            {{ $opt }}
                        @endif
                    </option>
                @endforeach
            </select>
            @if ($select2)
    </div>
    @endif
    @if ($select2)
        @script
            <script>
                $(document).ready(function() {
                    $(".select2").select2();

                    $("#select2-{{ $model }}").on('change', function() {
                        let data = $(this).val();
                        // console.log("{{ $model }}");
                        $wire.set('{{ $model }}', data);
                    });
                });
            </script>
        @endscript
    @endif
@elseif ($type === 'checkbox')
    @if (!$checkbox_inline)
        <br />
    @endif
    @if ($checkbox_type === 'toggle')
        <div class="form-check form-switch form-switch-{{ $checkbox_size }} mb-3" dir="ltr">
            <input class="form-check-input" name="{{ $model }}" type="{{ $type }}"
                id="{{ $model }}" wire:model.{{ $binding }}="{{ $model }}"
                @disabled($disabled) @if ($checked) checked @endif>
            <label class="form-check-label" for="{{ $model }}">{{ $checkbox_text }}</label>
        </div>
    @else
        <input name="{{ $model }}" type="{{ $type }}"
            wire:model.{{ $binding }}="{{ $model }}" @disabled($disabled)
            id="{{ $model }}" class="form-check-input" value="{{ old($model) ?? $value }}"
            @if ($checked) checked @endif />
        @if ($checkbox_text)
            <label for="{{ $model }}" class="form-check-label">{{ $checkbox_text }}</label>
        @endif
    @endif
@elseif($type === 'file')
    @if (!is_null($preview))
        <i class="fa fa-check-circle text-success"></i>
        @php
            $ext = $preview->getClientOriginalExtension();
        @endphp
        @if (in_array($ext, ['png', 'jpeg', 'jpg']))
            <img src="{{ $preview->temporaryUrl() }}" alt="Preview Image" class="img-fluid"
                @if ($previewLimit) style="max-width: {{ $previewMaxWidth }}; max-height: {{ $previewMaxHeight }}; @endif">
        @else
        @endif
    @endif
    <x-dynamics.filepond wire:model='{{ $model }}' />
@elseif($type === 'textarea')
    <textarea class="form-control" wire:model.{{ $binding }}='{{ $model }}' name="{{ $model }}"
        @disabled($disabled) id="{{ $model }}" rows="{{ $textarea_rows }}"
        placeholder="{{ $placeholder }}">{{ old($model) ?? $value }}</textarea>
@elseif ($type === 'radio')
    <div class="d-flex gap-4">
        @foreach ($option as $key => $opt)
            <div class="form-check mb-1">
                <input wire:model.{{ $binding }}='{{ $model }}' class="form-check-input"
                    type="{{ $type }}" name="{{ $model }}" old($model)
                    id="{{ $model }}-{{ $opt }}" @disabled($disabled)
                    value="{{ $opt }}" checked="{{ old($model) == $opt }}">
                <label class="form-check-label"
                    for="{{ $model }}-{{ $opt }}">{{ $optionIsArray ? $opt : $key }}</label>
            </div>
        @endforeach
    </div>
@else
    <input type="{{ $type ?? 'text' }}" wire:model.{{ $binding }}="{{ $model }}"
        id="{{ $model }}" name="{{ $model }}" placeholder="{{ $placeholder }}" class="form-control"
        value="{{ old($model) ?? $value }}" @disabled($disabled) />
    @endif

    @if (!is_null($append))
        {{ $append }}
</div>
@endif
@if ($driver == 'session')
    @if ($errors->has($model))
        <span class="text-danger" role="alert">
            @foreach ($errors->get($model) as $error)
                {{ $error }}
            @endforeach
        </span>
    @endif
@else
    @error($model)
        <span class="invalid-feedback" role="alert">
            {{ $message }}
        </span>
    @enderror
@endif
</div>

</div>
