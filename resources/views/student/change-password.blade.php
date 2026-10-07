@extends('layouts.student')

@section('content')
    <h2 class="text-xl font-bold text-curio-blue mb-1">Wachtwoord wijzigen</h2>
    <p class="text-sm text-gray-500 mb-5">
        @if ($student->must_change_password)
            Je logt voor het eerst in. Stel hieronder je eigen wachtwoord in voordat je verder gaat.
        @else
            Stel hieronder een nieuw wachtwoord in.
        @endif
    </p>

    @if ($errors->any())
        <div class="mb-4 rounded bg-red-50 text-red-700 px-4 py-3 text-sm">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('student.password.update') }}" class="space-y-4 max-w-sm">
        @csrf
        @method('PUT')

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

        <button type="submit" class="bg-curio-blue text-white text-sm font-bold rounded px-4 py-2 hover:bg-curio-blue/90">
            Wachtwoord opslaan
        </button>
    </form>
@endsection
