{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Dialysis Center')</title>
</head>
<body>
    <nav style="padding:1rem;border-bottom:1px solid #ccc;">
        <strong>Dialysis Center</strong> —
        {{ auth()->user()->name ?? '' }} ({{ auth()->user()->role ?? '' }})
        <form method="POST" action="{{ route('logout') }}" style="display:inline;">
            @csrf
            <button type="submit">Logout</button>
        </form>
    </nav>

    <main style="padding:1rem;">
        @if (session('success'))
            <p style="color:green;">{{ session('success') }}</p>
        @endif

        @yield('content')
    </main>
</body>
</html>