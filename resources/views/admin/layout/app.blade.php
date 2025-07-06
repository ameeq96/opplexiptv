<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Opplex Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
        }
        .sidebar {
            width: 220px;
            height: 100vh;
            background: #343a40;
            color: white;
            position: fixed;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            padding: 12px 20px;
            display: block;
        }
        .sidebar a:hover {
            background: #495057;
        }
        .content {
            margin-left: 220px;
            padding: 20px;
            width: 100%;
        }
    </style>
</head>
<body>
    @include('admin.layout.sidebar')
    <div class="content">
        @include('admin.layout.header')
        <main>
            @yield('content')
        </main>
    </div>
</body>
</html>
