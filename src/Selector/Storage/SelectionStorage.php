<?php

namespace PhpAT\Selector\Storage;

use PhpAT\Parser\Ast\ClassLike;

class SelectionStorage
{
    /** @var array<string, array<string, array<ClassLike>>>|null */
    private static array $origins = [];

    /**
     * @param array<ClassLike> $found
     */
    public static function registerOrigin(string $selectorClass, string $regexKey, array $found): void
    {
        self::$origins[$selectorClass][$regexKey] = $found;
    }

    /**
     * @return array<ClassLike>|null
     */
    public static function getKnown(string $selectorClass, string $regexKey): ?array
    {
        return self::$origins[$selectorClass][$regexKey] ?? null;
    }
}
