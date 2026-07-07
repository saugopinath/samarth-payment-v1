<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ ucfirst(strtolower(config('app.name', 'SAMARTH'))) }} | {{ __('Government of West Bengal') }}</title>

        <!-- Favicon -->
        <link rel="icon" type="image/svg+xml" href="{{ asset('images/emblem.svg') }}">

        <!-- Fonts (Hosted Locally via Vite) -->

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        
        <!-- Light Overlay for Readability -->
        <div class="fixed inset-0 bg-[#FDFBF7]/80 backdrop-blur-[1px] z-[-1]"></div>

        <!-- Full-Page Wrapper -->
        <div class="min-h-screen flex flex-col justify-between relative">
            
            @include('layouts.public.header')


            <!-- Main Content Area -->
            <main class="flex-grow w-full">
                {{ $slot }}
            </main>

            @include('layouts.public.footer')

        </div>

        <!-- Global Loader for Livewire Requests -->
        <x-global-loader />
    </body>
</html>
