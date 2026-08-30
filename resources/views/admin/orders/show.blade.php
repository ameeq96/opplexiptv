@extends('admin.layouts.app')

@section('page_title', 'Order #' . $order->id . ' Details')

@php
    $user = $order->user;
    $payment = $order->payment_method ?: ($order->custom_payment_method ?? 'N/A');
    $packageName = $order->package === 'other' ? ($order->custom_package ?? 'Custom') : $order->package;
    $packageName = str_ireplace('starshare', 'Filex', $packageName);
    $phoneRaw   = $user?->phone ?? '';
    $phoneClean = preg_replace('/\D+/', '', $phoneRaw);
    $waText     = rawurlencode("Hello {$user?->name}, about order #{$order->id}");
    $waUniversal = $phoneClean ? "https://wa.me/{$phoneClean}?text={$waText}" : null; // general/fallback
    $waBusinessAndroid = $phoneClean
        ? "intent://send/?phone={$phoneClean}&text={$waText}#Intent;scheme=whatsapp;package=com.whatsapp.w4b;end"
        : null;
@endphp

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-1">Order #{{ $order->id }}</h4>
            <div class="text-muted">Created {{ $order->created_at?->diffForHumans() }}</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ $isReseller ? route('admin.panel-orders.index') : route('admin.orders.index') }}"
               class="btn btn-outline-secondary">
                Back
            </a>
            <a href="{{ $isReseller ? route('admin.panel-orders.edit', $order) : route('admin.orders.edit', $order) }}"
               class="btn btn-primary">
                Edit
            </a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="badge {{ $isReseller ? 'bg-warning text-dark' : 'bg-primary' }}">
                            {{ $isReseller ? 'Reseller' : 'Package' }}
                        </span>
                        <span class="badge bg-light text-dark text-uppercase">{{ $order->status }}</span>
                        @if ($order->messaged_at)
                            <span class="badge bg-success">Messaged</span>
                        @endif
                    </div>

                    <h5 class="mb-3">{{ $packageName ?? 'N/A' }}</h5>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="small text-muted">Client</div>
                            <div class="fw-semibold">{{ $user?->name }}</div>
                            <div class="text-muted">{{ $user?->email }}</div>
                            @if ($waUniversal && $user?->phone)
                                <a href="javascript:void(0);"
                                   onclick="openWA('{{ $waBusinessAndroid }}','{{ $waUniversal }}')"
                                   class="text-decoration-none d-inline-flex align-items-center gap-1">
                                    <span class="text-muted">{{ $user->phone }}</span>
                                    <i class="bi bi-whatsapp text-success"></i>
                                </a>
                            @else
                                <div class="text-muted">{{ $user?->phone ?: 'N/A' }}</div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <div class="small text-muted">IPTV Username</div>
                            <div class="fw-semibold">{{ $order->iptv_username ?: 'N/A' }}</div>
                            @if ($order->device_id)
                                <div class="text-muted small">Device ID: {{ $order->device_id }}</div>
                            @endif
                        </div>

                        <div class="col-md-4">
                            <div class="small text-muted">Price</div>
                            <div class="fw-semibold">{{ $order->currency }} {{ number_format($order->price, 2) }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="small text-muted">Sell Price</div>
                            <div class="fw-semibold">
                                {{ $order->currency }} {{ number_format($order->sell_price ?? $order->price, 2) }}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="small text-muted">Profit</div>
                            <div class="fw-semibold">
                                {{ $order->currency }} {{ number_format($order->profit ?? 0, 2) }}
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="small text-muted">Payment Method</div>
                            <div class="fw-semibold">{{ $payment }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="small text-muted">Payment Status</div>
                            <div class="fw-semibold text-uppercase">{{ $order->payment_status }}</div>
                            <div class="text-muted small">{{ $order->payment_provider ?: 'Not verified' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="small text-muted">GA4 Purchase</div>
                            <div class="fw-semibold">{{ $order->ga_purchase_sent_at ? 'Sent' : 'Not sent' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="small text-muted">Buying Date</div>
                            <div class="fw-semibold">{{ $order->buying_date ?? 'N/A' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="small text-muted">Expiry Date</div>
                            <div class="fw-semibold">{{ $order->expiry_date ?? 'N/A' }}</div>
                        </div>

                        <div class="col-md-4">
                            <div class="small text-muted">Duration</div>
                            <div class="fw-semibold">{{ $order->duration ? $order->duration . ' days' : 'N/A' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="small text-muted">Credits</div>
                            <div class="fw-semibold">{{ $order->credits ?? 'N/A' }}</div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <div class="small text-muted mb-1">Notes</div>
                        <div class="border rounded p-2" style="min-height:60px;">
                            {{ $order->note ?: 'No notes added.' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <h6 class="mb-3">Status</h6>
                    <div class="d-flex flex-column gap-1">
                        <div><strong>Current:</strong> {{ ucfirst($order->status) }}</div>
                        <div><strong>Messaged At:</strong> {{ $order->messaged_at ?? 'N/A' }}</div>
                        <div><strong>Created:</strong> {{ $order->created_at }}</div>
                        <div><strong>Updated:</strong> {{ $order->updated_at }}</div>
                        @if ($order->referral)
                            <div><strong>Referral Code:</strong> {{ $order->referral->code }}</div>
                            <div><strong>Referral Status:</strong> {{ ucfirst($order->referral->status) }}</div>
                        @endif
                        @if ($order->referredBy)
                            <div><strong>Referred By:</strong> {{ $order->referredBy->code }}</div>
                            <div><strong>Qualification:</strong> {{ ucfirst($order->referredBy->status) }}</div>
                        @endif
                    </div>
                    @if ($order->payment_status !== 'paid')
                        <form method="POST" action="{{ route('admin.orders.verifyPayment', $order) }}" class="mt-3">
                            @csrf
                            <label class="form-label" for="manualTransactionId">Payment reference</label>
                            <input id="manualTransactionId" name="transaction_id" type="text" maxlength="191"
                                class="form-control mb-2" required placeholder="Bank, card, or crypto reference">
                            <button type="submit" class="btn btn-success w-100"
                                onclick="return confirm('Confirm that this payment has been independently verified?')">
                                Verify Payment
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="mb-3">Attachments</h6>
                    @if ($order->pictures && $order->pictures->count())
                        <div class="list-group">
                            @foreach ($order->pictures as $pic)
                                <a class="list-group-item list-group-item-action" href="{{ asset($pic->path) }}"
                                   target="_blank" rel="noopener">
                                    Screenshot #{{ $loop->iteration }}
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="text-muted">No attachments uploaded.</div>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm mt-3">
                <div class="card-body">
                    <h6 class="mb-3">Lifecycle Messages</h6>
                    @forelse ($order->marketingDeliveries as $delivery)
                        <div class="d-flex justify-content-between gap-2 border-bottom py-2 small">
                            <span>{{ ucfirst($delivery->workflow) }} / {{ $delivery->channel }}</span>
                            <strong>{{ $delivery->sent_at ? 'Sent' : ($delivery->failed_at ? 'Stopped' : 'Queued') }}</strong>
                        </div>
                    @empty
                        <div class="text-muted">No consented lifecycle messages.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <script>
        function openWA(androidBusiness, fallback) {
            if (androidBusiness && /Android/i.test(navigator.userAgent)) {
                window.location.href = androidBusiness;
                setTimeout(function() { window.location.href = fallback; }, 400);
            } else if (fallback) {
                window.location.href = fallback;
            }
        }
    </script>
@endsection
