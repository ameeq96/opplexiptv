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
        <div id="supportTickets" aria-busy="true"><p role="status">Loading tickets…</p></div>
        <p><a href="{{ route('customer.support.index') }}#new-support-ticket">Start a new request</a></p>
    </section>
    @if($conversationId)
    <section class="account-card account-support-thread" aria-labelledby="support-thread-title">
        <h2 id="support-thread-title">Ticket #{{ $conversationId }}</h2>
        <p role="status">Status: <strong id="ticketStatus">Loading…</strong></p>
        <p class="account-muted">Open: awaiting a team reply. In progress: our team is working on it. Resolved: the request has been marked complete.</p>
        <div id="supportMessages" aria-busy="true"><p role="status">Loading conversation…</p></div>
        <form action="{{ route('api.browser.customer.support.reply', $conversationId) }}" method="POST" data-api-form id="supportReply" hidden>
            @csrf
            <input type="hidden" name="submission_id" value="{{ (string) \Illuminate\Support\Str::uuid() }}">
            <div class="account-field"><label for="support-reply">Your reply</label><textarea id="support-reply" name="message" rows="5" maxlength="5000" required aria-describedby="reply-help"></textarea></div>
            <p class="account-muted" id="reply-help">Maximum 5,000 characters. Do not send passwords, card details or one-time codes. A new reply reopens a resolved request.</p>
            <button class="account-button" type="submit">Send reply</button>
        </form>
        <p><button class="account-button secondary" type="button" id="refreshSupport">Refresh to check for replies</button></p>
    </section>
    @else
    <section class="account-card account-support-thread" id="new-support-ticket" aria-labelledby="new-support-title">
        <h2 id="new-support-title">How can we help?</h2>
        <form action="{{ route('api.browser.customer.support.store') }}" method="POST" data-api-form>
            @csrf
            <input type="hidden" name="submission_id" value="{{ (string) \Illuminate\Support\Str::uuid() }}">
            <div class="account-field"><label for="support-message">Describe your question or issue</label><textarea id="support-message" name="message" rows="6" maxlength="5000" required aria-describedby="support-message-help" placeholder="Tell us what happened and, if relevant, your order number."></textarea></div>
            <p class="account-muted" id="support-message-help">Maximum 5,000 characters. Do not send passwords, card details or one-time codes.</p>
            <button class="account-button" type="submit">Create support request</button>
        </form>
    </section>
    @endif
</div>
<noscript><p>Please enable JavaScript to securely load support requests.</p></noscript>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const api = AccountPortal, text = api.escape, conversation = @json($conversationId);
    async function tickets(page = 1) {
        const target = document.getElementById('supportTickets'); target.setAttribute('aria-busy','true');
        try {
            const response = await api.load(@json(route('api.browser.customer.support.index')) + '?page=' + page);
            target.innerHTML = (response.data.length ? response.data.map(ticket => '<p><a class="account-link" href="' + api.href(ticket.url) + '"' + (ticket.id === conversation ? ' aria-current="page"' : '') + '>Ticket #' + text(ticket.id) + '</a><span> — ' + text(ticket.status.replaceAll('_',' ')) + '</span><br><time class="account-muted">Updated ' + text(api.date(ticket.updated_at,true)) + '</time></p>').join('') : '<p class="account-muted">No support requests yet. Use the form whenever you need help.</p>') + api.pagination(response.meta,'tickets');
        } catch (error) { target.textContent = 'Tickets could not be loaded. Refresh to try again.'; api.error(error); }
        finally { target.setAttribute('aria-busy','false'); }
    }
    async function messages(page) {
        if (!conversation) return;
        const target = document.getElementById('supportMessages'); target.setAttribute('aria-busy','true');
        try {
            const base = @json(url('/api/browser/account/support')) + '/' + conversation;
            const [ticket,response] = await Promise.all([api.load(base),api.load(base + '/messages' + (page ? '?page=' + page : ''))]);
            document.getElementById('ticketStatus').textContent = ticket.data.status.replaceAll('_',' ');
            document.getElementById('supportReply').hidden = false;
            target.innerHTML = (response.data.length ? response.data.map(message => '<article class="account-support-message ' + (message.sender_type === 'customer' ? 'customer' : '') + '"><strong>' + (message.sender_type === 'customer' ? 'You' : 'Support team') + '</strong><time class="account-muted">' + text(api.date(message.sent_at,true)) + '</time><p>' + text(message.content) + '</p></article>').join('') : '<p class="account-muted">No messages to show.</p>') + api.pagination(response.meta,'messages');
        } catch (error) { target.textContent = 'This conversation is unavailable. Please try again.'; document.getElementById('supportReply').hidden = true; api.error(error); }
        finally { target.setAttribute('aria-busy','false'); }
    }
    document.addEventListener('click', event => { const button = event.target.closest('[data-account-page]'); if (!button) return; if (button.dataset.accountPage === 'tickets') tickets(Number(button.dataset.page)); if (button.dataset.accountPage === 'messages') messages(Number(button.dataset.page)); });
    document.getElementById('refreshSupport')?.addEventListener('click', () => messages());
    document.getElementById('supportReply')?.addEventListener('data-api-success', event => {
        event.target.querySelector('[name="message"]').value = '';
        if (window.crypto?.randomUUID) event.target.querySelector('[name="submission_id"]').value = crypto.randomUUID();
        else { location.reload(); return; }
        messages(); tickets();
    });
    const params = new URL(location.href).searchParams;
    tickets(Number(params.get('tickets_page')) || 1); messages(Number(params.get('messages_page')) || undefined);
});
</script>
@endpush
