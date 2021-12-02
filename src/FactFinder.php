<?php declare(strict_types=1);
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

namespace Elio\FactFinder;

use Elio\FactFinder\Core\Export\Setup\ExportSetup;
use Elio\FactFinder\Setup\CustomFieldSetup;
use Exception;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

/**
 * Class FactFinder
 * @category  Bootstrap
 * @package   Shopware\Plugins\FactFinder
 * @author    Raoul Yemetio <ry@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @author    Simon Greiner <sg@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (http://www.elio-systems.com)
 */
class FactFinder extends Plugin
{
    public const CUSTOM_FIELD_CONTENT_EXPORT_TYPE = 'content_export_type';
    public const CUSTOM_FIELD_CONTENT_EXPORT_TYPE_INHERITED = 'content_export_type_inherited';
    public const CUSTOM_FIELD_CONTENT_EXPORT_EXCLUDE = 'content_export_exclude';
    public const CUSTOM_FIELD_CONTENT_EXPORT_EXCLUDE_INHERITED = 'content_export_exclude_inherited';
    public const CUSTOM_FIELD_CONTENT_EXPORT_EXCLUDE_PRODUCT_INFO_IN_KEYWORDS = 'content_export_exclude_product_info';
    public const CUSTOM_FIELD_CATEGORY_EXPORT_PRIORITY = 'category_export_priority';

    public const CUSTOM_FIELDS = [
        'FactFinderContentExportCategory' => [
            'label' => [
                'en-GB' => 'FactFinder content export',
                'de-DE' => 'FactFinder Content Export'
            ],
            'fields' => [
                self::CUSTOM_FIELD_CONTENT_EXPORT_TYPE => [
                    'type' => 'text',
                    'componentName' => 'sw-field',
                    'placeholder' => 'category',
                    'label' => [
                        'en-GB' => 'Type',
                        'de-DE' => 'Typ'
                    ]
                ],
                self::CUSTOM_FIELD_CONTENT_EXPORT_TYPE_INHERITED => [
                    'type' => 'text',
                    'componentName' => 'sw-field',
                    'placeholder' => 'category',
                    'label' => [
                        'en-GB' => 'Type for sub categories',
                        'de-DE' => 'Typ für Unterkategorien'
                    ]
                ],
                self::CUSTOM_FIELD_CONTENT_EXPORT_EXCLUDE => [
                    'type' => 'bool',
                    'componentName' => 'sw-field',
                    'label' => [
                        'en-GB' => 'Exclude in content export',
                        'de-DE' => 'Aus dem Content Export ausschließen'
                    ]
                ],
                self::CUSTOM_FIELD_CONTENT_EXPORT_EXCLUDE_INHERITED => [
                    'type' => 'bool',
                    'componentName' => 'sw-field',
                    'label' => [
                        'en-GB' => 'Exclude sub categories in content export',
                        'de-DE' => 'Unterkategorien vom Content Export ausschließen'
                    ]
                ],
                self::CUSTOM_FIELD_CONTENT_EXPORT_EXCLUDE_PRODUCT_INFO_IN_KEYWORDS => [
                    'type' => 'bool',
                    'componentName' => 'sw-field',
                    'label' => [
                        'en-GB' => 'Exclude product info in keywords',
                        'de-DE' => 'Produktinformationen in den Keywords ausschließen'
                    ]
                ],
                self::CUSTOM_FIELD_CATEGORY_EXPORT_PRIORITY => [
                    'type' => 'text',
                    'componentName' => 'sw-field',
                    'placeholder' => '50',
                    'label' => [
                        'en-GB' => 'Priority',
                        'de-DE' => 'Priorität'
                    ]
                ]
            ],
            'relations' => ['category', 'landing_page']
        ]
    ];

    public const EXPORT_CONFIG_EXPORT_PRODUCT_CATEGORIES = 'export_product_categories';
    public const EXPORT_CONFIG_EXPORT_STRUCTURE_CATEGORIES = 'export_structure_categories';
    public const EXPORT_CONFIG_EXPORT_LINK_CATEGORIES = 'export_link_categories';
    public const EXPORT_CONFIG_TRIGGER_IMPORT_SEARCH_DATA = 'trigger_import_search_data';
    public const EXPORT_CONFIG_TRIGGER_IMPORT_SUGGEST_DATA = 'trigger_import_suggest_data';

    /**
     * Adds the additional service definitions
     *
     * @param ContainerBuilder $container
     * @throws Exception
     */
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/DependencyInjection/'));
        $loader->load('services.xml');
    }

    /**
     * @param UpdateContext $updateContext
     */
    public function postUpdate(UpdateContext $updateContext): void
    {
        if (!$this->isActive()) {
            return;
        }

        $setup = new ExportSetup($this->container);
        $setup->createExports($updateContext->getContext());

        $customFieldSetup = new CustomFieldSetup($this->container);
        $customFieldSetup->install(self::CUSTOM_FIELDS);
    }

    /**
     * @param ActivateContext $activateContext
     */
    public function activate(ActivateContext $activateContext): void
    {
        $setup = new ExportSetup($this->container);
        $setup->createExports($activateContext->getContext());

        $customFieldSetup = new CustomFieldSetup($this->container);
        $customFieldSetup->install(self::CUSTOM_FIELDS);
    }
}
