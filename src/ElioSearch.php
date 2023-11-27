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

namespace Elio\ElioSearch;

use Elio\ElioSearch\Core\FilterRestrictions\Setup\FilterRestrictionsSetup;
use Elio\Foundation\Installer\CustomField\CustomFieldInstaller;
use Elio\Foundation\Installer\CustomField\Struct\CustomFieldSetStruct;
use Elio\Foundation\Installer\CustomField\Struct\CustomFieldStruct;
use Exception;
use Shopware\Core\Content\Category\CategoryDefinition;
use Shopware\Core\Content\LandingPage\LandingPageDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

/**
 * Class FactFinder
 *
 * @category  Bootstrap
 * @package   Shopware\Plugins\ElioSearch
 * @author    Raoul Yemetio <ry@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @author    Simon Greiner <sg@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (http://www.elio-systems.com)
 */
class ElioSearch extends Plugin
{
    public const CUSTOM_FIELD_CONTENT_EXPORT_TYPE = 'content_export_type';
    public const CUSTOM_FIELD_CONTENT_EXPORT_TYPE_INHERITED = 'content_export_type_inherited';
    public const CUSTOM_FIELD_CONTENT_EXPORT_EXCLUDE = 'content_export_exclude';
    public const CUSTOM_FIELD_CONTENT_EXPORT_EXCLUDE_INHERITED = 'content_export_exclude_inherited';
    public const CUSTOM_FIELD_CONTENT_EXPORT_EXCLUDE_PRODUCT_INFO_IN_KEYWORDS = 'content_export_exclude_product_info';
    public const CUSTOM_FIELD_CATEGORY_EXPORT_PRIORITY = 'category_export_priority';
    public const CUSTOM_FIELD_CATEGORY_CUSTOM_SEARCH_QUERY = 'category_custom_search_query';
    public const CUSTOM_FIELD_RANKING_PRODUCT_ORDER_COUNT = 'elio_search_ranking_product_order_count';
    public const CUSTOM_FIELD_RANKING_PRODUCT_ORDER_AMOUNT = 'elio_search_ranking_product_order_amount';
    public const CUSTOM_FIELD_DISPLAY_PRODUCT_BY_DEFAULT_IN_LISTING = 'elio_search_display_product_by_default_in_listing';
    public const CUSTOM_FIELD_DISPLAY_PRODUCT_BY_DEFAULT_IN_SEARCH = 'elio_search_display_product_by_default_in_search';

    public const DEFAULT_ELIO_SEARCH_FILTERS = ['CategoryPath', 'Manufacturer', 'Price', 'Stock'];

