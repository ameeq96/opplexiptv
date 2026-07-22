@extends('layouts.default')

@php
    $isDocumentEnglish = app()->getLocale() === 'en';
    $documentFaq = $isDocumentEnglish ? __('document_support.faq') : [];
    $documentFaqGroups = $isDocumentEnglish ? ($documentFaq['groups'] ?? []) : [];
    $documentFaqItems = $isDocumentEnglish
        ? collect($documentFaqGroups)->flatMap(fn ($group) => $group['items'] ?? [])->values()->all()
        : [];
    $faqSchemaItems = $isDocumentEnglish ? $documentFaqItems : ($faqs ?? []);
@endphp

@section('title', $isDocumentEnglish ? $documentFaq['heading'] : __('messages.faq.title'))

@push('schema')
    {!! jsonld(seo()->faqPage($faqSchemaItems)) !!}
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/faq.css') }}?v={{ @filemtime(public_path('css/faq.css')) ?: 1 }}">
    @if ($isDocumentEnglish)
        <link rel="stylesheet" href="{{ asset('css/document-support.css') }}?v={{ @filemtime(public_path('css/document-support.css')) ?: 1 }}">
    @endif
@endpush

@section('content')
    <x-page-title
        :title="$isDocumentEnglish ? $documentFaq['page_title'] : __('messages.faq.heading')"
        :breadcrumbs="$isDocumentEnglish
            ? [['url' => route('home'), 'label' => 'Home'], ['label' => $documentFaq['page_title']]]
            : [
                ['url' => '/', 'label' => __('messages.faq.breadcrumb.home')],
                ['label' => __('messages.faq.breadcrumb.current')],
            ]"
        background="images/background/10.webp"
        :rtl="$isRtl"
        aria-label="FAQ Page" />

    @if ($isDocumentEnglish)
        <main class="document-support document-support--faq" aria-labelledby="document-faq-title">
            <section class="document-support__hero document-support__hero--compact">
                <div class="auto-container document-support__hero-inner">
                    <span class="document-support__eyebrow">FAQS</span>
                    <h1 id="document-faq-title">{{ $documentFaq['heading'] }}</h1>
                    <p>{{ $documentFaq['intro'] }}</p>
                </div>
            </section>

            <div class="auto-container document-support__faq-groups">
                @foreach ($documentFaqGroups as $groupIndex => $group)
                    <section class="document-support__faq-group" aria-labelledby="document-faq-group-{{ $groupIndex }}">
                        <div class="document-support__section-heading document-support__section-heading--left">
                            <span class="document-support__section-number" aria-hidden="true">{{ str_pad((string) ($groupIndex + 1), 2, '0', STR_PAD_LEFT) }}</span>
                            <h2 id="document-faq-group-{{ $groupIndex }}">{{ $group['title'] }}</h2>
                        </div>

                        <ul class="accordion-box document-support__accordion">
                            @foreach ($group['items'] as $item)
                                @php($isOpen = $groupIndex === 0 && $loop->first)
                                <li class="accordion block {{ $isOpen ? 'active-block' : '' }}">
                                    <div class="acc-btn {{ $isOpen ? 'active' : '' }}"
                                        aria-expanded="{{ $isOpen ? 'true' : 'false' }}"
                                        role="button"
                                        tabindex="0">
                                        <div class="icon-outer" aria-hidden="true">
                                            <span class="icon icon-plus fa fa-plus"></span>
                                            <span class="icon icon-minus fa fa-minus"></span>
                                        </div>
                                        <span>{{ $item['question'] }}</span>
                                    </div>

                                    <div class="acc-content {{ $isOpen ? 'current' : '' }}">
                                        <div class="content">
                                            <div class="text">{{ $item['answer'] }}</div>

                                            @if (!empty($item['images']))
                                                <div class="document-support__faq-media" role="list">
                                                    @foreach ($item['images'] as $image)
                                                        <figure role="listitem">
                                                            <img src="{{ asset($image['url']) }}"
                                                                alt="{{ $image['caption'] }}"
                                                                loading="lazy"
                                                                decoding="async">
                                                            <figcaption>{{ $image['caption'] }}</figcaption>
                                                        </figure>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endforeach
            </div>

            <section class="document-support__cta" aria-labelledby="document-faq-cta-title">
                <div class="auto-container document-support__cta-inner">
                    <div>
                        <h2 id="document-faq-cta-title">{{ $documentFaq['cta']['heading'] }}</h2>
                        <p>{{ $documentFaq['cta']['text'] }}</p>
                    </div>
                    <div class="document-support__actions">
                        <a class="document-support__button document-support__button--light"
                            href="https://wa.me/16393903194?text={{ urlencode(__('document_support.whatsapp_messages.trial')) }}"
                            target="_blank" rel="noopener">
                            {{ $documentFaq['cta']['trial'] }}
                        </a>
                        <a class="document-support__button document-support__button--outline-light" href="{{ route('packages') }}">
                            {{ $documentFaq['cta']['plans'] }}
                        </a>
                        <a class="document-support__button document-support__button--outline-light"
                            href="https://wa.me/16393903194?text={{ urlencode(__('document_support.whatsapp_messages.support')) }}"
                            target="_blank" rel="noopener">
                            {{ $documentFaq['cta']['whatsapp'] }}
                        </a>
                    </div>
                </div>
            </section>
        </main>
    @else
        <section class="faqx {{ $isRtl ? 'rtl' : '' }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}" aria-labelledby="faqx-title">
            <div class="auto-container">
                <div class="faqx__head">
                    <span class="faqx__chip" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="9"/>
                            <path d="M9.1 9a3 3 0 0 1 5.8 1c0 2-3 2.5-3 4"/>
                            <path d="M12 17h.01"/>
                        </svg>
                    </span>
                    <h1 id="faqx-title" class="faqx__title">{{ __('messages.faq.section_title') }}</h1>
                </div>

                <ul class="accordion-box">
                    @foreach ($faqs as $faq)
                        <li class="accordion block {{ $loop->first ? 'active-block' : '' }}">
                            <div class="acc-btn {{ $loop->first ? 'active' : '' }}"
                                aria-expanded="{{ $loop->first ? 'true' : 'false' }}" role="button">
                                <div class="icon-outer">
                                    <span class="icon icon-plus fa fa-plus" aria-hidden="true"></span>
                                    <span class="icon icon-minus fa fa-minus" aria-hidden="true"></span>
                                </div>
                                <span>{{ $faq['question'] }}</span>
                            </div>

                            <div class="acc-content {{ $loop->first ? 'current' : '' }}">
                                <div class="content">
                                    <div class="text">{!! $faq['answer'] !!}</div>

                                    @if (isset($faq['images']))
                                        <div class="row d-flex justify-content-center align-items-center mt-3">
                                            @foreach ($faq['images'] as $image)
                                                <div class="col-lg-6 mt-2">
                                                    <figure>
                                                        <img src="{{ asset($image['url']) }}"
                                                            alt="{{ $image['caption'] }}" loading="lazy"
                                                            decoding="async" width="100%" />
                                                        <figcaption class="text-center mt-2">{{ $image['caption'] }}</figcaption>
                                                    </figure>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif
@stop
