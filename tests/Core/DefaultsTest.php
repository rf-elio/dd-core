<?php


namespace Elio\ElioSearch\Tests\Core;


use Elio\ElioSearch\Core\Defaults;
use PHPUnit\Framework\TestCase;

/**
 * Class DefaultsTest
 *
 * @package Elio\ElioSearch\Tests\Core
 */
class DefaultsTest extends TestCase
{
    public function testValueSeparator(): void
    {
        self::assertSame('|', Defaults::VALUE_SEPARATOR);
    }
}
