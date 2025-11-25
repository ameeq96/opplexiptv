@extends('layouts.default')
@section('title', __('messages.thankyou_page.title'))

@section('content')
<style>
.thank-wrap{
    min-height:60vh;
    display:flex;
    align-items:center;
    justify-content:center;
    background:radial-gradient(circle at top,#e0f2fe 0,#eff6ff 40%,#f9fafb 100%);
}
.thank-card{
    max-width:620px;
    width:100%;
    background:#ffffff;
    border-radius:1.25rem;
    box-shadow:0 18px 45px rgba(15,23,42,.18);
    border:1px solid #e5e7eb;
    padding:2.5rem 2rem;
    text-align:center;
    position:relative;
    overflow:hidden;
}
.thank-badge{
    width:74px;
    height:74px;
    border-radius:999px;
    background:linear-gradient(135deg,#22c55e,#16a34a);
    display:flex;
    align-items:center;
    justify-content:center;
    margin:0 auto 1rem auto;
    color:#fff;
    font-size:34px;
    box-shadow:0 16px 35px rgba(22,163,74,.55);
}
.thank-title{
    font-weight:800;
    font-size:1.8rem;
    color:#0f172a;
}
.thank-sub{
    color:#6b7280;
    max-width:460px;
    margin:0.35rem auto 1.3rem;
}
.thank-pill{
    display:inline-flex;
    align-items:center;
    gap:.4rem;
    padding:.35rem .75rem;
    border-radius:999px;
    background:#ecfdf5;
    color:#15803d;
    font-size:.8rem;
    font-weight:600;
    margin-bottom:1.1rem;
}
.thank-pill i{font-size:.9rem}
.thank-order-box{
    background:#f9fafb;
    border-radius:.9rem;
    border:1px dashed #e5e7eb;
    padding:1rem .9rem;
    text-align:left;
    margin:0 auto 1.4rem;
}
.thank-order-row{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    font-size:.9rem;
    color:#4b5563;
}
.thank-order-row strong{color:#111827}
.thank-order-row + .thank-order-row{
    margin-top:.45rem;
}
.thank-order-total{
    border-top:1px dashed #e5e7eb;
    margin-top:.55rem;
    padding-top:.55rem;
    font-weight:700;
    font-size:1.05rem;
}
.thank-actions{
    display:flex;
    flex-wrap:wrap;
    justify-content:center;
    gap:.75rem;
    margin-top:1.4rem;
}
.thank-btn-primary{
    display:inline-flex;
    align-items:center;
    gap:.4rem;
    padding:.65rem 1.4rem;
    border-radius:.7rem;
    border:0;
    background:#2563eb;
    color:#fff;
    font-weight:600;
    text-decoration:none;
}
.thank-btn-primary:hover{
    background:#1d4ed8;
    color:#fff;
}
.thank-btn-ghost{
    display:inline-flex;
    align-items:center;
    gap:.4rem;
    padding:.65rem 1.4rem;
    border-radius:.7rem;
    border:1px solid #e5e7eb;
    background:#ffffff;
    color:#374151;
    font-weight:500;
    text-decoration:none;
}
.thank-btn-ghost:hover{
    background:#f9fafb;
    color:#111827;
}
.thank-footnote{
    margin-top:1.3rem;
    font-size:.82rem;
    color:#6b7280;
}
.confetti-dot{
    position:absolute;
    width:7px;
    height:7px;
    border-radius:999px;
    opacity:.55;
}
.confetti-dot.c1{background:#f97316;top:18px;left:40px}
.confetti-dot.c2{background:#22c55e;top:40px;right:38px}
.confetti-dot.c3{background:#3b82f6;bottom:22px;left:55px}
.confetti-dot.c4{background:#e11d48;bottom:30px;right:60px}
@media (max-width:576px){
    .thank-card{margin:1.5rem 1rem;padding:2rem 1.5rem}
    .thank-title{font-size:1.5rem}
}
</style>

@php
    // Session se success message (checkoutStep2 se aa raha hai)
    $successMessage = session('success');
@endphp

<div class="thank-wrap">
  <div class="thank-card mt-5 mb-5">
    <span class="confetti-dot c1"></span>
    <span class="confetti-dot c2"></span>
    <span class="confetti-dot c3"></span>
    <span class="confetti-dot c4"></span>

    <div class="thank-badge">
      <i class="fa fa-check"></i>
    </div>

    <div class="thank-pill">
      <i class="fa fa-shield-alt"></i> {{ __('messages.thankyou_page.badge_text') }}
    </div>

    <h1 class="thank-title">{{ __('messages.thankyou_page.heading') }}</h1>

    <p class="thank-sub">
      {{ __('messages.thankyou_page.sub_text') }}
    </p>

    @if($successMessage)
      <p class="mb-3" style="font-size:.9rem;color:#4b5563;">
        {{ $successMessage }}
      </p>
    @endif

    <div class="thank-order-box">
      <div class="thank-order-row">
        <span>{{ __('messages.thankyou_page.order_status') }}</span>
        <span><strong>{{ __('messages.thankyou_page.pending') }}</strong></span>
      </div>
      <div class="thank-order-row">
        <span>{{ __('messages.thankyou_page.delivery') }}</span>
        <span>{{ __('messages.thankyou_page.delivery_text') }}</span>
      </div>
      <div class="thank-order-row">
        <span>{{ __('messages.thankyou_page.support') }}</span>
        <span>{{ __('messages.thankyou_page.support_text') }}</span>
      </div>

      <div class="thank-order-total">
        <span>{{ __('messages.thankyou_page.next') }}</span>
        <span>{{ __('messages.thankyou_page.next_text') }}</span>
      </div>
    </div>

    <div class="thank-actions">
      <a href="{{ route('home') }}" class="thank-btn-primary">
        <i class="fa fa-home"></i> {{ __('messages.thankyou_page.home_btn') }}
      </a>

      <a href="{{ route('contact') ?? '#' }}" class="thank-btn-ghost">
        <i class="fa fa-headset"></i> {{ __('messages.thankyou_page.support_btn') }}
      </a>
    </div>

    <div class="thank-footnote">
      {{ __('messages.thankyou_page.footnote') }}
    </div>
  </div>
</div>
@endsection
