@php
    $currency = '$';
    $notAvailable = __('interface.email.common.not_available');
    $fmtMoney = function ($val) use ($currency, $notAvailable) {
        if ($val === null || $val === '') return $notAvailable;
        return $currency . number_format((float) $val, 2);
    };
    $title = $isAdmin
        ? __('interface.email.checkout.admin_title')
        : __('interface.email.checkout.customer_title');
    $packageType = $details['package_type'] ?? '';
    $packageTypeLabel = match ($packageType) {
        'package', 'iptv' => __('messages.checkout_package_type_iptv'),
        'reseller' => __('messages.checkout_package_type_reseller'),
        default => $packageType,
    };
    $paymentMethod = $details['payment_method'] ?? '';
    $paymentMethodLabel = match ($paymentMethod) {
        'card' => __('messages.checkout_pay_card_title'),
        'crypto' => __('messages.checkout_pay_crypto_title'),
        default => $paymentMethod,
    };
@endphp

<h2 style="margin:0 0 12px 0;">{{ $title }}</h2>

<p style="margin:0 0 12px 0;">
    @if ($isAdmin)
        {{ __('interface.email.checkout.admin_intro') }}
    @else
        {{ __('interface.email.checkout.customer_intro') }}
    @endif
</p>

<table style="width:100%;max-width:520px;border-collapse:collapse;font-family:Arial,sans-serif;font-size:14px;">
    <tr>
        <td style="padding:6px 8px;font-weight:bold;">{{ __('interface.email.checkout.order_number') }}</td>
        <td style="padding:6px 8px;">{{ $details['order_id'] ?? $notAvailable }}</td>
    </tr>
    <tr>
        <td style="padding:6px 8px;font-weight:bold;">{{ __('interface.email.checkout.customer') }}</td>
        <td style="padding:6px 8px;">
            {{ $details['customer_name'] ?? $notAvailable }}<br>
            {{ $details['customer_email'] ?? '' }}
        </td>
    </tr>
    <tr>
        <td style="padding:6px 8px;font-weight:bold;">{{ __('interface.email.checkout.phone') }}</td>
        <td style="padding:6px 8px;">{{ $details['phone'] ?? $notAvailable }}</td>
    </tr>
    <tr>
        <td style="padding:6px 8px;font-weight:bold;">{{ __('interface.email.checkout.package') }}</td>
        <td style="padding:6px 8px;">{{ $details['package'] ?? $notAvailable }}</td>
    </tr>
    <tr>
        <td style="padding:6px 8px;font-weight:bold;">{{ __('interface.email.checkout.type') }}</td>
        <td style="padding:6px 8px;">{{ $packageTypeLabel }}</td>
    </tr>
    @if (!empty($details['vendor']))
        <tr>
            <td style="padding:6px 8px;font-weight:bold;">{{ __('interface.email.checkout.provider') }}</td>
            <td style="padding:6px 8px;">{{ ucfirst($details['vendor']) }}</td>
        </tr>
    @endif
    @if (!empty($details['device']))
        <tr>
            <td style="padding:6px 8px;font-weight:bold;">{{ __('interface.email.checkout.device') }}</td>
            <td style="padding:6px 8px;">{{ $details['device'] }}</td>
        </tr>
    @endif
    <tr>
        <td style="padding:6px 8px;font-weight:bold;">{{ __('interface.email.checkout.quantity') }}</td>
        <td style="padding:6px 8px;">{{ $details['quantity'] ?? 1 }}</td>
    </tr>
    <tr>
        <td style="padding:6px 8px;font-weight:bold;">{{ __('interface.email.checkout.payment_method') }}</td>
        <td style="padding:6px 8px;">{{ $paymentMethodLabel }}</td>
    </tr>
    <tr>
        <td style="padding:6px 8px;font-weight:bold;">{{ __('interface.email.checkout.subscription_price') }}</td>
        <td style="padding:6px 8px;">{{ $fmtMoney($details['subscription_price'] ?? $details['unit_price'] ?? null) }}</td>
    </tr>
    <tr>
        <td style="padding:6px 8px;font-weight:bold;">{{ __('interface.email.checkout.connection_price') }}</td>
        <td style="padding:6px 8px;">{{ $fmtMoney($details['connection_price'] ?? null) }}</td>
    </tr>
    <tr>
        <td style="padding:6px 8px;font-weight:bold;">{{ __('interface.email.checkout.total') }}</td>
        <td style="padding:6px 8px;font-weight:bold;">{{ $fmtMoney($details['total_price'] ?? null) }}</td>
    </tr>
    @if (!empty($details['expiry']))
        <tr>
            <td style="padding:6px 8px;font-weight:bold;">{{ __('interface.email.checkout.expiry') }}</td>
            <td style="padding:6px 8px;">{{ $details['expiry'] }}</td>
        </tr>
    @endif
    @if (!empty($details['notes']))
        <tr>
            <td style="padding:6px 8px;font-weight:bold;">{{ __('interface.email.checkout.notes') }}</td>
            <td style="padding:6px 8px;">{{ $details['notes'] }}</td>
        </tr>
    @endif
</table>

@if ($isAdmin)
    <p style="margin:12px 0 0 0;">{{ __('interface.email.checkout.admin_follow_up') }}</p>
@else
    <p style="margin:12px 0 0 0;">{{ __('interface.email.checkout.customer_follow_up') }}</p>
@endif
