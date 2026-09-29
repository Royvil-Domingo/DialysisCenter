{{-- Owner: Royvil — authenticated layout (sidebar + topbar) --}}
<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.head')
    <title>@yield('title', 'Dashboard') · {{ \App\Models\Setting::get('clinic_name', 'Dialysis Center') }}</title>
</head>
<body>
<div class="d-lg-flex">
    @include('partials.sidebar')
    <div class="flex-grow-1 min-w-0">
        @include('partials.topbar')
        <main class="p-3 p-md-4">
            @include('partials.flash')
            @yield('content')
        </main>
    </div>
</div>
@include('partials.logout-modal')
@include('partials.chatbot-widget')
@include('partials.scripts')
@stack('scripts')
</body>
</html>