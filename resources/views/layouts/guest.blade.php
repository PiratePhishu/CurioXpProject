<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F5F7FA] text-[#424242] font-sans min-h-screen flex flex-col">
    <header class="bg-curio-blue text-white flex items-center gap-4 px-6 py-4">
        <div class="text-2xl font-bold tracking-widest text-curio-orange">CURIO</div>
        <div class="text-sm"><strong>XP-Tracker</strong> &nbsp;|&nbsp; ICT System Engineer Niveau 4</div>
    </header>

    <main class="flex-1 flex items-center justify-center px-6 py-12">
        <div class="bg-white rounded shadow-sm w-full max-w-sm p-6">
            @yield('content')
        </div>
    </main>

    <div class="text-center text-xs text-gray-400 pb-10">Curio XP-Tracker</div>
</body>
</html>
