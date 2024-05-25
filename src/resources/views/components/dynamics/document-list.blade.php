@props(['documents'])
<div>
    <div class="accordion" id="document-list">
        @foreach ($documents as $doc)
            <div class="accordion-item">
                <h2 class="accordion-header" id="heading{{ $doc->id }}">
                    <button class="accordion-button collapsed py-2" type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapse{{ $doc->id }}" aria-expanded="false"
                        aria-controls="collapse{{ $doc->id }}">
                        {{ $doc->name }}
                    </button>
                </h2>
                <div id="collapse{{ $doc->id }}" class="accordion-collapse collapse"
                    aria-labelledby="heading{{ $doc->id }}" data-bs-parent="#document-list">
                    <div class="accordion-body">
                        <iframe src="{{ asset($doc->document_path) }}" width="100%" height="400">
                            This browser does not support PDFs. Please download the PDF to view it:
                            <a href="{{ asset($doc->document_path) }}">Download
                                PDF</a></iframe>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
