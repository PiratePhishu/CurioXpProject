@extends('layouts.app')

@section('content')
    <h2 class="text-xl font-bold text-curio-blue mb-3">Teamklassement &mdash; huidige teams</h2>

    <div class="bg-[#FFF3E0] border-l-4 border-curio-orange rounded-r px-4 py-3 text-sm italic text-[#5A4A00] mb-5">
        Telt automatisch de XP op van de studenten die nu hetzelfde teamlabel hebben (in te stellen op 'Studenten &amp; teams').
        Wisselt een team van samenstelling, dan beweegt dit klassement mee &mdash; de individuele XP blijft intact.
        Rangschikt op totaal; 'Gem./lid' maakt teams van ongelijke grootte eerlijk vergelijkbaar.
    </div>

    <div class="flex items-center gap-2 mb-4">
        <label class="font-bold text-curio-blue text-sm">Zoek student:</label>
        <input type="text" id="studentSearch" placeholder="Naam…" class="border border-curio-border rounded px-2 py-1.5 text-sm w-64">
    </div>

    <div class="bg-white rounded shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-curio-blue text-white">
                <tr>
                    <th class="py-2 px-3 text-center w-16">Rang</th>
                    <th class="py-2 px-3 text-center w-24">Team</th>
                    <th class="py-2 px-3 text-left">Leden</th>
                    <th class="py-2 px-3 text-center w-24">Aantal</th>
                    <th class="py-2 px-3 text-center w-28">Totaal XP</th>
                    <th class="py-2 px-3 text-center w-28">Gem./lid</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($teams as $index => $team)
                    <tr
                        class="border-b border-curio-border last:border-0 cursor-pointer hover:bg-[#F5F7FA]"
                        data-search="{{ strtolower($team['members']->pluck('name')->join(', ')) }}"
                        onclick="window.location = '{{ route('teams.show', $team['team']) }}';"
                    >
                        <td class="py-2 px-3 text-center">{{ $index + 1 }}</td>
                        <td class="py-2 px-3 text-center">
                            <span class="inline-block rounded-full bg-[#E8F0F8] text-curio-blue px-2 py-0.5 text-xs font-bold">{{ $team['team'] }}</span>
                        </td>
                        <td class="py-2 px-3">
                            <a href="{{ route('teams.show', $team['team']) }}" class="hover:underline hover:text-curio-blue">
                                {{ $team['members']->pluck('name')->join(', ') }}
                            </a>
                        </td>
                        <td class="py-2 px-3 text-center">{{ $team['count'] }}</td>
                        <td class="py-2 px-3 text-center font-bold text-curio-green">{{ $team['total_points'] }}</td>
                        <td class="py-2 px-3 text-center">{{ $team['average'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-6 text-center text-gray-400 italic">Nog geen teams ingesteld.</td>
                    </tr>
                @endforelse
                <tr id="noMatchesRow" class="hidden">
                    <td colspan="6" class="py-6 text-center text-gray-400 italic">Geen studenten gevonden.</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="mt-3 text-xs text-gray-400">Teams zonder leden verschijnen niet in het klassement.</div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            window.curioXp.filterRows(document.getElementById('studentSearch'), '[data-search]', '#noMatchesRow');
        });
    </script>
@endsection
