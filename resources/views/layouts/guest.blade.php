{{-- Owner: Royvil — public layout (welcome, lookup, login, errors) --}}
<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.head')
    <title>@yield('title', 'Welcome') · {{ \App\Models\Setting::get('clinic_name', 'Dialysis Center') }}</title>
</head>
<body>
@if (session('status'))
    <div class="alert alert-success m-3">{{ session('status') }}</div>
@endif
@yield('content')
@include('partials.chatbot-widget')
@include('partials.scripts')
@stack('scripts')
</body>
</html>