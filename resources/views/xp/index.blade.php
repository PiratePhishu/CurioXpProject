@extends('layouts.app')

@section('content')
    <h2 class="text-xl font-bold text-curio-blue mb-3">XP invoeren</h2>

    <div class="bg-[#FFF3E0] border-l-4 border-curio-orange rounded-r px-4 py-3 text-sm italic text-[#5A4A00] mb-5">
        Vul per student de behaalde XP per les in &mdash; het totaal wordt automatisch berekend. Kies 'Per les' voor
        &eacute;&eacute;n les tegelijk, of 'Overzicht' om alle lessen naast elkaar te zien. Werken twee studenten
        samen? Dan krijgen ze allebei hetzelfde getal. Wijzigingen worden direct opgeslagen.
    </div>

    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <div class="inline-flex rounded-md border border-curio-border overflow-hidden">
            <span class="bg-curio-blue text-white text-sm font-bold px-4 py-2">Per les</span>
            <a href="{{ route('xp.overview') }}" class="bg-white text-gray-500 text-sm font-bold px-4 py-2 hover:text-curio-blue">Overzicht (alle lessen)</a>
        </div>

        <form method="GET" action="{{ route('xp.index') }}" class="flex items-center gap-2">
            <label class="font-bold text-curio-blue text-sm">Les:</label>
            <select name="lesson" onchange="this.form.submit()" class="border border-curio-border rounded px-2 py-1.5 text-sm">
                @foreach ($lessons as $lesson)
                    <option value="{{ $lesson->code }}" @selected($currentLesson?->id === $lesson->id)>
                        Week {{ $lesson->week }} &mdash; {{ $lesson->name }}
                    </option>
                @endforeach
            </select>
            @if ($currentLesson)
                <span class="text-xs text-gray-400">max {{ $currentLesson->max_points }}</span>
            @endif
        </form>

        @if ($currentLesson)
            <div class="relative">
                <button type="button" id="fillAllBtn" class="bg-curio-orange hover:bg-curio-orange-dark text-white text-sm font-bold rounded px-4 py-2">
                    Zelfde XP voor iedereen&hellip;
                </button>
                <div id="fillAllPanel" class="hidden absolute right-0 mt-2 bg-white border border-curio-border rounded shadow-lg p-3 flex items-center gap-2 z-10">
                    <input type="number" id="fillAllPoints" min="0" max="{{ $currentLesson->max_points }}" value="{{ $currentLesson->max_points }}" class="w-20 border border-curio-border rounded px-2 py-1 text-center text-sm">
                    <button type="button" id="fillAllApply" class="bg-curio-blue text-white text-sm font-bold rounded px-3 py-1">Toepassen</button>
                </div>
            </div>
        @endif
    </div>

    <div class="bg-white rounded shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-curio-blue text-white">
                <tr>
                    <th class="py-2 px-3 text-center w-14">Nr</th>
                    <th class="py-2 px-3 text-left">Student</th>
                    <th class="py-2 px-3 text-center w-24">Team nu</th>
                    <th class="py-2 px-3 text-center w-40">XP deze les</th>
                    <th class="py-2 px-3 text-center w-32">Totaal (alle lessen)</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($students as $index => $student)
                    <tr class="border-b border-curio-border last:border-0" data-student-row="{{ $student->id }}">
                        <td class="py-2 px-3 text-center">{{ $index + 1 }}</td>
                        <td class="py-2 px-3 font-semibold">{{ $student->name }}</td>
                        <td class="py-2 px-3 text-center">{{ $student->team ?: '—' }}</td>
                        <td class="py-2 px-3 text-center">
                            @if ($currentLesson)
                                <input
                                    type="number"
                                    min="0"
                                    max="{{ $currentLesson->max_points }}"
                                    class="xp-input w-24 border border-curio-border rounded px-2 py-1 text-center"
                                    data-student-id="{{ $student->id }}"
                                    data-lesson-id="{{ $currentLesson->id }}"
                                    value="{{ $student->xpEntries->first()?->points }}"
                                >
                            @endif
                        </td>
                        <td class="py-2 px-3 text-center font-bold text-curio-green bg-curio-green-light total-cell">{{ $student->total_points }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-6 text-center text-gray-400 italic">Nog geen studenten toegevoegd.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($currentLesson)
        <script>
            document.querySelectorAll('.xp-input').forEach((input) => {
                input.addEventListener('change', async () => {
                    const row = input.closest('[data-student-row]');
                    const value = input.value === '' ? null : Number(input.value);

                    try {
                        const data = await window.curioXp.sendJSON('{{ route('xp.update') }}', 'PUT', {
                            student_id: Number(input.dataset.studentId),
                            lesson_id: Number(input.dataset.lessonId),
                            points: value,
                        });

                        row.querySelector('.total-cell').textContent = data.total_points;
                    } catch (error) {
                        input.value = '';
                    }
                });
            });

            const fillAllBtn = document.getElementById('fillAllBtn');
            const fillAllPanel = document.getElementById('fillAllPanel');

            fillAllBtn?.addEventListener('click', () => {
                fillAllPanel.classList.toggle('hidden');
            });

            document.getElementById('fillAllApply')?.addEventListener('click', async () => {
                const points = document.getElementById('fillAllPoints').value;

                if (points === '') {
                    return;
                }

                await window.curioXp.sendJSON('{{ route('xp.fill-all') }}', 'PUT', {
                    lesson_id: {{ $currentLesson->id }},
                    points: Number(points),
                });

                window.location.reload();
            });
        </script>
    @endif
@endsection
