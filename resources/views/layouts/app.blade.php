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
    @php
        $schoolYears = \App\Models\SchoolYear::query()
            ->with(['classes' => fn ($query) => $query->orderBy('name')])
            ->orderBy('name')
            ->get();
        $currentSchoolClass = auth()->user()?->currentSchoolClass;
    @endphp

    <header class="bg-curio-blue text-white flex items-center gap-4 px-6 py-4">
        <div class="text-2xl font-bold tracking-widest text-curio-orange">CURIO</div>
        <form method="POST" action="{{ route('classes.activate') }}">
            @csrf
            <select
                name="school_class_id"
                onchange="this.form.submit()"
                class="bg-curio-blue border border-white/30 rounded text-xs text-white px-2 py-1.5"
                title="Klas wisselen"
            >
                @forelse ($schoolYears as $schoolYear)
                    <optgroup label="{{ $schoolYear->name }}">
                        @foreach ($schoolYear->classes as $class)
                            <option value="{{ $class->id }}" @selected($currentSchoolClass?->id === $class->id)>{{ $class->name }}</option>
                        @endforeach
                    </optgroup>
                @empty
                    <option>Geen klassen</option>
                @endforelse
            </select>
        </form>
        <div class="flex-1">
            <div class="text-sm"><strong>XP-Tracker</strong> &nbsp;|&nbsp; ICT System Engineer Niveau 4</div>
            <div class="text-sm">{{ $currentSchoolClass?->schoolYear?->name }} &nbsp;|&nbsp; {{ $currentSchoolClass?->name ?? 'Geen klas geselecteerd' }} &nbsp;|&nbsp; XP volgt de student</div>
        </div>
        <div class="text-sm flex items-center gap-1">
            <span id="saveDot" style="color:#2E7D32">&#9679;</span>
            <span id="saveText">Opgeslagen</span>
        </div>
        <form method="POST" action="{{ route('teacher.logout') }}">
            @csrf
            <button type="submit" class="text-sm text-white/80 hover:text-white underline">Uitloggen</button>
        </form>
    </header>

    <nav class="bg-white border-b border-curio-border flex gap-1 px-6">
        @php
            $tabs = [
                'scoreboard' => ['label' => 'Scorebord', 'route' => 'scoreboard'],
                'teams' => ['label' => 'Teamklassement', 'route' => 'teams'],
                'xp' => ['label' => 'XP invoeren', 'route' => 'xp.index'],
                'students' => ['label' => 'Studenten & teams', 'route' => 'students.index'],
                'classes' => ['label' => 'Klassen', 'route' => 'classes.index'],
            ];
        @endphp
        @foreach ($tabs as $key => $tab)
            @php $active = request()->routeIs($tab['route']) || ($key === 'xp' && request()->routeIs('xp.*')); @endphp
            <a href="{{ route($tab['route']) }}"
               class="px-4 py-3 text-sm font-semibold border-b-2 {{ $active ? 'text-curio-blue border-curio-orange' : 'text-gray-500 border-transparent hover:text-curio-blue' }}">
                {{ $tab['label'] }}
            </a>
        @endforeach
    </nav>

    <main class="max-w-6xl mx-auto px-6 py-8">
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
