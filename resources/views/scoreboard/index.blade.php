@extends('layouts.app')

@section('content')
    <h2 class="text-xl font-bold text-curio-blue mb-3">Scorebord &mdash; individueel</h2>

    <div class="bg-[#FFF3E0] border-l-4 border-curio-orange rounded-r px-4 py-3 text-sm italic text-[#5A4A00] mb-5">
        De XP staat op naam van de student en gaat met de student mee, ook bij een wissel van partner. Dit klassement
        werkt automatisch bij.
    </div>

    <div class="bg-white rounded shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-curio-blue text-white">
                <tr>
                    <th class="py-2 px-3 text-center w-16">Rang</th>
                    @foreach (['naam' => 'Student', 'team' => 'Team nu', 'tot' => 'Totaal XP'] as $field => $label)
                        @php
                            $nextDir = $sort === $field && $direction === 'asc' ? 'desc' : 'asc';
                        @endphp
                        <th class="py-2 px-3 {{ $field === 'naam' ? 'text-left' : 'text-center w-28' }}">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => $field, 'dir' => $nextDir]) }}"
                               class="hover:underline">
                                {{ $label }}
                                <span class="text-[9px] opacity-70">{{ $sort === $field ? ($direction === 'asc' ? '▲' : '▼') : '' }}</span>
                            </a>
                        </th>
                    @endforeach
                    <th class="py-2 px-3 text-center w-32">Level</th>
                    <th class="py-2 px-3 w-2/5">Voortgang naar volgend level</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($students as $index => $student)
                    @php
                        $level = \App\Support\XpLevel::for($student->total_points);
                        $progress = \App\Support\XpLevel::progress($student->total_points) * 100;
                    @endphp
                    <tr class="border-b border-curio-border last:border-0">
                        <td class="py-2 px-3 text-center">{{ $index + 1 }}</td>
                        <td class="py-2 px-3 font-semibold">{{ $student->name }}</td>
                        <td class="py-2 px-3 text-center">
                            @if ($student->team)
                                <span class="inline-block rounded-full bg-[#E8F0F8] text-curio-blue px-2 py-0.5 text-xs font-bold">{{ $student->team }}</span>
                            @else
                                <span class="text-gray-300 italic">&mdash;</span>
                            @endif
                        </td>
                        <td class="py-2 px-3 text-center font-bold text-curio-green">{{ $student->total_points }}</td>
                        <td class="py-2 px-3 text-center">{{ $level['name'] }}</td>
                        <td class="py-2 px-3">
                            <div class="relative h-4 bg-curio-grey rounded-full overflow-hidden">
                                <span class="absolute inset-y-0 left-0 rounded-full bg-gradient-to-r from-curio-orange to-curio-gold" style="width: {{ $progress }}%"></span>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-6 text-center text-gray-400 italic">Nog geen studenten toegevoegd.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4 bg-curio-grey rounded px-4 py-3 text-xs text-[#424242]">
        <strong>Levels:</strong>
        @foreach ($levels as $i => $level)
            {{ $level['name'] }} ({{ $level['min'] }}{{ $level['span'] > 0 ? '–'.($level['min'] + $level['span'] - 1) : '+' }})
            @if (!$loop->last) &nbsp;|&nbsp; @endif
        @endforeach
    </div>
@endsection
