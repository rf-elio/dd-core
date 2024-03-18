<?php


namespace Elio\ElioDataDiscovery\Tests\Core;


use Elio\ElioDataDiscovery\Core\Defaults;
use PHPUnit\Framework\TestCase;

/**
 * Class DefaultsTest
 *
 * @package Elio\ElioDataDiscovery\Tests\Core
 */
class DefaultsTest extends TestCase
{
    public function testValueSeparator(): void
    {
        self::assertSame('|', Defaults::VALUE_SEPARATOR);
    }
}
