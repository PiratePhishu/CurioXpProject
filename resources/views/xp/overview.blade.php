@extends('layouts.app')

@section('content')
    <h2 class="text-xl font-bold text-curio-blue mb-3">XP invoeren</h2>

    <div class="bg-[#FFF3E0] border-l-4 border-curio-orange rounded-r px-4 py-3 text-sm italic text-[#5A4A00] mb-5">
        Vul per student de behaalde XP per les in &mdash; het totaal wordt automatisch berekend. Wijzigingen worden
        direct opgeslagen.
    </div>

    <div class="flex items-center justify-between gap-3 mb-4">
        <div class="inline-flex rounded-md border border-curio-border overflow-hidden">
            <a href="{{ route('xp.index') }}" class="bg-white text-gray-500 text-sm font-bold px-4 py-2 hover:text-curio-blue">Per les</a>
            <span class="bg-curio-blue text-white text-sm font-bold px-4 py-2">Overzicht (alle lessen)</span>
        </div>
    </div>

    <div class="bg-white rounded shadow-sm overflow-x-auto">
        <table class="text-sm border-collapse min-w-full">
            <thead>
                <tr>
                    <th class="sticky left-0 bg-curio-blue text-white text-left py-2 px-3 min-w-[160px] z-10">Student</th>
                    @foreach ($lessons as $lesson)
                        <th class="bg-curio-blue text-white py-2 px-1 text-center text-[11px] whitespace-nowrap" title="{{ $lesson->name }} (max {{ $lesson->max_points }})">
                            W{{ $lesson->week }}
                        </th>
                    @endforeach
                    <th class="sticky right-0 bg-curio-blue text-white py-2 px-3 text-center min-w-[96px] z-10">Totaal</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($students as $student)
                    <tr class="border-b border-[#EDEFF2]" data-student-row="{{ $student->id }}">
                        <td class="sticky left-0 bg-white font-semibold py-1 px-3 whitespace-nowrap">{{ $student->name }}</td>
                        @foreach ($lessons as $lesson)
                            @php $points = $student->xpEntries->firstWhere('lesson_id', $lesson->id)?->points; @endphp
                            <td class="text-center p-1">
                                <input
                                    type="number"
                                    min="0"
                                    max="{{ $lesson->max_points }}"
                                    class="xp-input w-14 border border-curio-border rounded px-1 py-1 text-center text-xs"
                                    data-student-id="{{ $student->id }}"
                                    data-lesson-id="{{ $lesson->id }}"
                                    value="{{ $points }}"
                                >
                            </td>
                        @endforeach
                        <td class="sticky right-0 bg-curio-green-light text-center font-bold text-curio-green total-cell">{{ $student->total_points }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $lessons->count() + 2 }}" class="py-6 text-center text-gray-400 italic">Nog geen studenten toegevoegd.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

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
@endsection
