<?php


namespace Elio\FactFinder\Tests\Core\Util;


use Elio\FactFinder\Core\Util\ArrayUtil;
use PHPUnit\Framework\TestCase;
use TypeError;

/**
 * Class ArrayUtilTest
 *
 * @package Elio\FactFinder\Tests\Core\Util
 */
class ArrayUtilTest extends TestCase
{

    /**
     * @dataProvider arrayKeyPushDataProvider
     *
     * @param array $array
     * @param mixed $data
     * @param array $keys
     * @param array $expected
     */
    public function testArrayKeyPush(array $array, $data, array $keys, array $expected): void
    {
        ArrayUtil::arrayKeyPush($array, $data, ...$keys);
        self::assertSame($expected, $array);
    }

    /**
     * @dataProvider arrayKeyAddDataProvider
     *
     * @param array $array
     * @param mixed $data
     * @param array $keys
     * @param array $expected
     */
    public function testArrayKeyAdd(array $array, $data, array $keys, array $expected): void
    {
        ArrayUtil::arrayKeyAdd($array, $data, ...$keys);
        self::assertSame($expected, $array);
    }

    public function testArrayKeyPushWithInvalidArray(): void
    {
        $this->expectException(TypeError::class);
        $array = ['children' => ''];
        ArrayUtil::arrayKeyPush($array, 'property', 'children', 'child');
    }

    public function testArrayKeyAddWithInvalidArray(): void
    {
        $this->expectException(TypeError::class);
        $array = ['children' => ''];
        ArrayUtil::arrayKeyAdd($array, 'property', 'children', 'child');
    }

    public function testArrayGroup(): void
    {
        $rows = $this->getTestProductsData();
        $result = ArrayUtil::arrayGroup($rows, 'category');
        self::assertSame([
            'phones' => [
                ['id' => 1, 'name' => 'iPhone', 'category' => 'phones'],
                ['id' => 2, 'name' => 'Samsung', 'category' => 'phones'],
                ['id' => 3, 'name' => 'Xiaomi', 'category' => 'phones'],
            ],
            'cars' => [
                ['id' => 4, 'name' => 'Audi', 'category' => 'cars'],
                ['id' => 5, 'name' => 'Opel', 'category' => 'cars'],
            ],
        ], $result);

        $result = ArrayUtil::arrayGroup($rows, 'name');
        self::assertSame([
            'iPhone' => [
                ['id' => 1, 'name' => 'iPhone', 'category' => 'phones'],
            ],
            'Samsung' => [
                ['id' => 2, 'name' => 'Samsung', 'category' => 'phones'],
            ],
            'Xiaomi' => [
                ['id' => 3, 'name' => 'Xiaomi', 'category' => 'phones'],
            ],
            'Audi' => [
                ['id' => 4, 'name' => 'Audi', 'category' => 'cars'],
            ],
            'Opel' => [
                ['id' => 5, 'name' => 'Opel', 'category' => 'cars'],
            ],
        ], $result);

        $this->expectWarning();
        ArrayUtil::arrayGroup($rows, 'test');
    }

    /**
     * @dataProvider convertToStringDataProvider
     *
     * @param mixed $data
     * @param mixed $expected
     */
    public function testConvertToString($data, $expected): void
    {
        $result = ArrayUtil::convertToString($data);
        self::assertSame($expected, $result);
    }

    public function testConvertToStringError(): void
    {
        $this->expectError();
        ArrayUtil::convertToString(['first', 'second', ['third']]);
    }


    /**
     * @dataProvider convertStringToArrayDataProvider
     *
     * @param mixed $data
     * @param mixed $expected
     */
    public function testConvertStringToArray($data, $expected): void
    {
        $result = ArrayUtil::convertStringToArray($data);
        self::assertSame($expected, $result);
    }

    /**
     * @dataProvider getArrayKeysAsStringDataProvider
     *
     * @param mixed $data
     * @param mixed $expected
     */
    public function testGetArrayKeysAsString($data, $expected): void
    {
        $result = ArrayUtil::getArrayKeysAsString($data);
        self::assertSame($expected, $result);
    }

