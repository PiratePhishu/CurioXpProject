@extends('layouts.guest')

@section('content')
    <h2 class="text-xl font-bold text-curio-blue mb-1">Wachtwoord vergeten</h2>
    <p class="text-sm text-gray-500 mb-5">
        Vul je e-mailadres in. Als dit bekend is, sturen we je een link om een nieuw wachtwoord in te stellen.
    </p>

    @if ($errors->any())
        <div class="mb-4 rounded bg-red-50 text-red-700 px-4 py-3 text-sm">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('teacher.password.email') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="block text-xs font-bold text-curio-blue mb-1">E-mailadres</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                   class="w-full border border-curio-border rounded px-2 py-1.5 text-sm">
        </div>

        <button type="submit" class="w-full bg-curio-blue text-white text-sm font-bold rounded px-4 py-2 hover:bg-curio-blue/90">
            Resetlink versturen
        </button>
    </form>

    <div class="mt-5 text-center text-xs text-gray-400">
        <a href="{{ route('teacher.login') }}" class="text-curio-blue font-semibold hover:underline">Terug naar inloggen</a>
    </div>
@endsection
