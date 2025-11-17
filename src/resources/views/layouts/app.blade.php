<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - {{ config('app.name') }}</title>
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    @stack('styles')
</head>
<body>
    @include('layouts.header')
    
    <main class="container-fluid py-4">
        @yield('content')
    </main>
    
    @include('layouts.footer')
    
    <!-- Bootstrap 5.3 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Theme Switcher Script -->
    <script>
        // Theme management
        const html = document.documentElement;
        const themeToggle = document.getElementById('theme-toggle');
        const themeIcon = document.getElementById('theme-icon');
        
        // Theme icons
        const icons = {
            light: 'bi-sun-fill',
            dark: 'bi-moon-stars-fill',
            auto: 'bi-circle-half'
        };
        
        // Get saved theme or default to dark
        let currentTheme = localStorage.getItem('theme') || 'dark';
        
        // Apply theme on page load
        function applyTheme(theme) {
            if (theme === 'auto') {
                const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                html.setAttribute('data-bs-theme', systemTheme);
            } else {
                html.setAttribute('data-bs-theme', theme);
            }
            
            // Update icon
            themeIcon.className = `bi ${icons[theme]}`;
            
            // Save to localStorage
            localStorage.setItem('theme', theme);
            currentTheme = theme;
        }
        
        // Apply saved theme
        applyTheme(currentTheme);
        
        // Theme toggle click handler
        if (themeToggle) {
            themeToggle.addEventListener('click', () => {
                // Cycle through themes: light -> dark -> auto -> light
                let nextTheme;
                if (currentTheme === 'light') {
                    nextTheme = 'dark';
                } else if (currentTheme === 'dark') {
                    nextTheme = 'auto';
                } else {
                    nextTheme = 'light';
                }
                
                applyTheme(nextTheme);
            });
        }
        
        // Listen for system theme changes when in auto mode
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (currentTheme === 'auto') {
                html.setAttribute('data-bs-theme', e.matches ? 'dark' : 'light');
            }
        });
    </script>
    
    @stack('scripts')
</body>
</html>