    public function testSwap(): void
    {
        $values = $this->getTestProductsData();

        $result = ArrayUtil::swap($values, 'name');
        self::assertSame([
            'iPhone' => ['id' => 1, 'name' => 'iPhone', 'category' => 'phones'],
            'Samsung' => ['id' => 2, 'name' => 'Samsung', 'category' => 'phones'],
            'Xiaomi' => ['id' => 3, 'name' => 'Xiaomi', 'category' => 'phones'],
            'Audi' => ['id' => 4, 'name' => 'Audi', 'category' => 'cars'],
            'Opel' => ['id' => 5, 'name' => 'Opel', 'category' => 'cars'],
        ], $result);

        $result = ArrayUtil::swap($values, 'name', 'category');
        self::assertSame([
            'iPhone' => 'phones',
            'Samsung' => 'phones',
            'Xiaomi' => 'phones',
            'Audi' => 'cars',
            'Opel' => 'cars',
        ], $result);
    }


    public function arrayKeyPushDataProvider(): array
    {
        return [
            [
                [],
                ['childName' => 'name', 'childCategory' => 'category'],
                ['children', 'child'],
                [
                    'children' => [
                        'child' => [
                            ['childName' => 'name', 'childCategory' => 'category']
                        ]
                    ]
                ],
            ],
            [
                [],
                'property',
                ['children', 'child'],
                [
                    'children' => [
                        'child' => [
                            'property'
                        ]
                    ]
                ],
            ],
            [
                ['children' => []],
                'property',
                ['children', 'child'],
                [
                    'children' => [
                        'child' => [
                            'property'
                        ]
                    ]
                ],
            ]
        ];
    }

    public function arrayKeyAddDataProvider(): array
    {
        return [
            [
                [],
                ['childName' => 'name', 'childCategory' => 'category'],
                ['children', 'child'],
                [
                    'children' => [
                        'child' => [
                            'childName' => 'name',
                            'childCategory' => 'category'
                        ]
                    ]
                ],
            ],
            [
                [],
                'property',
                ['children', 'child'],
                [
                    'children' => [
                        'child' => 'property'
                    ]
                ],
            ],
            [
                ['children' => []],
                'property',
                ['children', 'child'],
                [
                    'children' => [
                        'child' => 'property'
                    ]
                ],
            ]
        ];
    }


    public function convertToStringDataProvider(): array
    {
        return [
            [
                ['first', 'second', 'third'],
                '0:first;1:second;2:third'
            ],
            [
                ['first' => 'value', 'second' => 'value', 'third' => 'value'],
                'first:value;second:value;third:value'
            ],
            [
                [2 => 'value', 3 => 'value', 4 => 'value'],
                '2:value;3:value;4:value'
            ],
            [
                'first',
                ''
            ]
        ];
    }

    public function convertStringToArrayDataProvider(): array
    {
        return [
            [
                '0:first;1:second;2:third',
                ['first', 'second', 'third']
            ],
            [
                'first:value;second:value;third:value',
                ['first' => 'value', 'second' => 'value', 'third' => 'value']
            ],
            [
                '2:value;3:value;4:value',
                [2 => 'value', 3 => 'value', 4 => 'value']
            ],
            [
                '',
                ['']
            ]
        ];
    }

    public function getArrayKeysAsStringDataProvider(): array
    {
        return [
            [
                ['first', 'second', 'third'],
                ['0', '1', '2']
            ],
            [
                [1.1 => 'first', 2.2 => 'second', 3.3 => 'third'],
                ['1', '2', '3']
            ],
            [
                [100 => 'first', 200 => 'second', 300 => 'third'],
                ['100', '200', '300']
            ],
            [
                ['first' => 'first', 'second' => 'second', 'third' => 'third'],
                ['first', 'second', 'third']
            ]
        ];
    }

    private function getTestProductsData(): array
    {
        return [
            ['id' => 1, 'name' => 'iPhone', 'category' => 'phones'],
            ['id' => 2, 'name' => 'Samsung', 'category' => 'phones'],
            ['id' => 3, 'name' => 'Xiaomi', 'category' => 'phones'],
            ['id' => 4, 'name' => 'Audi', 'category' => 'cars'],
            ['id' => 5, 'name' => 'Opel', 'category' => 'cars'],
        ];
    }
}
