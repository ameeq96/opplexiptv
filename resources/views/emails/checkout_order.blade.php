@php
    $currency = '$';
    $fmtMoney = function ($val) use ($currency) {
        if ($val === null || $val === '') return 'N/A';
        return $currency . number_format((float) $val, 2);
    };
    $title = $isAdmin
        ? 'A new checkout order was placed.'
        : 'Thanks for your order! We have received the details below.';
@endphp

<h2 style="margin:0 0 12px 0;">{{ $title }}</h2>

<p style="margin:0 0 12px 0;">
    @if ($isAdmin)
        A customer just completed the checkout form. Here is the summary.
    @else
        We will process your request shortly. A team member will reach out if anything else is needed.
    @endif
</p>

<table style="width:100%;max-width:520px;border-collapse:collapse;font-family:Arial,sans-serif;font-size:14px;">
    <tr>
        <td style="padding:6px 8px;font-weight:bold;">Order #</td>
        <td style="padding:6px 8px;">{{ $details['order_id'] ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td style="padding:6px 8px;font-weight:bold;">Customer</td>
        <td style="padding:6px 8px;">
            {{ $details['customer_name'] ?? 'N/A' }}<br>
            {{ $details['customer_email'] ?? '' }}
        </td>
    </tr>
    <tr>
        <td style="padding:6px 8px;font-weight:bold;">Phone</td>
        <td style="padding:6px 8px;">{{ $details['phone'] ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td style="padding:6px 8px;font-weight:bold;">Package</td>
        <td style="padding:6px 8px;">{{ $details['package'] ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td style="padding:6px 8px;font-weight:bold;">Type</td>
        <td style="padding:6px 8px;">{{ ucfirst($details['package_type'] ?? '') }}</td>
    </tr>
    @if (!empty($details['vendor']))
        <tr>
            <td style="padding:6px 8px;font-weight:bold;">Provider</td>
            <td style="padding:6px 8px;">{{ ucfirst($details['vendor']) }}</td>
        </tr>
    @endif
    @if (!empty($details['device']))
        <tr>
            <td style="padding:6px 8px;font-weight:bold;">Device</td>
            <td style="padding:6px 8px;">{{ $details['device'] }}</td>
        </tr>
    @endif
    <tr>
        <td style="padding:6px 8px;font-weight:bold;">Quantity</td>
        <td style="padding:6px 8px;">{{ $details['quantity'] ?? 1 }}</td>
    </tr>
    <tr>
        <td style="padding:6px 8px;font-weight:bold;">Payment Method</td>
        <td style="padding:6px 8px;">{{ strtoupper($details['payment_method'] ?? '') }}</td>
    </tr>
    <tr>
        <td style="padding:6px 8px;font-weight:bold;">Subscription Price</td>
        <td style="padding:6px 8px;">{{ $fmtMoney($details['subscription_price'] ?? $details['unit_price'] ?? null) }}</td>
    </tr>
    <tr>
        <td style="padding:6px 8px;font-weight:bold;">Connection Price</td>
        <td style="padding:6px 8px;">{{ $fmtMoney($details['connection_price'] ?? null) }}</td>
    </tr>
    <tr>
        <td style="padding:6px 8px;font-weight:bold;">Total</td>
        <td style="padding:6px 8px;font-weight:bold;">{{ $fmtMoney($details['total_price'] ?? null) }}</td>
    </tr>
    @if (!empty($details['expiry']))
        <tr>
            <td style="padding:6px 8px;font-weight:bold;">Expiry</td>
            <td style="padding:6px 8px;">{{ $details['expiry'] }}</td>
        </tr>
    @endif
    @if (!empty($details['notes']))
        <tr>
            <td style="padding:6px 8px;font-weight:bold;">Notes</td>
            <td style="padding:6px 8px;">{{ $details['notes'] }}</td>
        </tr>
    @endif
</table>

@if ($isAdmin)
    <p style="margin:12px 0 0 0;">Please follow up with the customer and complete activation.</p>
@else
    <p style="margin:12px 0 0 0;">If you have any questions, just reply to this email.</p>
@endif
