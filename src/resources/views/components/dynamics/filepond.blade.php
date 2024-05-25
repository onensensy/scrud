<div wire:ignore x-data x-init="() => {
    const pond = FilePond.create($refs.input, {
        allowMultiple: {{ isset($attributes['multiple']) ? 'true' : 'false' }},
        server: {
            process: (fieldName, file, metadata, load, error, progress, abort, transfer, options) => {
                @this.upload('{{ $attributes['wire:model.live'] }}', file, load, error, progress)
            },
            revert: (filename, load) => {
                @this.removeUpload('{{ $attributes['wire:model.live'] }}', filename, load)
            },
        },
    });
    this.addEventListener('pondReset', e => {
        pond.removeFiles();
    });
}">
    <input type="file" x-ref="input" {!! isset($attributes['accept']) ? 'accept="' . $attributes['accept'] . '"' : '' !!}>
</div>
