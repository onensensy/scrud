@props(['documents'])
<div class="row">
    <div class="accordion col-md-10" id="document-list">
        @php
        $score = 0;
        @endphp
        {{-- @dd($documents) --}}
        @foreach ($documents as $doc)
        <div class="accordion-item">
            <h2 class="accordion-header" id="heading{{ $doc->id }}">
                <button class="accordion-button collapsed py-2" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapse{{ $doc->id }}" aria-expanded="false"
                    aria-controls="collapse{{ $doc->id }}">
                    {{ $doc->name }}
                </button>
            </h2>
            <div id="collapse{{ $doc->id }}" class="accordion-collapse collapse" aria-labelledby="heading{{ $doc->id }}"
                data-bs-parent="#document-list">
                <div class="accordion-body">
                    <iframe src="{{ asset($doc->document_path) }}" width="100%" height="400">
                        This browser does not support PDFs. Please download the PDF to view it:
                        <a href="{{ asset($doc->document_path) }}">Download
                            PDF</a></iframe>
                </div>
            </div>
        </div>
        @php
        $score += $doc->documentType->score;
        @endphp
        @endforeach
    </div>
    <div class="col-md-2">
        <style>
            /* Custom CSS for the circle */
            .circle {
                width: 100px;
                height: 100px;
                border-radius: 50%;
                border: 4px solid #235e14;
                /* Set border color */
                display: flex;
                justify-content: center;
                align-items: center;

                /* Set text color */
                font-size: 30px;
            }
        </style>
        <div class='d-flex justify-content-center  align-items-center flex-column'>
            <div class="circle text-primary">{{$score ?? '--'}} </div>
            <div class="text-primary h1">SCORE </div>
        </div>
    </div>
</div>