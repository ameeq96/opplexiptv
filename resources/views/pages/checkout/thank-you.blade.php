@extends('layouts.default')
@section('title', __('messages.thankyou_page.title'))

@section('content')

@php
    // Session se success message (checkoutStep2 se aa raha hai)
    $successMessage = session('success');
    $orderSummary = session('order_summary');
    $whatsappPaymentUrl = null;
    $paymentMethodLabel = null;

    if ($orderSummary) {
        $paymentMethodLabel = match ($orderSummary['payment_method'] ?? null) {
            'card' => __('messages.checkout_pay_card_title'),
            'crypto' => __('messages.checkout_pay_crypto_title'),
            default => ucfirst((string) ($orderSummary['payment_method'] ?? '')),
        };
        $whatsappNumber = preg_replace(
            '/\D+/',
            '',
            (string) (config('services.whatsapp.number') ?: '16393903194')
        );
        $paymentMessage = __('messages.thankyou_page.whatsapp_payment_message', [
            'order' => $orderSummary['id'],
            'package' => $orderSummary['package'],
            'amount' => sprintf(
                '%s %.2f',
                $orderSummary['currency'],
                (float) $orderSummary['total']
            ),
            'method' => $paymentMethodLabel,
        ]);

        if ($whatsappNumber !== '') {
            $whatsappPaymentUrl = 'https://wa.me/' . $whatsappNumber . '?text=' . rawurlencode($paymentMessage);
        }
    }
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
      <i class="fa fa-shield"></i> {{ __('messages.thankyou_page.badge_text') }}
    </div>

    <h1 class="thank-title">{{ __('messages.thankyou_page.heading') }}</h1>

    <h2 class="thank-sub" style="font-size:1rem; font-weight:600;">
      {{ __('messages.thankyou_page.sub_text') }}
    </h2>

    @if($successMessage)
      <p class="mb-3" style="font-size:.9rem;color:#4b5563;">
        {{ $successMessage }}
      </p>
    @endif

    <div class="thank-order-box">
      @if($orderSummary)
        <div class="thank-order-row">
          <span>{{ __('interface.email.checkout.order_number') }}</span>
          <span><strong>#{{ $orderSummary['id'] }}</strong></span>
        </div>
        <div class="thank-order-row">
          <span>{{ __('interface.email.checkout.package') }}</span>
          <span><strong>{{ $orderSummary['package'] }}</strong></span>
        </div>
        <div class="thank-order-row">
          <span>{{ __('messages.checkout_total_label') }}</span>
          <span><strong>${{ number_format((float) $orderSummary['total'], 2) }}</strong></span>
        </div>
      @endif
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
      @if($whatsappPaymentUrl)
        <a href="{{ $whatsappPaymentUrl }}" class="thank-btn-primary" target="_blank" rel="noopener noreferrer"
          data-whatsapp-click
          data-whatsapp-placement="thank_you_payment"
          data-whatsapp-intent="payment"
          data-whatsapp-package="{{ $orderSummary['package'] }}"
          data-whatsapp-value="{{ number_format((float) $orderSummary['total'], 2, '.', '') }}"
          data-whatsapp-currency="{{ $orderSummary['currency'] }}">
          <i class="fa fa-whatsapp"></i> {{ __('messages.thankyou_page.continue_payment_whatsapp') }}
        </a>
      @endif

      <a href="{{ route('contact') ?? '#' }}" class="thank-btn-ghost">
        <i class="fa fa-life-ring"></i> {{ __('messages.thankyou_page.support_btn') }}
      </a>

      <a href="{{ route('home') }}" class="thank-btn-ghost">
        <i class="fa fa-home"></i> {{ __('messages.thankyou_page.home_btn') }}
      </a>
    </div>

    <div class="thank-footnote">
      {{ __('messages.thankyou_page.footnote') }}
    </div>
  </div>
</div>

@if($orderSummary)
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      if (typeof window.trackMarketingEvent !== 'function') return;
      if (typeof window.__loadConversionTracking === 'function') {
        window.__loadConversionTracking();
      }
      window.trackMarketingEvent('order_submitted', {
        transaction_id: @json((string) $orderSummary['id']),
        currency: @json($orderSummary['currency']),
        value: @json((float) $orderSummary['total']),
        items: [{
          item_id: @json((string) $orderSummary['package_id']),
          item_name: @json($orderSummary['package']),
          item_brand: @json($orderSummary['vendor']),
          item_category: @json($orderSummary['package_type']),
          price: @json((float) $orderSummary['total']),
          quantity: 1
        }]
      }, 'Lead');
    });
  </script>
@endif
@endsection
