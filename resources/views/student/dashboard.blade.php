@extends('layouts.student')

@section('content')
    <h2 class="text-xl font-bold text-curio-blue mb-3">Mijn score</h2>

    <div class="bg-white rounded shadow-sm p-5 mb-6">
        <div class="flex items-center justify-between mb-2">
            <div>
                <div class="text-3xl font-bold text-curio-green">{{ $totalPoints }} XP</div>
                <div class="text-sm text-gray-500">Level: <span class="font-bold text-curio-blue">{{ $level['name'] }}</span></div>
            </div>
            @if ($student->team)
                <span class="inline-block rounded-full bg-[#E8F0F8] text-curio-blue px-3 py-1 text-xs font-bold">Team {{ $student->team }}</span>
            @endif
        </div>

        <div class="relative h-4 bg-curio-grey rounded-full overflow-hidden mt-3">
            <span class="absolute inset-y-0 left-0 rounded-full bg-gradient-to-r from-curio-orange to-curio-gold" style="width: {{ $progress }}%"></span>
        </div>

        <div class="mt-3 text-xs text-[#424242]">
            @foreach ($levels as $lvl)
                {{ $lvl['name'] }} ({{ $lvl['min'] }}{{ $lvl['span'] > 0 ? '–'.($lvl['min'] + $lvl['span'] - 1) : '+' }})
                @if (!$loop->last) &nbsp;|&nbsp; @endif
            @endforeach
        </div>
    </div>

    <h3 class="text-sm font-bold text-curio-blue mb-2">XP per les</h3>

    <div class="bg-white rounded shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-curio-blue text-white">
                <tr>
                    <th class="py-2 px-3 text-left">Les</th>
                    <th class="py-2 px-3 text-center w-24">Week</th>
                    <th class="py-2 px-3 text-center w-28">Punten</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($lessons as $lesson)
                    @php $points = $student->xpEntries->firstWhere('lesson_id', $lesson->id)?->points; @endphp
                    <tr class="border-b border-curio-border last:border-0">
                        <td class="py-2 px-3">{{ $lesson->name }}</td>
                        <td class="py-2 px-3 text-center">{{ $lesson->week }}</td>
                        <td class="py-2 px-3 text-center font-bold {{ $points !== null ? 'text-curio-green' : 'text-gray-300' }}">
                            {{ $points !== null ? $points : '—' }} / {{ $lesson->max_points }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="py-6 text-center text-gray-400 italic">Nog geen lessen beschikbaar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
