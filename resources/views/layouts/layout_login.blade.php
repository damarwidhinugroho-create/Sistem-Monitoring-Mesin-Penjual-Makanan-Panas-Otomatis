<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Login Operator')</title>
    <script>
        try {
            if (localStorage.getItem('operator-theme') === 'dark') {
                document.documentElement.dataset.theme = 'dark';
            }
        } catch (_) {}
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/gaya-umum.css', 'resources/js/app.js', 'resources/js/pageMotion.js'])
    @stack('styles')
</head>
<body>
    <div class="initial-page-loader" data-initial-page-loader role="status" aria-live="polite">
        <span class="initial-page-loader__mark" aria-hidden="true"></span>
        <span>Memuat SAJI...</span>
    </div>
    <main class="guest-layout">
        @yield('content')
    </main>
</body>
</html>
