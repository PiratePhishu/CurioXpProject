@extends('layouts.app')

@section('content')
    <a href="{{ route('teams') }}" class="text-sm text-curio-blue hover:underline">&larr; Terug naar Teamklassement</a>

    <h2 class="text-xl font-bold text-curio-blue mt-2 mb-3">
        Team {{ $team }}
        <span class="text-sm font-normal text-gray-500">&mdash; {{ $students->count() }} student(en)</span>
    </h2>

    <div class="bg-[#FFF3E0] border-l-4 border-curio-orange rounded-r px-4 py-3 text-sm italic text-[#5A4A00] mb-5">
        Vul per student de behaalde XP voor de gekozen les in &mdash; het totaal wordt automatisch berekend.
        Wijzigingen worden direct opgeslagen.
    </div>

    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <div class="flex gap-4 text-sm">
            <div><span class="font-bold text-curio-blue">Totaal XP:</span> <span class="font-bold text-curio-green">{{ $totalPoints }}</span></div>
            <div><span class="font-bold text-curio-blue">Gem./lid:</span> {{ $average }}</div>
        </div>

        <form method="GET" action="{{ route('teams.show', $team) }}" class="flex items-center gap-2">
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
    </div>

    <div class="bg-white rounded shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-curio-blue text-white">
                <tr>
                    <th class="py-2 px-3 text-center w-14">Nr</th>
                    <th class="py-2 px-3 text-left">Student</th>
                    <th class="py-2 px-3 text-center w-40">XP deze les</th>
                    <th class="py-2 px-3 text-center w-32">Totaal (alle lessen)</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($students as $index => $student)
                    <tr class="border-b border-curio-border last:border-0" data-student-row="{{ $student->id }}">
                        <td class="py-2 px-3 text-center">{{ $index + 1 }}</td>
                        <td class="py-2 px-3 font-semibold">{{ $student->name }}</td>
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
                        <td colspan="4" class="py-6 text-center text-gray-400 italic">Geen studenten in dit team.</td>
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
        </script>
    @endif
@endsection
