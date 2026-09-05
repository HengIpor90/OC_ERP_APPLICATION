<?php

namespace App\Concerns;

use App\Models\Team;
use Illuminate\Support\Str;

trait GeneratesUniqueTeamSlugs
{
    /**
     * Generate a unique slug for the team.
     */
    protected static function generateUniqueTeamSlug(string $name, int|string|null $excludeId = null): string
    {
        $defaultSlug = Str::slug($name);

        $existingSlugs = static::withTrashed()
            ->get(['id', 'slug'])
            ->reject(fn (Team $team) => $excludeId !== null && (string) $team->id === (string) $excludeId)
            ->pluck('slug')
            ->filter(fn (string $slug) => $slug === $defaultSlug || Str::startsWith($slug, $defaultSlug.'-'));

        $maxSuffix = $existingSlugs
            ->map(function (string $slug) use ($defaultSlug): ?int {
                if ($slug === $defaultSlug) {
                    return 0;
                } elseif (preg_match('/^'.preg_quote($defaultSlug, '/').'-(\d+)$/', $slug, $matches)) {
                    return (int) $matches[1];
                }

                return null;
            })
            ->filter(fn (?int $suffix) => $suffix !== null)
            ->max() ?? 0;

        return $existingSlugs->isEmpty()
            ? $defaultSlug
            : $defaultSlug.'-'.($maxSuffix + 1);
    }
}
