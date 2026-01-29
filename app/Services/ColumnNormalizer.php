<?php

namespace App\Services;

use Illuminate\Support\Str;

class ColumnNormalizer
{
    public static function normalize(string $value): string
    {
        $value = Str::of($value)
            ->lower()
            ->ascii()
            ->replaceMatches('/[^a-z0-9]+/u', ' ')
            ->replaceMatches('/\s+/', ' ')
            ->trim()
            ->toString();

        return $value;
    }

    public static function findBySynonyms(array $normalizedHeaders, array $synonyms): ?string
    {
        foreach ($normalizedHeaders as $original => $normalized) {
            foreach ($synonyms as $synonym) {
                if (str_contains($normalized, $synonym)) {
                    return $original;
                }
            }
        }

        return null;
    }

    public static function findBestMatch(array $normalizedHeaders, array $keywords, int $minScore = 1): ?string
    {
        $best = null;
        $bestScore = 0;

        foreach ($normalizedHeaders as $original => $normalized) {
            $score = 0;
            foreach ($keywords as $keyword) {
                if ($keyword !== '' && str_contains($normalized, $keyword)) {
                    $score++;
                }
            }
            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $original;
            }
        }

        return $bestScore >= $minScore ? $best : null;
    }
}
