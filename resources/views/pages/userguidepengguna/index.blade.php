@extends('layouts.dashboard.template')

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="card-header-right">
                <ul class="list-unstyled card-option">
                    <li><i class="fa fa fa-wrench open-card-option"></i></li>
                    <li><i class="fa fa-window-maximize full-card"></i></li>
                    <li><i class="fa fa-minus minimize-card"></i></li>
                    <li><i class="fa fa-refresh reload-card"></i></li>
                    <li><i class="fa fa-trash close-card"></i></li>
                </ul>
            </div>
        </div>
        <div class="card-block table-border-style">
            <div class="table-responsive">
                <h3 style="color: #104819; font-weight: bold; font-size: 30px; text-align: center;">Frequently Asked
                    Questions</h3>
                <hr>
                <br>
                <p style="color: #9F1717; font-weight: bold; font-size: 12px;">Catatan: Data pada halaman ini didasarkan
                    pada aktivitas pengguna di website MYBAAK</p>
                <br>
                <h4 style="color: #104819; font-weight: bold; font-size: 30px;">FAQ</h4>
                <div class="card-block accordion-block">
                    <div id="accordion-faq" role="tablist" aria-multiselectable="true">
                        @foreach ($faqs as $faq)
                            <div class="accordion-panel">
                                <div class="accordion-heading" role="tab" id="headingFaq{{ $loop->iteration }}">
                                    <h1 class="card-title accordion-title">
                                        <a class="accordion-msg waves-effect waves-dark collapsed d-flex justify-content-between align-items-center"
                                            data-toggle="collapse" data-parent="#accordion-faq"
                                            href="#collapseFaq{{ $loop->iteration }}" aria-expanded="false"
                                            aria-controls="collapseFaq{{ $loop->iteration }}"
                                            style="color: #019C24; font-weight: bold; font-size: 20px;">
                                            {{ $faq->title }}
                                            <i class="fa fa-angle-down"></i>
                                        </a>
                                    </h1>
                                </div>
                                <div id="collapseFaq{{ $loop->iteration }}" class="panel-collapse collapse" role="tabpanel"
                                    aria-labelledby="headingFaq{{ $loop->iteration }}">
                                    <div class="accordion-content accordion-desc">
                                        {!! $faq->deskripsi !!}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('style')
    <style>
        .accordion-msg .fa-angle-down,
        .accordion-msg svg.fa-angle-down {
            transition: transform 0.3s ease-in-out;
            display: inline-block !important;
        }

        .accordion-msg[aria-expanded="true"] .fa-angle-down,
        .accordion-msg[aria-expanded="true"] svg.fa-angle-down,
        .accordion-msg:not(.collapsed) .fa-angle-down,
        .accordion-msg:not(.collapsed) svg.fa-angle-down {
            transform: rotate(180deg) !important;
        }

        .accordion-desc p {
            margin-bottom: 10px;
        }

        .accordion-desc img {
            max-width: 100%;
            height: auto;
        }

        .accordion-desc ul,
        .accordion-desc ol {
            padding-left: 20px;
            margin-bottom: 15px;
        }

        .accordion-desc ul {
            list-style-type: disc;
        }

        .accordion-desc ol {
            list-style-type: decimal;
        }
    </style>
@endpush
