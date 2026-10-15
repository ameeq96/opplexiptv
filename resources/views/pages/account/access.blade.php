@extends('pages.account.layout')
@section('title', 'Confirm sign-in')
@section('content')
<div class="account-auth account-card">
    <p class="account-eyebrow">Private access</p><h1>Ready to sign in?</h1>
    <p class="account-muted">Continue to open the account for the email that received this link. Opening this page alone does not use your link.</p>
    @if(! $errors->any())
    <form method="post" action="{{ $action }}">@csrf<button type="submit" class="account-button">Open my account</button></form>
    @endif
    <p style="margin-top:20px"><a href="{{ route('customer.login') }}">Request a new sign-in link</a></p>
</div>
@endsection
