<!DOCTYPE html>
<html class="loading" lang="en" data-textdirection="ltr">

@php
    use Jenssegers\Agent\Agent;
    $agent = new Agent();
    $containerClass = $agent->isMobile() ? 'centered' : 'sec-title centered';

@endphp

<head>
    @include('includes.head')
</head>

<body>

    @include('includes.header')

    <div class="body_wrap">

        @yield('content')

    </div>

    @include('includes.footer')

    @yield('script')

    </div>
</body>

</html>
