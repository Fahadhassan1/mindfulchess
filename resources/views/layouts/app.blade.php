<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Mindful Chess') }}</title>
        
        <!-- Favicon -->
        <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
        <link rel="icon" href="{{ asset('images/favicon.png') }}" type="image/png">
        <link rel="shortcut icon" href="{{ asset('images/favicon.png') }}" type="image/png">
        <link rel="apple-touch-icon" href="{{ asset('images/favicon.png') }}">
        <meta name="msapplication-TileImage" content="{{ asset('images/favicon.png') }}">
        <meta name="msapplication-TileColor" content="#532563">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @hasSection('page_header')
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        @yield('page_header')
                    </div>
                </header>
            @elseif (isset($header))
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Alert Messages -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                @include('components.alerts')
            </div>

            <!-- Page Content -->
            <main>
                 {{ $slot }}
            </main>
        </div>
        <!-- Alert Auto-close Script -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Auto-close alerts after 5 seconds
                const alerts = document.querySelectorAll('.alert-success, .alert-error, .alert-warning, .alert-info');
                
                alerts.forEach(function(alert) {
                    setTimeout(function() {
                        alert.style.opacity = '0';
                        alert.style.transition = 'opacity 1s';
                        
                        setTimeout(function() {
                            alert.style.display = 'none';
                        }, 1000);
                    }, 5000);
                });
            });
        </script>
    </body>
</html>
