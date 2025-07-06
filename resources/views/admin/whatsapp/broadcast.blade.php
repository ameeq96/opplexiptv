@extends('admin.layout.app')

@section('page_title', 'WhatsApp Broadcast')

@section('content')
    <h4>Send WhatsApp Message to All Clients</h4>

    <button onclick="sendAll()" class="btn btn-success mb-3">Send to All (Open Tabs)</button>

    <ul class="list-group">
        @foreach($users as $user)
            @php
                $phone = preg_replace('/[^0-9]/', '', $user->phone);
                $msg = urlencode("Hello {$user->name}, your IPTV service update from Opplex.");
                $url = "https://wa.me/{$phone}?text={$msg}";
            @endphp
            <li class="list-group-item d-flex justify-content-between align-items-center">
                {{ $user->name }} ({{ $user->phone }})
                <a href="{{ $url }}" target="_blank" class="btn btn-sm btn-success">Send</a>
            </li>
        @endforeach
    </ul>

    <script>
        function sendAll() {
            const links = document.querySelectorAll('.list-group-item a');
            links.forEach(link => {
                window.open(link.href, '_blank');
            });
        }
    </script>
@endsection
