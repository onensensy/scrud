@props([
    // 'name',
    'model',
    'binding' => 'live',
    'placeholder' => null,
    'type',
    'hasLabel' => true,
    'label',
    'col' => 6,
    'required' => false,
    'option' => [],
    'optionCall' => null,
    'optionIsArray' => false,
    'fileMultiple' => false,
    'checkbox_inline' => false,
    'checkbox_label' => true,
    'checkbox_text' => 'Yes',
    'checked' => false,
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
    'select2' => false,
])

@php
    $label = $hasLabel ? $label : '';

    $placeholder = isset($placeholder) ? $placeholder : 'Enter ' . $label;
@endphp

<div class="col-md-{{ $col }} px-2">
    <div class="form-group">

        @if ($hasLabel)
            <label class="mb-0 p-0">{{ $label }} @if ($required)
                    *
                @endif
            </label>
        @endif

        @if (!is_null($info))
            <x-scrud::dynamics.tooltips message="{{ $info }}" />
        @endif
        @if (!is_null($append))
            <div class="input-group">
        @endif
        {{-- Select Inputs --}}
        @if ($type === 'select')
            @if ($select2)
                <div wire:ignore>
            @endif
            <select id="select2-{{ $model }}" wire:model.live="{{ $model }}" @required($required)
                placeholder="{{ $placeholder }}" id="{{ $model }}" name="{{ $model }}"
                class="form-select select2">
                <option value="">Select an option</option>
                @foreach ($option as $opt)
                    <option value="{{ $optionIsArray ? $opt : $opt->id }}"
                        @if (($optionIsArray ? $opt : $opt->id) == $value) selected @endif>
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
    <input name="{{ $model }}" type="{{ $type }}" @required($required)
        wire:model.{{ $binding }}="{{ $model }}" @disabled($disabled) id="{{ $model }}"
        class="form-check-input" value="{{ old($model) ?? $value }}"
        @if ($checked) checked @endif />
    @if ($checkbox_text)
        <label for="{{ $model }}" class="form-check-label">{{ $checkbox_text }}</label>
    @endif
@elseif($type === 'file')
    @if ($driver == 'livewire')
        @if (!is_null($preview))
            <i class="fa fa-check-circle text-success"></i>
            @php
                $ext = $preview->getClientOriginalExtension();
            @endphp
            @if (in_array($ext, ['png', 'jpeg', 'jpg']))
                <img src="{{ $preview->temporaryUrl() }}" alt="Preview Image" style="width:300px">
            @else
            @endif
        @endif

        <x-scrud::dynamics.filepond wire:model.{{ $binding }}='{{ $model }}' />
    @else
        <input id="{{ $model }}" type="{{ $type }}" name="{{ $model }}" @required($required)
            @if ($fileMultiple) multiple data-allow-reorder="true" @endif data-max-file-size="4MB"
            data-max-files="3">
    @endif
@elseif($type === 'textarea')
    <textarea class="form-control" wire:model.{{ $binding }}='{{ $model }}' name="{{ $model }}"
        @disabled($disabled) id="{{ $model }}" rows="{{ $textarea_rows }}" @required($required)
        placeholder="{{ $placeholder }}">{{ old($model) ?? $value }}</textarea>
@elseif ($type === 'radio')
    <div class="d-flex gap-4">
        @foreach ($option as $key => $opt)
            <div class="form-check mb-1">
                <input wire:model.{{ $binding }}='{{ $model }}' class="form-check-input"
                    type="{{ $type }}" name="{{ $model }}" old($model) @required($required)
                    id="{{ $model }}-{{ $opt }}" @disabled($disabled)
                    value="{{ $opt }}" checked="{{ old($model) == $opt }}">
                <label class="form-check-label"
                    for="{{ $model }}-{{ $opt }}">{{ $key }}</label>
            </div>
        @endforeach
    </div>
@else
    <input type="{{ $type ?? 'text' }}" wire:model.{{ $binding }}="{{ $model }}" @required($required)
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
