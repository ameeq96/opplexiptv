<!DOCTYPE html>
<html class="loading" lang="en" data-textdirection="ltr">

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
