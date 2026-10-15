@extends('pages.account.layout')
@section('title', 'Sign in')
@section('content')
<div class="account-auth">
    <p class="account-eyebrow">Your customer space</p>
    <h1>Welcome back.</h1>
    <p class="account-muted account-lead">Orders, activation updates and support, together. Sign in with the email you used at checkout.</p>
    <div class="account-card">
        <h2>No password to remember</h2>
        <p class="account-muted">We will send a private sign-in link to your inbox. New here? Verifying your email also creates your customer account.</p>
        <form method="post" action="{{ route('customer.access.request') }}">
            @csrf
            <div class="account-field"><label for="customer-email">Email address</label>
                <input id="customer-email" type="email" name="email" required maxlength="191" autocomplete="email" inputmode="email" value="{{ old('email') }}" aria-describedby="email-help" @error('email') aria-invalid="true" @enderror>
                <small id="email-help">Use your own email. Never share your sign-in link.</small>
            </div>
            <button class="account-button" type="submit">Email me a sign-in link</button>
        </form>
    </div>
    @if(app()->environment('local'))<p class="account-muted"><strong>Local preview:</strong> Access emails are written to the local mail log; no real email is sent.</p>@endif
    <p class="account-muted">You can still browse plans and use guest checkout without signing in.</p>
</div>
@endsection
