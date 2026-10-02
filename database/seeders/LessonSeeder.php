<?php

namespace Database\Seeders;

use App\Models\Lesson;
use Illuminate\Database\Seeder;

class LessonSeeder extends Seeder
{
    /**
     * SoHo-netwerk "Kees de Bij" curriculum, week 1-16.
     *
     * @var array<int, array{code: string, week: string, max: int, name: string}>
     */
    private const LESSONS = [
        ['code' => 'L2', 'week' => '1', 'max' => 200, 'name' => 'Kickoff: klantwensen & netwerkschets'],
        ['code' => 'L4', 'week' => '2', 'max' => 250, 'name' => 'IP-planning en segmentatie'],
        ['code' => 'L6', 'week' => '3', 'max' => 300, 'name' => 'UTP-kabels knijpen'],
        ['code' => 'L8', 'week' => '4', 'max' => 250, 'name' => 'Fysieke montage'],
        ['code' => 'L10', 'week' => '5', 'max' => 300, 'name' => 'Router & guest network'],
        ['code' => 'L12', 'week' => '6', 'max' => 275, 'name' => 'NAS & camera'],
        ['code' => 'L14', 'week' => '7', 'max' => 300, 'name' => 'NAS extra functie'],
        ['code' => 'L16', 'week' => '8', 'max' => 250, 'name' => 'Performance optimalisatie'],
        ['code' => 'L18', 'week' => '9', 'max' => 275, 'name' => 'Netwerkprinter & documentatie'],
        ['code' => 'L20', 'week' => '10', 'max' => 225, 'name' => 'QuinLED smart-verlichting'],
        ['code' => 'L22', 'week' => '11', 'max' => 325, 'name' => 'VPN & remote access'],
        ['code' => 'L24', 'week' => '12', 'max' => 275, 'name' => 'Integratietest & security'],
        ['code' => 'L26', 'week' => '13', 'max' => 250, 'name' => 'Documentatie'],
        ['code' => 'L28', 'week' => '14', 'max' => 200, 'name' => 'Presentatie voorbereiden'],
        ['code' => 'L30', 'week' => '15-16', 'max' => 500, 'name' => 'FINALE: eindpresentatie aan Kees'],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::LESSONS as $position => $lesson) {
            Lesson::query()->updateOrCreate(
                ['code' => $lesson['code']],
                [
                    'week' => $lesson['week'],
                    'name' => $lesson['name'],
                    'max_points' => $lesson['max'],
                    'position' => $position,
                ]
            );
        }
    }
}
