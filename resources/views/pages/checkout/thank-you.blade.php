@extends('layouts.default')
@section('title', __('messages.thankyou_page.title'))

@section('content')

@php
    $successMessage = session('success');
    $isCheckoutCompletion = (bool) session('checkout_completed', false);
    $orderSummary = $orderSummary ?? session('order_summary');
    $order = $order ?? null;
    $paymentProofUploadUrl = $paymentProofUploadUrl ?? null;
    $proofSubmittedAt = $proofSubmittedAt ?? null;
    $proofCount = $order?->pictures?->count() ?? 0;
    $paymentIsPaid = $order?->payment_status === 'paid';
    $orderIsActive = in_array($order?->status, ['active', 'expired'], true);
    $orderStatusLabel = match (true) {
        $order?->status === 'expired' => __('messages.thankyou_page.status_expired'),
        $orderIsActive => __('messages.thankyou_page.status_active'),
        $paymentIsPaid => __('messages.thankyou_page.status_activation_pending'),
        $proofCount > 0 => __('messages.thankyou_page.status_verification_pending'),
        default => __('messages.thankyou_page.pending'),
    };
    $whatsappPaymentUrl = null;
    $paymentMethodLabel = null;

    if ($orderSummary) {
        $paymentMethodLabel = match ($orderSummary['payment_method'] ?? null) {
            'card' => __('messages.checkout_pay_card_title'),
            'crypto' => __('messages.checkout_pay_crypto_title'),
            'easypaisa' => 'Easypaisa',
            'nayapay / sadapay' => 'Nayapay / Sadapay',
            'meezan bank' => 'Meezan Bank',
            'raast' => 'RAAST',
            'remitly' => 'Remitly',
            'skrill' => 'Skrill to Skrill',
            'binance' => 'Binance Exchange',
            'mexc' => 'MEXC Exchange',
            'on-chain usdt (bep20)' => 'On-Chain USDT (BEP20)',
            default => ucfirst((string) ($orderSummary['payment_method'] ?? '')),
        };
        $whatsappNumber = preg_replace(
            '/\D+/',
            '',
            (string) config('services.whatsapp.number')
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

<style>
  .order-status-timeline { display:grid; gap:.7rem; margin:0 auto 1.4rem; text-align:start; }
  .order-status-step { display:grid; grid-template-columns:32px minmax(0,1fr); gap:.7rem; align-items:start; color:#64748b; }
  .order-status-step__icon { display:flex; width:32px; height:32px; align-items:center; justify-content:center; border-radius:999px; background:#e2e8f0; color:#64748b; }
  .order-status-step.is-complete .order-status-step__icon { background:#dcfce7; color:#15803d; }
  .order-status-step strong { display:block; color:#334155; font-size:.9rem; }
  .order-status-step small { display:block; margin-top:2px; }
  .payment-proof-form { margin:0 auto 1.4rem; padding:1rem; border:1px solid #dbeafe; border-radius:.9rem; background:#f8fbff; text-align:start; }
  .payment-proof-form label { display:block; margin-bottom:.45rem; color:#0f172a; font-weight:700; }
  .payment-proof-form input[type="file"] { width:100%; padding:.55rem; border:1px solid #cbd5e1; border-radius:.65rem; background:#fff; }
  .payment-proof-form button { width:100%; margin-top:.75rem; padding:.7rem 1rem; border:0; border-radius:.7rem; background:#16a34a; color:#fff; font-weight:700; }
  .payment-proof-form small { display:block; margin-top:.45rem; color:#64748b; }
  .payment-proof-message { margin:.75rem 0 0; color:#15803d; font-size:.86rem; font-weight:600; }
  .payment-proof-error { margin:.45rem 0 0; color:#b91c1c; font-size:.84rem; }
</style>

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
        @if ((float) ($orderSummary['discount'] ?? 0) > 0)
          <div class="thank-order-row">
            <span>{{ $orderSummary['promotion_name'] ?? 'Event offer' }} (10% OFF)</span>
            <span><strong>-${{ number_format((float) $orderSummary['discount'], 2) }}</strong></span>
          </div>
        @endif
        <div class="thank-order-row">
          <span>{{ __('messages.checkout_total_label') }}</span>
          <span><strong>${{ number_format((float) $orderSummary['total'], 2) }}</strong></span>
        </div>
      @endif
      <div class="thank-order-row">
        <span>{{ __('messages.thankyou_page.order_status') }}</span>
        <span><strong>{{ $orderStatusLabel }}</strong></span>
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

    @if($order)
      <div class="order-status-timeline" aria-label="{{ __('messages.thankyou_page.timeline_title') }}">
        <div class="order-status-step is-complete">
          <span class="order-status-step__icon" aria-hidden="true"><i class="fa fa-check"></i></span>
          <div>
            <strong>{{ __('messages.thankyou_page.timeline_created') }}</strong>
            <small>{{ $order->created_at?->locale(app()->getLocale())->isoFormat('lll') }}</small>
          </div>
        </div>
        <div class="order-status-step {{ $proofCount > 0 ? 'is-complete' : '' }}">
          <span class="order-status-step__icon" aria-hidden="true"><i class="fa {{ $proofCount > 0 ? 'fa-check' : 'fa-clock-o' }}"></i></span>
          <div>
            <strong>{{ __('messages.thankyou_page.timeline_proof') }}</strong>
            <small>{{ $proofSubmittedAt ? $proofSubmittedAt->locale(app()->getLocale())->isoFormat('lll') : __('messages.thankyou_page.timeline_waiting') }}</small>
          </div>
        </div>
        <div class="order-status-step {{ $paymentIsPaid ? 'is-complete' : '' }}">
          <span class="order-status-step__icon" aria-hidden="true"><i class="fa {{ $paymentIsPaid ? 'fa-check' : 'fa-clock-o' }}"></i></span>
          <div>
            <strong>{{ __('messages.thankyou_page.timeline_verified') }}</strong>
            <small>{{ $order->paid_at?->locale(app()->getLocale())->isoFormat('lll') ?? __('messages.thankyou_page.timeline_waiting') }}</small>
          </div>
        </div>
        <div class="order-status-step {{ $orderIsActive ? 'is-complete' : '' }}">
          <span class="order-status-step__icon" aria-hidden="true"><i class="fa {{ $orderIsActive ? 'fa-check' : 'fa-clock-o' }}"></i></span>
          <div>
            <strong>{{ __('messages.thankyou_page.timeline_activated') }}</strong>
            <small>{{ $orderIsActive ? __('messages.thankyou_page.timeline_complete') : __('messages.thankyou_page.timeline_waiting') }}</small>
          </div>
        </div>
      </div>

      @if($paymentProofUploadUrl)
        <form action="{{ $paymentProofUploadUrl }}" method="POST" enctype="multipart/form-data" class="payment-proof-form">
          @csrf
          <label for="paymentProof">
            {{ $proofCount > 0 ? __('messages.thankyou_page.upload_another_proof') : __('messages.thankyou_page.upload_proof') }}
          </label>
          <input id="paymentProof" name="payment_proof" type="file"
            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" required>
          <small>{{ __('messages.thankyou_page.proof_help') }}</small>
          @error('payment_proof')
            <p class="payment-proof-error" role="alert">{{ $message }}</p>
          @enderror
          <button type="submit"><i class="fa fa-lock" aria-hidden="true"></i> {{ __('messages.thankyou_page.submit_proof') }}</button>
          @if($proofCount > 0)
            <p class="payment-proof-message"><i class="fa fa-check-circle" aria-hidden="true"></i> {{ __('messages.thankyou_page.proof_received') }}</p>
          @endif
        </form>
      @elseif($proofCount > 0)
        <p class="payment-proof-message mb-3"><i class="fa fa-check-circle" aria-hidden="true"></i> {{ __('messages.thankyou_page.proof_received') }}</p>
      @endif
    @endif

    <div class="thank-actions">
      @if($whatsappPaymentUrl && !$paymentIsPaid)
        <a href="{{ $whatsappPaymentUrl }}" class="thank-btn-primary" id="whatsappPaymentLink"
          target="_blank" rel="noopener noreferrer"
          data-whatsapp-click
          data-whatsapp-placement="thank_you_payment"
          data-whatsapp-intent="payment"
          data-whatsapp-order-id="{{ $orderSummary['id'] }}"
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
      {{ $paymentIsPaid ? __('messages.thankyou_page.paid_footnote') : __('messages.thankyou_page.footnote') }}
    </div>
  </div>
</div>

@if($orderSummary && $isCheckoutCompletion)
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      try {
        if (typeof window.trackMarketingEvent === 'function') {
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
        }
      } catch (error) {}
    });
  </script>
@endif
@endsection
