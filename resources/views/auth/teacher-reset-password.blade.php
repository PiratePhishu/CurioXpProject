@extends('layouts.guest')

@section('content')
    <h2 class="text-xl font-bold text-curio-blue mb-1">Nieuw wachtwoord instellen</h2>
    <p class="text-sm text-gray-500 mb-5">Kies een nieuw wachtwoord voor je docentaccount.</p>

    @if ($errors->any())
        <div class="mb-4 rounded bg-red-50 text-red-700 px-4 py-3 text-sm">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('teacher.password.reset.update') }}" class="space-y-4">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <div>
            <label for="email" class="block text-xs font-bold text-curio-blue mb-1">E-mailadres</label>
            <input type="email" name="email" id="email" value="{{ old('email', $email) }}" required autofocus
                   class="w-full border border-curio-border rounded px-2 py-1.5 text-sm">
        </div>

        <div>
            <label for="password" class="block text-xs font-bold text-curio-blue mb-1">Nieuw wachtwoord</label>
            <input type="password" name="password" id="password" required minlength="8"
                   class="w-full border border-curio-border rounded px-2 py-1.5 text-sm">
        </div>

        <div>
            <label for="password_confirmation" class="block text-xs font-bold text-curio-blue mb-1">Bevestig wachtwoord</label>
            <input type="password" name="password_confirmation" id="password_confirmation" required minlength="8"
                   class="w-full border border-curio-border rounded px-2 py-1.5 text-sm">
        </div>

        <button type="submit" class="w-full bg-curio-blue text-white text-sm font-bold rounded px-4 py-2 hover:bg-curio-blue/90">
            Wachtwoord opslaan
        </button>
    </form>
@endsection
