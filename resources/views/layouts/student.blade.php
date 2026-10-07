<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F5F7FA] text-[#424242] font-sans">
    <header class="bg-curio-blue text-white flex items-center gap-4 px-6 py-4">
        <div class="text-2xl font-bold tracking-widest text-curio-orange">CURIO</div>
        <div class="flex-1">
            <div class="text-sm"><strong>XP-Tracker</strong> &nbsp;|&nbsp; ICT System Engineer Niveau 4</div>
            <div class="text-sm">{{ $student->name }} &nbsp;|&nbsp; Team {{ $student->team ?: '—' }}</div>
        </div>
        <form method="POST" action="{{ route('student.logout') }}">
            @csrf
            <button type="submit" class="text-sm text-white/80 hover:text-white underline">Uitloggen</button>
        </form>
    </header>

    <main class="max-w-3xl mx-auto px-6 py-8">
        @if (session('status'))
            <div class="mb-4 rounded bg-curio-green-light text-curio-green px-4 py-3 text-sm">
                {{ session('status') }}
            </div>
        @endif

        @yield('content')
    </main>

    <div class="text-center text-xs text-gray-400 pb-10">Curio XP-Tracker</div>
</body>
</html>
