@extends('pages.account.layout')
@section('title', 'Setup guides')
@section('content')
    <div class="account-card">
        <h1>Get connected, one step at a time</h1>
        <p class="account-muted">Choose your device below. These guides are optional: you do not need to select a device to pay or renew.</p>
        <p>Wait until your service is marked active and you have received the connection details. An app installation alone does not activate your service.</p>
        <a class="account-button" href="{{ route('customer.account') }}">Back to My Account</a>
    </div>
    <div class="account-grid" style="grid-template-columns:minmax(0,1fr);">
        @foreach ([
            'Android / Android TV' => [
                'Open Google Play on your phone, tablet or TV. Use the player recommended for your service in our Applications guide.',
                'Install the player from its official store listing. Avoid unknown APK download sites.',
            ],
            'Fire TV / Firestick' => [
                'From the Fire TV home screen, open Find and search for the recommended player in the Amazon Appstore.',
                'If that player is not available, contact support for the supported installation method for your device. Do not install an unverified file.',
                'Wait for the content list to load. Check that the device is connected to a stable Wi-Fi or Ethernet connection.',
            ],
            'Smart TV' => [
                'Check your TV brand and model. Available players vary between Samsung, LG, Android TV and other systems.',
                'Open your TV app store and install a player confirmed as compatible with your service. Ask support if unsure.',
                'Some players require separate activation; check before paying for an app.',
                'Keep your connection details private.',
            ],
            'iPhone / iPad' => [
                'Open the App Store and find the player recommended for your service.',
                'Install the official listing.',
                'Never post your connection details publicly.',
                'Allow the content list to load, then try playback on Wi-Fi. Your mobile data provider may charge for streaming over cellular data.',
            ],
            'Windows / Mac' => [
                'Choose a supported desktop player from our Applications guide. Download only from the official publisher or operating-system app store.',
                'Install and open the player.',
                'Do not send passwords or playlist URLs in public screenshots.',
                'If playback fails, record the player name and exact error message, then contact support through My Account.',
            ],
        ] as $device => $steps)
            <details class="account-card">
                <summary><strong>{{ $device }}</strong></summary>
                <ol>
                    @foreach ($steps as $step)
                        <li>{{ $step }}</li>
                    @endforeach
                </ol>
            </details>
        @endforeach
        <div class="account-card">
            <h2>Need help?</h2>
            <p>Tell support your device model, player name and error message. Do not include your password, card details or one-time verification codes.</p>
            <div class="account-actions">
                <a class="account-button" href="{{ url('/iptv-applications') }}">Applications guide</a>
                <a class="account-button" href="{{ route('customer.account') }}">My Account &amp; support</a>
            </div>
        </div>
    </div>
@endsection
