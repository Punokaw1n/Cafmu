<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $currentTenant->name ?? 'Menu' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Dynamic Theme Variables -->
    @php
        $primaryColor = App::bound('currentTenant') ? App::make('currentTenant')->getSetting('primary_color', '#d97706') : '#d97706';
    @endphp
    <style>
        :root {
            --color-primary-50: color-mix(in srgb, {{ $primaryColor }} 10%, white);
            --color-primary-100: color-mix(in srgb, {{ $primaryColor }} 20%, white);
            --color-primary-200: color-mix(in srgb, {{ $primaryColor }} 30%, white);
            --color-primary-500: {{ $primaryColor }};
            --color-primary-600: color-mix(in srgb, {{ $primaryColor }} 80%, black);
            --color-primary-700: color-mix(in srgb, {{ $primaryColor }} 60%, black);
        }
    </style>
</head>
<body class="bg-gray-50 font-sans antialiased">
    @yield('content')
</body>
</html>
