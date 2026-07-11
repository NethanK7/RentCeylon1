<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'RentCeylon') }}</title>
        <meta name="description" content="RentCeylon — rent anything and everything in Sri Lanka. Cameras, vehicles, tools, event gear and more, with deposit protection and verified listers.">
        <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100' fill='none'%3E%3Cpath d='M28,32 C14,42 10,60 28,76 C30,62 30,48 28,32 Z' fill='%23AFCBEF'/%3E%3Cpath d='M72,32 C86,42 90,60 72,76 C70,62 70,48 72,32 Z' fill='%23AFCBEF'/%3E%3Cpath d='M37,14 C23,28 19,55 35,78 C40,60 42,35 37,14 Z' fill='%233868AE'/%3E%3Cpath d='M63,14 C77,28 81,55 65,78 C60,60 58,35 63,14 Z' fill='%233868AE'/%3E%3Cpath d='M50,7 C39,25 37,55 50,80 C63,55 61,25 50,7 Z' fill='%23123063'/%3E%3Ccircle cx='50' cy='80' r='16' fill='%23C6900F'/%3E%3Ccircle cx='50' cy='80' r='10.5' fill='%23EFC468'/%3E%3Ccircle cx='50' cy='80' r='4' fill='%23123063'/%3E%3C/svg%3E">

        <!-- Scripts -->
        @routes
        @viteReactRefresh
        @vite(['resources/js/app.tsx', "resources/js/Pages/{$page['component']}.tsx"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
