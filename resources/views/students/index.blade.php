@extends('layouts.app')

@section('content')
    <h2 class="text-xl font-bold text-curio-blue mb-3">Studenten &amp; teams</h2>

    <div class="bg-[#FFF3E0] border-l-4 border-curio-orange rounded-r px-4 py-3 text-sm italic text-[#5A4A00] mb-5">
        Beheer hier de namen en het huidige teamlabel per student. Klik op een kolomkop (Naam, Team of Totaal XP) om te
        sorteren. Het teamlabel (bijv. A, B, C&hellip;) bepaalt alleen de groepering op het Teamklassement &mdash; het
        heeft geen invloed op de individuele XP. Pas labels gerust elke week aan bij een wissel.
    </div>

    <form method="POST" action="{{ route('students.store') }}" class="flex flex-wrap items-end gap-2 mb-3">
        @csrf
        <div>
            <label class="block text-xs font-bold text-curio-blue mb-1">Naam</label>
            <input type="text" name="name" required class="border border-curio-border rounded px-2 py-1.5 text-sm" placeholder="Nieuwe student">
        </div>
        <div>
            <label class="block text-xs font-bold text-curio-blue mb-1">Team</label>
            <input type="text" name="team" maxlength="10" class="border border-curio-border rounded px-2 py-1.5 text-sm w-20" placeholder="A">
        </div>
        <button type="submit" class="bg-curio-orange hover:bg-curio-orange-dark text-white text-sm font-bold rounded px-4 py-2">Student toevoegen</button>
    </form>

    <form method="POST" action="{{ route('students.import') }}" enctype="multipart/form-data" class="flex flex-wrap items-end gap-2 mb-5">
        @csrf
        <div>
            <label class="block text-xs font-bold text-curio-blue mb-1">Studenten importeren (Excel/CSV, één naam per rij)</label>
            <input type="file" name="file" required accept=".xlsx,.xls,.csv" class="border border-curio-border rounded px-2 py-1.5 text-sm bg-white">
        </div>
        <button type="submit" class="bg-curio-blue hover:bg-curio-blue/90 text-white text-sm font-bold rounded px-4 py-2">Importeren</button>
    </form>

    @if ($errors->any())
        <div class="mb-4 rounded bg-red-50 text-red-700 px-4 py-3 text-sm">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="bg-white rounded shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-curio-blue text-white">
                <tr>
                    <th class="py-2 px-3 text-center w-14">Nr</th>
                    @foreach (['naam' => 'Naam', 'team' => 'Team', 'tot' => 'Totaal XP'] as $field => $label)
                        @php $nextDir = $sort === $field && $direction === 'asc' ? 'desc' : 'asc'; @endphp
                        <th class="py-2 px-3 {{ $field === 'naam' ? 'text-left' : 'text-center w-32' }}">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => $field, 'dir' => $nextDir]) }}" class="hover:underline">
                                {{ $label }}
                                <span class="text-[9px] opacity-70">{{ $sort === $field ? ($direction === 'asc' ? '▲' : '▼') : '' }}</span>
                            </a>
                        </th>
                    @endforeach
                    <th class="py-2 px-3 text-center w-20">Acties</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($students as $index => $student)
                    <tr class="border-b border-curio-border last:border-0">
                        <td class="py-2 px-3 text-center">{{ $index + 1 }}</td>
                        <td class="py-2 px-3">
                            <input
                                type="text"
                                class="field-input border border-transparent hover:border-curio-border focus:border-curio-orange rounded px-2 py-1 w-full"
                                data-student-id="{{ $student->id }}"
                                data-field="name"
                                value="{{ $student->name }}"
                            >
                        </td>
                        <td class="py-2 px-3 text-center">
                            <input
                                type="text"
                                maxlength="10"
                                class="field-input border border-transparent hover:border-curio-border focus:border-curio-orange rounded px-2 py-1 w-16 text-center"
                                data-student-id="{{ $student->id }}"
                                data-field="team"
                                value="{{ $student->team }}"
                            >
                        </td>
                        <td class="py-2 px-3 text-center font-bold text-curio-green">{{ $student->total_points }}</td>
                        <td class="py-2 px-3 text-center">
                            <form method="POST" action="{{ route('students.destroy', $student) }}" onsubmit="return confirm('Student {{ addslashes($student->name) }} verwijderen?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700 text-xs font-bold">Verwijder</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-6 text-center text-gray-400 italic">Nog geen studenten toegevoegd.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <script>
        document.querySelectorAll('.field-input').forEach((input) => {
            let originalValue = input.value;

            input.addEventListener('change', async () => {
                const url = `/studenten/${input.dataset.studentId}`;

                try {
                    await window.curioXp.sendJSON(url, 'PATCH', {
                        [input.dataset.field]: input.value,
                    });
                    originalValue = input.value;
                } catch (error) {
                    input.value = originalValue;
                }
            });
        });
    </script>
@endsection
