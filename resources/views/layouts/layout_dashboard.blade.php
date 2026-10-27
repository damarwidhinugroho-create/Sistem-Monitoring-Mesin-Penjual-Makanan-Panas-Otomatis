<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard Operator')</title>
    <script>
        const savedOperatorTheme = localStorage.getItem('operator-theme');
        if (savedOperatorTheme === 'dark') {
            document.documentElement.dataset.theme = 'dark';
        }
        document.documentElement.style.backgroundColor = savedOperatorTheme === 'dark' ? '#111827' : '#F3F4F6';
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/gaya-umum.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body>
    <div class="operator-layout">
        <x-sidebar />
        <main class="operator-main">
            <section class="operator-content">
                @yield('content')
            </section>
        </main>
    </div>
</body>
</html>