    /**
     * Adds the additional service definitions
     *
     * @param ContainerBuilder $container
     *
     * @throws Exception
     */
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/DependencyInjection/'));
        $loader->load('services.xml');
    }

    /**
     * @param ActivateContext $activateContext
     */
    public function activate(ActivateContext $activateContext): void
    {
        $filtersSetup = new FilterRestrictionsSetup($this->container);
        $filtersSetup->createFilters($activateContext->getContext(), self::DEFAULT_ELIO_SEARCH_FILTERS, true);

        $installer = new CustomFieldInstaller($this->container, $activateContext->getContext());
        $installer->install(...$this->getCustomFieldSets());
    }

    /**
     * @param UpdateContext $updateContext
     */
    public function postUpdate(UpdateContext $updateContext): void
    {
        if (!$this->isActive()) {
            return;
        }

        $filtersSetup = new FilterRestrictionsSetup($this->container);
        $filtersSetup->createFilters($updateContext->getContext(), self::DEFAULT_ELIO_SEARCH_FILTERS);

        $installer = new CustomFieldInstaller($this->container, $updateContext->getContext());
        $installer->install(...$this->getCustomFieldSets());
    }

    /**
     * @param UninstallContext $uninstallContext
     *
     * @throws Exception
     */
    public function uninstall(UninstallContext $uninstallContext): void
    {
        if ($uninstallContext->keepUserData()) {
            return;
        }

        (new FilterRestrictionsSetup($this->container))->removeTables();
        $installer = new CustomFieldInstaller($this->container, $uninstallContext->getContext());
        $installer->install(...$this->getCustomFieldSets());
    }

    private function getCustomFieldSets(): array
    {
        return [(new CustomFieldSetStruct(
            'ElioSearchContentExportCategory',
            [CategoryDefinition::ENTITY_NAME, LandingPageDefinition::ENTITY_NAME],
            [
                'en-GB' => 'ElioSearch content export',
                'de-DE' => 'ElioSearch Content Export'
            ]
        ))->addCustomFields(
            new CustomFieldStruct(
                self::CUSTOM_FIELD_CONTENT_EXPORT_TYPE,
                'text',
                [
                    'en-GB' => 'Type',
                    'de-DE' => 'Typ'
                ]
            ),
            new CustomFieldStruct(
                self::CUSTOM_FIELD_CONTENT_EXPORT_TYPE_INHERITED,
                'text',
                [
                    'en-GB' => 'Type for sub categories',
                    'de-DE' => 'Typ für Unterkategorien'
                ]
            ),
            new CustomFieldStruct(
                self::CUSTOM_FIELD_CONTENT_EXPORT_EXCLUDE,
                'bool',
                [
                    'en-GB' => 'Exclude in content export',
                    'de-DE' => 'Aus dem Content Export ausschließen'
                ]
            ),
            new CustomFieldStruct(
                self::CUSTOM_FIELD_CONTENT_EXPORT_EXCLUDE_INHERITED,
                'bool',
                [
                    'en-GB' => 'Exclude sub categories in content export',
                    'de-DE' => 'Unterkategorien vom Content Export ausschließen'
                ]
            ),
            new CustomFieldStruct(
                self::CUSTOM_FIELD_CONTENT_EXPORT_EXCLUDE_PRODUCT_INFO_IN_KEYWORDS,
                'bool',
                [
                    'en-GB' => 'Exclude product info in keywords',
                    'de-DE' => 'Produktinformationen in den Keywords ausschließen'
                ]
            ),
            new CustomFieldStruct(
                self::CUSTOM_FIELD_CATEGORY_EXPORT_PRIORITY,
                'text',
                [
                    'en-GB' => 'Priority',
                    'de-DE' => 'Priorität'
                ],
                null,
                [
                    'en-GB' => '50',
                    'de-DE' => '50'
                ]
            ),
            new CustomFieldStruct(
                self::CUSTOM_FIELD_CATEGORY_EXPORT_PRIORITY,
                'text',
                [
                    'en-GB' => 'Custom search query',
                    'de-DE' => 'Individuelle Suchanfrage'
                ],
                null,
                [
                    'en-GB' => 'brandline={category.name}&Manufacturer={parent.name}',
                    'de-DE' => 'brandline={category.name}&Manufacturer={parent.name}'
                ]
            ),
        ),
        (new CustomFieldSetStruct(
            'ElioSearchProduct',
            [ProductDefinition::ENTITY_NAME],
            [
                'en-GB' => 'ElioSearch product',
                'de-DE' => 'ElioSearch product'
            ]
        ))->addCustomFields(
            new CustomFieldStruct(
                self::CUSTOM_FIELD_RANKING_PRODUCT_ORDER_COUNT,
                'int',
                [
                    'en-GB' => 'Order count',
                    'de-DE' => 'Anzahl Bestellungen'
                ]
            ),
            new CustomFieldStruct(
                self::CUSTOM_FIELD_RANKING_PRODUCT_ORDER_AMOUNT,
                'float',
                [
                    'en-GB' => 'Order amount',
                    'de-DE' => 'Bestellwert'
                ]
            ),
            new CustomFieldStruct(
                self::CUSTOM_FIELD_DISPLAY_PRODUCT_BY_DEFAULT_IN_LISTING,
                'bool',
                [
                    'en-GB' => 'Displayed product/variant in listing',
                    'de-DE' => 'Produkt/Variante im Listing zeigen'
                ]
            ),
            new CustomFieldStruct(
                self::CUSTOM_FIELD_DISPLAY_PRODUCT_BY_DEFAULT_IN_SEARCH,
                'bool',
                [
                    'en-GB' => 'Displayed product/variant in search result',
                    'de-DE' => 'Produkt/Variante im Suchergebnis zeigen'
                ]
            )
        )];
    }
}
