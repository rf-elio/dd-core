<?php declare(strict_types=1);

namespace Elio\ElioDataDiscovery\Core\Util;

class StripClassPathUtil
{
    public static function stripClassPath(string $classPath): string
    {
        return substr(strrchr($classPath, '\\'), 1);
    }
}