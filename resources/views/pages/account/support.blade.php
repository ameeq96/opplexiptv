@extends('pages.account.layout')

@section('title', 'Support requests')

@push('styles')
    <style>
        .account-support-grid { grid-template-columns:minmax(0,260px) minmax(0,1fr); align-items:start; }
        .account-support-message { padding:14px; margin:12px 0; border:1px solid var(--line); border-radius:12px; background:#f4f6fa; overflow-wrap:anywhere; }
        .account-support-message.customer { margin-left:16px; background:var(--soft); }
        .account-support-message time { display:block; font-size:12px; }
        .account-support-message p { margin:8px 0 0; white-space:pre-wrap; }
        @media(max-width:760px) { .account-support-grid { grid-template-columns:1fr; } .account-support-list { order:2; } .account-support-thread { order:1; } }
    </style>
@endpush

@section('content')
    <h1>Support requests</h1>
    <p class="account-muted">Send a request, follow its status and continue the conversation here. Replies appear on this page; no WhatsApp message is sent automatically.</p>
    <div class="account-grid account-support-grid">
        <section class="account-card account-support-list" aria-labelledby="support-tickets-title">
            <h2 id="support-tickets-title">Your tickets</h2>
            @forelse ($tickets as $listedTicket)
                <p>
                    <a class="account-link" href="{{ route('customer.support.show', $listedTicket->id) }}" @if ($ticket?->id === $listedTicket->id) aria-current="page" @endif>Ticket #{{ $listedTicket->id }}</a>
                    <span>— {{ ucfirst(str_replace('_', ' ', $listedTicket->status)) }}</span><br>
                    <time class="account-muted" datetime="{{ $listedTicket->updated_at?->toIso8601String() }}">Updated {{ $listedTicket->updated_at?->format('d M Y, H:i') }}</time>
                </p>
            @empty
                <p class="account-muted">No support requests yet. Use the form below whenever you need help.</p>
            @endforelse
            @if ($tickets->hasPages())
                <nav class="account-pagination" aria-label="Support ticket pages">
                    @if ($tickets->previousPageUrl())<a class="account-link" href="{{ $tickets->previousPageUrl() }}" rel="prev">Previous tickets</a>@endif
                    @if ($tickets->nextPageUrl())<a class="account-link" href="{{ $tickets->nextPageUrl() }}" rel="next">More tickets</a>@endif
                </nav>
            @endif
            <p><a href="{{ route('customer.support.index') }}#new-support-ticket">Start a new request</a></p>
        </section>
        @if ($ticket)
            <section class="account-card account-support-thread" aria-labelledby="support-thread-title">
                <h2 id="support-thread-title">Ticket #{{ $ticket->id }}</h2>
                <p role="status">Status: <strong>{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</strong></p>
                <p class="account-muted">Open: awaiting a team reply. In progress: our team is working on it. Resolved: the request has been marked complete.</p>
                @forelse ($messages as $message)
                    <article class="account-support-message {{ $message->sender_type === 'customer' ? 'customer' : '' }}">
                        <strong>{{ $message->sender_type === 'customer' ? 'You' : 'Support team' }}</strong>
                        <time class="account-muted" datetime="{{ $message->sent_at?->toIso8601String() }}">{{ $message->sent_at?->format('d M Y, H:i') }}</time>
                        <p>{{ $message->content }}</p>
                    </article>
                @empty
                    <p class="account-muted">No messages to show.</p>
                @endforelse
                @if ($messages->hasPages())
                    <nav class="account-pagination" aria-label="Conversation history">
                        @if ($messages->previousPageUrl())<a class="account-link" href="{{ $messages->previousPageUrl() }}" rel="prev">Older messages</a>@endif
                        @if ($messages->nextPageUrl())<a class="account-link" href="{{ $messages->nextPageUrl() }}" rel="next">Newer messages</a>@endif
                    </nav>
                @endif
                <form action="{{ route('customer.support.reply', $ticket->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="submission_id" value="{{ old('submission_id', (string) \Illuminate\Support\Str::uuid()) }}">
                    <div class="account-field"><label for="support-reply">Your reply</label>
                    <textarea id="support-reply" name="message" rows="5" maxlength="5000" required aria-describedby="reply-help">{{ old('message') }}</textarea></div>
                    <p class="account-muted" id="reply-help">Maximum 5,000 characters. Do not send passwords, card details or one-time codes. A new reply reopens a resolved request.</p>
                    <button class="account-button" type="submit">Send reply</button>
                </form>
                <p><a href="{{ route('customer.support.show', $ticket->id) }}">Refresh to check for replies</a></p>
            </section>
        @else
            <section class="account-card account-support-thread" id="new-support-ticket" aria-labelledby="new-support-title">
                <h2 id="new-support-title">How can we help?</h2>
                <form action="{{ route('customer.support.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="submission_id" value="{{ old('submission_id', (string) \Illuminate\Support\Str::uuid()) }}">
                    <div class="account-field"><label for="support-message">Describe your question or issue</label>
                    <textarea id="support-message" name="message" rows="6" maxlength="5000" required aria-describedby="support-message-help" placeholder="Tell us what happened and, if relevant, your order number.">{{ old('message') }}</textarea></div>
                    <p class="account-muted" id="support-message-help">Maximum 5,000 characters. Do not send passwords, card details or one-time codes.</p>
                    <button class="account-button" type="submit">Create support request</button>
                </form>
            </section>
        @endif
    </div>
@endsection
