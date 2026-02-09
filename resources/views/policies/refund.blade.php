@extends('layouts.default')

@section('title', __('messages.about.title'))

@section('content')
<section class="container py-5">
  <h1 class="mb-2 h2"><b>{{ __('refund.title') }}</b></h1>
  <h2 class="h5 text-muted mb-1">{{ __('refund.effective') }}</h2>
  <hr class="my-4">

  <p>{!! __('refund.intro') !!}</p>

  <h3 class="mt-3">1. {{ __('refund.eligibility.title') }}</h3>
  <ul>
    <li>{!! __('refund.eligibility.item1') !!}</li>
    <li>{!! __('refund.eligibility.item2') !!}</li>
    <li>{!! __('refund.eligibility.item3') !!}</li>
  </ul>

  <h3 class="mt-3">2. {{ __('refund.non_refundable.title') }}</h3>
  <ul>
    <li>{!! __('refund.non_refundable.item1') !!}</li>
    <li>{!! __('refund.non_refundable.item2') !!}</li>
    <li>{!! __('refund.non_refundable.item3') !!}</li>
  </ul>

  <h3 class="mt-3">3. {{ __('refund.cancellation.title') }}</h3>
  <p>{!! __('refund.cancellation.text') !!}</p>

  <h3 class="mt-3">4. {{ __('refund.process.title') }}</h3>
  <ul>
    <li>{!! __('refund.process.item1') !!}</li>
    <li>{!! __('refund.process.item2') !!}</li>
  </ul>

  <h3 class="mt-3">5. {{ __('refund.returns.title') }}</h3>
  <p>{!! __('refund.returns.text') !!}</p>
</section>
@endsection
