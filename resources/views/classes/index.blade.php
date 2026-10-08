@extends('layouts.app')

@section('content')
    <h2 class="text-xl font-bold text-curio-blue mb-3">Klassen</h2>

    <div class="bg-[#FFF3E0] border-l-4 border-curio-orange rounded-r px-4 py-3 text-sm italic text-[#5A4A00] mb-5">
        Elke klas heeft zijn eigen studenten, teams, lessen en XP, en hoort bij een schooljaar. Wissel van klas via het
        keuzemenu linksboven &mdash; daar staan de klassen gegroepeerd per schooljaar. Een nieuwe klas start zonder
        studenten, maar neemt de lesnamen over van de klas die je nu bekijkt.
    </div>

    <form method="POST" action="{{ route('classes.store') }}" class="flex flex-wrap items-end gap-2 mb-5">
        @csrf
        <div>
            <label class="block text-xs font-bold text-curio-blue mb-1">Naam nieuwe klas</label>
            <input type="text" name="name" required class="border border-curio-border rounded px-2 py-1.5 text-sm" placeholder="Bijv. ICT SE N4 - groep 2">
        </div>
        <div>
            <label class="block text-xs font-bold text-curio-blue mb-1">Schooljaar</label>
            <input type="text" name="year" required value="{{ old('year', $defaultYear) }}" class="border border-curio-border rounded px-2 py-1.5 text-sm w-28">
        </div>
        <button type="submit" class="bg-curio-orange hover:bg-curio-orange-dark text-white text-sm font-bold rounded px-4 py-2">Klas toevoegen</button>
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
                    <th class="py-2 px-3 text-left">Naam</th>
                    <th class="py-2 px-3 text-center w-32">Schooljaar</th>
                    <th class="py-2 px-3 text-center w-32">Studenten</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($classes as $class)
                    <tr class="border-b border-curio-border last:border-0">
                        <td class="py-2 px-3">
                            <input
                                type="text"
                                class="field-input border border-transparent hover:border-curio-border focus:border-curio-orange rounded px-2 py-1 w-full"
                                data-class-id="{{ $class->id }}"
                                value="{{ $class->name }}"
                            >
                        </td>
                        <td class="py-2 px-3 text-center">{{ $class->schoolYear->name }}</td>
                        <td class="py-2 px-3 text-center">{{ $class->students_count }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="py-6 text-center text-gray-400 italic">Nog geen klassen aangemaakt.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <script>
        document.querySelectorAll('.field-input').forEach((input) => {
            let originalValue = input.value;

            input.addEventListener('change', async () => {
                const url = `/klassen/${input.dataset.classId}`;

                try {
                    await window.curioXp.sendJSON(url, 'PATCH', { name: input.value });
                    originalValue = input.value;
                } catch (error) {
                    input.value = originalValue;
                }
            });
        });
    </script>
@endsection
