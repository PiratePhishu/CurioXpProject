@extends('layouts.guest')

@section('content')
    <h2 class="text-xl font-bold text-curio-blue mb-1">Inloggen als docent</h2>
    <p class="text-sm text-gray-500 mb-5">Beheer studenten, teams en XP.</p>

    @if ($errors->any())
        <div class="mb-4 rounded bg-red-50 text-red-700 px-4 py-3 text-sm">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('teacher.login.store') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="block text-xs font-bold text-curio-blue mb-1">E-mailadres</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                   class="w-full border border-curio-border rounded px-2 py-1.5 text-sm">
        </div>

        <div>
            <label for="password" class="block text-xs font-bold text-curio-blue mb-1">Wachtwoord</label>
            <input type="password" name="password" id="password" required
                   class="w-full border border-curio-border rounded px-2 py-1.5 text-sm">
        </div>

        <label class="flex items-center gap-2 text-xs text-gray-500">
            <input type="checkbox" name="remember">
            Onthoud mij
        </label>

        <button type="submit" class="w-full bg-curio-blue text-white text-sm font-bold rounded px-4 py-2 hover:bg-curio-blue/90">
            Inloggen
        </button>
    </form>

    <div class="mt-5 text-center text-xs text-gray-400">
        Ben je student? <a href="{{ route('student.login') }}" class="text-curio-blue font-semibold hover:underline">Log hier in</a>
    </div>
@endsection
