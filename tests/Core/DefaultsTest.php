<?php


namespace Elio\FactFinder\Tests\Core;


use Elio\FactFinder\Core\Defaults;
use PHPUnit\Framework\TestCase;

/**
 * Class DefaultsTest
 *
 * @package Elio\FactFinder\Tests\Core
 */
class DefaultsTest extends TestCase
{
    public function testValueSeparator(): void
    {
        self::assertSame('|', Defaults::VALUE_SEPARATOR);
    }
}
