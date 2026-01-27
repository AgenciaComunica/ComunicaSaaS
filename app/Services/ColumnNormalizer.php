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
}
