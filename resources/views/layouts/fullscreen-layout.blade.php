<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Auth' }} | {{ config('app.name', 'TailAdmin') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('theme', {
                init() {
                    const savedTheme = localStorage.getItem('theme');
                    this.theme = savedTheme || 'light';
                    this.updateTheme();
                },
                theme: 'light',
                toggle() {
                    this.theme = this.theme === 'light' ? 'dark' : 'light';
                    localStorage.setItem('theme', this.theme);
                    this.updateTheme();
                },
                updateTheme() {
                    const html = document.documentElement;

                    if (this.theme === 'dark') {
                        html.classList.add('dark');
                        document.body?.classList.add('dark', 'bg-gray-900');
                    } else {
                        html.classList.remove('dark');
                        document.body?.classList.remove('dark', 'bg-gray-900');
                    }
                }
            });
        });
    </script>

    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme');
            const theme = savedTheme || 'light';
            const html = document.documentElement;

            if (theme === 'dark') {
                html.classList.add('dark');
            } else {
                html.classList.remove('dark');
            }
        })();
    </script>
</head>

<body class="min-h-screen">

    <x-common.preloader />

    @yield('content')

    @stack('scripts')
</body>

</html>
