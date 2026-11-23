<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') · SAJI</title>
    @vite(['resources/css/gaya-umum.css', 'resources/js/app.js', 'resources/js/pageMotion.js'])
</head>
<body>
    <main class="error-page">
        @yield('content')
    </main>
</body>
</html>
