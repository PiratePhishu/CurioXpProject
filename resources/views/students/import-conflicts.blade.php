@extends('layouts.app')

@section('content')
    <h2 class="text-xl font-bold text-curio-blue mb-3">Dubbele studentnummers in de import</h2>

    <div class="mb-5 rounded border-l-4 border-red-500 bg-red-50 px-4 py-3 text-sm text-red-700">
        <strong>{{ $conflicts->count() }} studentnummer(s) konden niet automatisch worden opgeslagen.</strong>
        Een studentnummer stond meerdere keren in het bestand met verschillende namen, of hoort al bij een student in
        een andere klas. Een student kan maar in &eacute;&eacute;n klas zitten (het studentnummer is ook de login), dus kies
        hieronder per student wat er moet gebeuren. De overige studenten uit het bestand zijn al opgeslagen.
    </div>

    @if ($errors->any())
        <div class="mb-4 rounded bg-red-50 text-red-700 px-4 py-3 text-sm">
            @foreach (array_unique($errors->all()) as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form id="conflicts-form" method="POST" action="{{ route('students.import.conflicts.resolve') }}">
        @csrf

        @if ($conflicts->contains(fn ($conflict) => $conflict->hasClassConflict()))
            <div class="mb-3">
                <button type="button" id="move-all" class="bg-curio-blue hover:bg-curio-blue/90 text-white text-sm font-bold rounded px-4 py-2">
                    Verplaats alle naar {{ $targetClass->name }}
                </button>
            </div>
        @endif

        <div class="space-y-3 mb-5">
            @foreach ($conflicts as $conflict)
                @php
                    $existing = $conflict->existingStudent;
                    $existingPoints = (int) ($existing?->total_points ?? 0);
                @endphp

                <div class="bg-white rounded shadow-sm p-4">
                    <div class="font-bold text-curio-blue mb-2">
                        Studentnummer {{ $conflict->student_number }}
                    </div>

                    @if ($conflict->hasNameConflict())
                        <fieldset class="mb-3">
                            <legend class="text-xs font-bold text-curio-blue mb-1">Dit nummer staat meerdere keren in het bestand. Welke naam klopt?</legend>
                            @foreach ($conflict->candidate_names as $candidateName)
                                <label class="flex items-center gap-2 text-sm">
                                    <input
                                        type="radio"
                                        name="resolutions[{{ $conflict->id }}][name]"
                                        value="{{ $candidateName }}"
                                        required
                                        @checked(old("resolutions.{$conflict->id}.name") === $candidateName)
                                    >
                                    {{ $candidateName }}
                                </label>
                            @endforeach
                        </fieldset>
                    @else
                        <div class="text-sm mb-3">Naam in het bestand: <strong>{{ $conflict->candidate_names[0] }}</strong></div>
                    @endif

                    @if ($conflict->hasClassConflict())
                        <fieldset>
                            <legend class="text-xs font-bold text-curio-blue mb-1">
                                Deze student staat al als <strong>{{ $existing->name }}</strong> in
                                <strong>{{ $existing->schoolClass->name }}</strong>
                                @if ($existing->schoolClass->schoolYear)
                                    ({{ $existing->schoolClass->schoolYear->name }})
                                @endif
                                @if ($existing->trashed())
                                    <span class="italic">(verwijderd)</span>
                                @endif
                                . In welke klas hoort de student?
                            </legend>
                            <label class="flex items-center gap-2 text-sm">
                                <input
                                    type="radio"
                                    name="resolutions[{{ $conflict->id }}][placement]"
                                    value="keep"
                                    required
                                    @checked(old("resolutions.{$conflict->id}.placement") === 'keep')
                                >
                                Laten staan in {{ $existing->schoolClass->name }}
                            </label>
                            <label class="flex items-center gap-2 text-sm">
                                <input
                                    type="radio"
                                    name="resolutions[{{ $conflict->id }}][placement]"
                                    value="move"
                                    required
                                    class="move-choice"
                                    data-points="{{ $existingPoints }}"
                                    @checked(old("resolutions.{$conflict->id}.placement") === 'move')
                                >
                                Verplaatsen naar {{ $targetClass->name }}
                                @if ($existingPoints > 0)
                                    <span class="text-red-600 font-semibold">
                                        &#9888;&#65039; {{ $existing->name }} heeft {{ $existingPoints }} XP in {{ $existing->schoolClass->name }}; dit wordt verwijderd.
                                    </span>
                                @endif
                            </label>
                        </fieldset>
                    @endif
                </div>
            @endforeach
        </div>

        <button type="submit" class="bg-curio-orange hover:bg-curio-orange-dark text-white text-sm font-bold rounded px-4 py-2">Keuzes opslaan</button>
        <a href="{{ route('students.index') }}" class="ml-2 text-sm text-curio-blue font-semibold hover:underline">Later oplossen</a>
    </form>

    <form id="discard-form" method="POST" action="{{ route('students.import.conflicts.destroy') }}" class="mt-6 border-t border-curio-border pt-4">
        @csrf
        @method('DELETE')
        <p class="text-sm text-gray-600 mb-2">
            Verkeerd bestand ge&iuml;mporteerd? Verwerp de open conflicten om opnieuw te kunnen importeren. Er wordt niets
            opgeslagen voor deze studentnummers; studenten die al zonder conflict zijn opgeslagen blijven staan.
        </p>
        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white text-sm font-bold rounded px-4 py-2">Conflicten verwerpen</button>
    </form>

    <script>
        document.getElementById('discard-form').addEventListener('submit', (event) => {
            if (! confirm('Alle open importconflicten van deze klas verwerpen? Deze keuzes gaan verloren.')) {
                event.preventDefault();
            }
        });

        document.getElementById('move-all')?.addEventListener('click', () => {
            document.querySelectorAll('.move-choice').forEach((radio) => {
                radio.checked = true;
            });
        });

        document.getElementById('conflicts-form').addEventListener('submit', (event) => {
            const checkedMoves = [...document.querySelectorAll('.move-choice:checked')];
            const pointsToDelete = checkedMoves.reduce((total, radio) => total + Number(radio.dataset.points), 0);
            const studentsLosingPoints = checkedMoves.filter((radio) => Number(radio.dataset.points) > 0).length;

            if (pointsToDelete > 0 && ! confirm(`Let op: bij het verplaatsen van ${studentsLosingPoints} student(en) wordt in totaal ${pointsToDelete} XP verwijderd. Doorgaan?`)) {
                event.preventDefault();
            }
        });
    </script>
@endsection
