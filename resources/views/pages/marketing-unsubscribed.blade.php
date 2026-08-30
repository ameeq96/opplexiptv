@extends('layouts.default')

@section('title', __('marketing.unsubscribe.title'))

@section('content')
<section class="container py-5 text-center">
    @if ($confirmed ?? false)
        <h1 class="h2 mb-3">{{ __('marketing.unsubscribe.title') }}</h1>
        <p class="text-muted mb-4">{{ __('marketing.unsubscribe.text') }}</p>
        <a href="{{ route('home') }}" class="btn btn-primary">{{ __('marketing.unsubscribe.home') }}</a>
    @else
        <h1 class="h2 mb-3">{{ __('marketing.unsubscribe.confirm_title') }}</h1>
        <p class="text-muted mb-4">{{ __('marketing.unsubscribe.confirm_text') }}</p>
        <form method="POST" action="{{ $signedAction }}" class="d-inline-block">
            @csrf
            <button type="submit" class="btn btn-danger">{{ __('marketing.unsubscribe.confirm_button') }}</button>
        </form>
        <a href="{{ route('home') }}" class="btn btn-outline-secondary">{{ __('marketing.unsubscribe.keep_button') }}</a>
    @endif
</section>
@endsection
