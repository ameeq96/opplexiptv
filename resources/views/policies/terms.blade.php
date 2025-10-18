@extends('layouts.default')

@section('title', __('messages.about.title'))

@section('content')
<section class="container py-5">
    <h1 class="mb-2 h2"><b>{{ __('terms.terms.title') }}</b></h1>
    <p class="text-muted">{{ __('terms.terms.effective') }} • {{ __('terms.terms.provider') }}: {{ 'Opplex Store' }}</p>
    <hr class="my-4">

    <h2>1. {{ __('terms.terms.introduction.title') }}</h2>
    <p>{!! __('terms.terms.introduction.text') !!}</p>

    <h2>2. {{ __('terms.terms.service_overview.title') }}</h2>
    <p>{!! __('terms.terms.service_overview.text') !!}</p>

    <h2>3. {{ __('terms.terms.accounts.title') }}</h2>
    <ul>
        <li>{{ __('terms.terms.accounts.item1') }}</li>
        <li>{{ __('terms.terms.accounts.item2') }}</li>
        <li>{{ __('terms.terms.accounts.item3') }}</li>
    </ul>

    <h2>4. {{ __('terms.terms.payments.title') }}</h2>
    <ul>
        <li>{{ __('terms.terms.payments.item1') }}</li>
        <li>{{ __('terms.terms.payments.item2') }}</li>
        <li>{{ __('terms.terms.payments.item3') }}</li>
    </ul>

    <h2>5. {{ __('terms.terms.refunds.title') }}</h2>
    <p>{!! str_replace(':link', url('/refund-policy'), __('terms.terms.refunds.text')) !!}</p>

    <h2>6. {{ __('terms.terms.acceptable_use.title') }}</h2>
    <ul>
        <li>{{ __('terms.terms.acceptable_use.item1') }}</li>
        <li>{{ __('terms.terms.acceptable_use.item2') }}</li>
        <li>{{ __('terms.terms.acceptable_use.item3') }}</li>
    </ul>

    <h2>7. {{ __('terms.terms.availability.title') }}</h2>
    <p>{!! __('terms.terms.availability.text') !!}</p>

    <h2>8. {{ __('terms.terms.liability.title') }}</h2>
    <p>{!! __('terms.terms.liability.text') !!}</p>

    <h2>9. {{ __('terms.terms.contact.title') }}</h2>
    <p>{!! __('terms.terms.contact.text') !!}</p>
</section>
@endsection
