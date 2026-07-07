<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Favicon -->
        <link rel="icon" type="image/svg+xml" href="{{ asset('images/emblem.svg') }}">

        <!-- Fonts (Hosted Locally via Vite) -->

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div x-data="{ sidebarOpen: false }" class="flex h-screen overflow-hidden bg-gray-100 dark:bg-gray-900">
            <!-- Sidebar -->
            <livewire:layout.sidebar />

            <!-- Main Content Area -->
            <div class="flex flex-col flex-1 overflow-hidden relative">
                
                <!-- Header -->
                <livewire:layout.header />

                <!-- Page Content wrapper -->
                <div class="flex-1 overflow-y-auto overflow-x-hidden flex flex-col bg-gray-50 dark:bg-gray-900">
                    
                    <!-- Page Heading (Optional) -->
                    @if (isset($header))
                        <header class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
                            <div class="px-4 py-4 sm:px-6 lg:px-8">
                                {{ $header }}
                            </div>
                        </header>
                    @endif

                    <main class="flex-1 w-full">
                        {{ $slot }}
                    </main>

                    <!-- Footer -->
                    <x-footer />
                </div>
            </div>
        </div>

        <!-- Global Loader for Livewire Requests -->
        <x-global-loader />
    </body>
</html>
