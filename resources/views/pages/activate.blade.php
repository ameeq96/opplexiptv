@extends('layouts.default')
@section('title', __('interface.activation.page_title'))

@section('content')
    <section class="section sec-activation d-flex justify-content-center align-items-center py-5"
        style="background-image:url('{{ asset('images/background/pattern-6.webp') }}')" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
        <div class="container" style="max-width:720px;">
            <h1 class="text-center mb-2" style="text-align: {{ $isRtl ? 'right' : 'left' }};">
                {{ __('interface.activation.page_title') }}
            </h1>
            <h2 class="text-muted mb-5 text-center mt-4" style="font-size:1rem; text-align: {{ $isRtl ? 'right' : 'left' }};">
                {{ __('meta.activate.title') }}
            </h2>

            <div class="card p-4 shadow-sm">
                <a href="{{ route('activate-info') }}">
                    <button id="waBtn" class="btn btn-danger mt-3 w-100" type="button"
                        aria-label="{{ __('interface.activation.get_instructions') }}">
                        <i class="fa fa me-1"></i> {{ __('interface.activation.get_instructions') }}
                    </button>
                </a>
                <small id="errorText" class="text-danger d-none mt-2">{{ __('interface.activation.valid_order') }}</small>
            </div>

        </div>
    </section>
@endsection
