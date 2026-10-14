@extends('admin.layouts.app')

@section('page_title', 'Review unlinked customer activity')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div><h3 class="mb-1">Unlinked activity review</h3><p class="text-muted mb-0">Preview first. Link one verified record at a time.</p></div>
        <a href="{{ route('admin.clients.index') }}" class="btn btn-outline-secondary">Back to customers</a>
    </div>
    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <div class="alert alert-warning">
        This preview does not change data. Names alone are not identity proof. Verify the contact against a trusted record before linking; historical imports may contain generated or unverified email addresses. Conflicting/shared contacts remain separate. This tool never merges or deletes customers and never changes an existing non-empty customer link, payment, start date or end date.
    </div>
    <form method="GET" action="{{ route('admin.clients.unlinked') }}" class="admin-card d-flex flex-wrap gap-3 mb-4">
        <label for="review-type" class="form-label mb-0 align-self-center">Record type</label>
        <select id="review-type" name="type" class="form-select w-auto">
            @foreach (['trial_click' => 'Captured leads', 'checkout_draft' => 'Checkout drafts', 'digital_order' => 'Unlinked digital orders', 'conversation' => 'Captured conversations', 'activity' => 'Activity events'] as $value => $label)
                <option value="{{ $value }}" @selected($type === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <button class="btn btn-primary" type="submit">Preview</button>
    </form>
    @if ($selectedConversation)
        <section class="admin-card mb-4">
            <h4>Captured transcript #{{ $selectedConversation->id }}</h4>
            <p class="text-muted">Read-only review. This conversation remains unlinked until you verify and link its record below.</p>
            @forelse ($unlinkedMessages as $message)
                <div class="border rounded p-3 mb-2">
                    <div class="small text-muted mb-1">{{ $message->sender_name ?: ucfirst($message->sender_type) }} · {{ $message->sent_at?->format('Y-m-d H:i') ?: 'Time not supplied' }}</div>
                    <div style="white-space: pre-wrap; overflow-wrap: anywhere;">{{ $message->content }}</div>
                </div>
            @empty<p class="text-muted">No captured messages are available for this conversation.</p>@endforelse
            {{ $unlinkedMessages->links('pagination::bootstrap-5') }}
        </section>
    @endif
    @forelse ($previews as $preview)
        <article class="admin-card mb-3">
            <div class="d-flex flex-wrap justify-content-between gap-3 mb-3">
                <div><h5>{{ str_replace('_', ' ', ucfirst($type)) }} #{{ $preview['record']->id }}</h5><div>{{ $preview['name'] ?: 'Name not supplied' }}</div><div class="text-muted">{{ $preview['email'] ?: 'Email not supplied' }} · {{ $preview['phone'] ?: 'Phone not supplied' }}</div></div>
                <div class="small text-muted">Recorded {{ $preview['record']->created_at?->format('Y-m-d H:i') }}</div>
            </div>
            @if ($type === 'conversation')
                <p><a class="btn btn-outline-secondary btn-sm" href="{{ route('admin.clients.unlinked', ['type' => 'conversation', 'conversation_id' => $preview['record']->id, 'page' => $records->currentPage()]) }}">Read captured messages before linking</a></p>
            @elseif ($type === 'activity')
                <div class="border rounded p-3 mb-3">
                    <div class="small text-muted">{{ $preview['record']->source }} · {{ $preview['record']->event_type }} · {{ $preview['record']->occurred_at?->format('Y-m-d H:i') }}</div>
                    @if ($preview['record']->message)<div class="mt-2" style="white-space: pre-wrap; overflow-wrap: anywhere;">{{ $preview['record']->message }}</div>@endif
                    @foreach (['status', 'invoice_status', 'recipient', 'reference', 'tracker', 'error_code', 'subject'] as $detailKey)
                        @if (is_scalar(data_get($preview['record']->details, $detailKey)))<div class="small mt-1">{{ ucfirst(str_replace('_', ' ', $detailKey)) }}: {{ data_get($preview['record']->details, $detailKey) }}</div>@endif
                    @endforeach
                    @if (is_numeric(data_get($preview['record']->details, 'amount_minor')) && is_string(data_get($preview['record']->details, 'currency')))
                        <div class="small mt-1">Amount: {{ data_get($preview['record']->details, 'currency') }} {{ number_format(data_get($preview['record']->details, 'amount_minor') / 100, 2) }}</div>
                    @endif
                </div>
            @endif
            <p>{{ $preview['reason'] }}</p>
            @if ($preview['candidate'])
                <div class="border rounded p-3 mb-3"><strong>Candidate: <a href="{{ route('admin.clients.show', $preview['candidate']) }}">{{ $preview['candidate']->name ?: 'Unnamed customer' }} #{{ $preview['candidate']->id }}</a></strong><div>{{ $preview['candidate']->email ?: 'No email' }} · {{ $preview['candidate']->phone ?: 'No phone' }}</div></div>
                <form action="{{ route('admin.clients.link-activity') }}" method="POST">
                    @csrf
                    <input type="hidden" name="type" value="{{ $type }}">
                    <input type="hidden" name="record_id" value="{{ $preview['record']->id }}">
                    <input type="hidden" name="user_id" value="{{ $preview['candidate']->id }}">
                    <input type="hidden" name="fingerprint" value="{{ $preview['fingerprint'] }}">
                    <div class="form-check mb-3"><input id="verified-{{ $preview['record']->id }}" name="verified" value="1" type="checkbox" class="form-check-input" required><label for="verified-{{ $preview['record']->id }}" class="form-check-label">I verified that this record belongs to this customer; this is not a guessed or generated contact match.</label></div>
                    <button type="submit" class="btn btn-outline-primary">Link this verified record only</button>
                </form>
            @else
                <div class="text-muted">No automatic link is available. Verify or correct the source contact through its normal workflow, then preview again.</div>
            @endif
        </article>
    @empty<div class="admin-card text-muted">No unlinked records of this type.</div>@endforelse
    {{ $records->links('pagination::bootstrap-5') }}
@endsection
