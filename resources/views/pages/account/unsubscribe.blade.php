@extends('pages.account.layout')
@section('title', 'Expiry reminder preference')
@section('content')
<div class="account-auth account-card"><p class="account-eyebrow">Your choice</p><h1>Expiry reminders</h1>
    @if($confirmed)<p role="status">Expiry reminder emails are now turned off. Your service and other notification preferences are unchanged.</p>
    @else<p>Turn off emails reminding you to renew before expiry? This does not cancel your service.</p><form method="post" action="{{ $action }}" data-api-form>@csrf<button type="submit" class="account-button">Turn off expiry emails</button></form>@endif
    <p style="margin-top:20px"><a href="{{ route('customer.account') }}">Go to my account</a></p>
</div>
@endsection
