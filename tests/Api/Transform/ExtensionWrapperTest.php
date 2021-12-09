<?php
/**
 * Copyright (c) 2021, elio GmbH.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice,
 * this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 * this list of conditions and the following disclaimer in the documentation
 * and/or other materials provided with the distribution.
 *
 * 3. Neither the name of the copyright holder nor the names of its contributors
 * may be used to endorse or promote products derived from this software without
 * specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

namespace Elio\FactFinder\Tests\Api\Transform;

use Elio\FactFinder\Api\Transform\ExtensionWrapper;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Struct\Struct;
use Swagger\Client\Model\ModelInterface;


/**
 * Class ExtensionWrapperTest
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class ExtensionWrapperTest extends TestCase
{
    /**
     * Tests if the key is still the same
     */
    public function testKey(): void
    {
        $this->assertEquals(ExtensionWrapper::KEY, 'ff-api');
    }

    /**
     * Tests if the input model is the same as the model that is provided in the get model method
     */
    public function testGetModel(): void
    {
        $wrapper = new ExtensionWrapper($this->getTestModel());
        $this->assertSame($wrapper->getModel()->getModelName(), 'test');
    }

    /**
     * Tests if the wrapper is instance of struct
     */
    public function testExtensionWrapperType(): void
    {
        $wrapper = new ExtensionWrapper($this->getTestModel());
        $this->assertInstanceOf(Struct::class, $wrapper);
    }

    /**
     * @return ModelInterface
     */
    private function getTestModel() : ModelInterface
    {
        return new class () implements ModelInterface
        {
            public function getModelName()
            {
                return 'test';
            }

            public static function swaggerTypes() { return []; }
            public static function swaggerFormats() { return []; }
            public static function attributeMap() { return []; }
            public static function setters() { return []; }
            public static function getters() { return []; }
            public function listInvalidProperties() { return []; }
            public function valid() { return true; }
        };
    }
}
