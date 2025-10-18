@extends('layouts.default')

@section('title', __('messages.about.title'))

@section('content')
<section class="container py-5">
  <h1 class="mb-2 h2"><b>{{ __('privacy.title') }}</b></h1>
  <p class="text-muted">{{ __('privacy.effective') }}</p>
  <hr class="my-4">

  <p>{!! __('privacy.intro') !!}</p>

  <h2 class="mt-4">1. {{ __('privacy.collection.title') }}</h2>
  <ul>
    <li>{!! __('privacy.collection.item1') !!}</li>
    <li>{!! __('privacy.collection.item2') !!}</li>
    <li>{!! __('privacy.collection.item3') !!}</li>
  </ul>

  <h2 class="mt-4">2. {{ __('privacy.usage.title') }}</h2>
  <ul>
    <li>{!! __('privacy.usage.item1') !!}</li>
    <li>{!! __('privacy.usage.item2') !!}</li>
    <li>{!! __('privacy.usage.item3') !!}</li>
  </ul>

  <h2 class="mt-4">3. {{ __('privacy.contact.title') }}</h2>
  <p>{!! __('privacy.contact.text') !!}</p>
</section>
@endsection
