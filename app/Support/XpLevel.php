<?php

namespace App\Support;

/**
 * Mirrors the level thresholds from the original Curio XP-Tracker desktop app.
 */
class XpLevel
{
    /**
     * @var array<int, array{name: string, min: int, span: int}>
     */
    private const LEVELS = [
        ['name' => 'Network Legend', 'min' => 3600, 'span' => 0],
        ['name' => 'Network Master', 'min' => 2600, 'span' => 1000],
        ['name' => 'Network Architect', 'min' => 1600, 'span' => 1000],
        ['name' => 'Apprentice', 'min' => 600, 'span' => 1000],
        ['name' => 'Novice', 'min' => 0, 'span' => 600],
    ];

    /**
     * @return array{name: string, min: int, span: int}
     */
    public static function for(int $totalPoints): array
    {
        foreach (self::LEVELS as $level) {
            if ($totalPoints >= $level['min']) {
                return $level;
            }
        }

        return self::LEVELS[array_key_last(self::LEVELS)];
    }

    public static function name(int $totalPoints): string
    {
        return self::for($totalPoints)['name'];
    }

    public static function progress(int $totalPoints): float
    {
        $level = self::for($totalPoints);

        if ($level['span'] === 0) {
            return 1.0;
        }

        return min(1.0, ($totalPoints - $level['min']) / $level['span']);
    }

    /**
     * @return array<int, array{name: string, min: int, span: int}>
     */
    public static function all(): array
    {
        return array_reverse(self::LEVELS);
    }
}
