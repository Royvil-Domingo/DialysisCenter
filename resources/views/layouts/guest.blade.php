<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Dialysis Center')</title>
</head>
<body>
    <main style="max-width:400px;margin:4rem auto;padding:1rem;">
        @if (session('status'))
            <p style="color:green;">{{ session('status') }}</p>
        @endif

        @yield('content')
    </main>
</body>
</html>