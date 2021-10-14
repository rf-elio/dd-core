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

namespace Elio\FactFinder\Core\Export\Generator;

use Elio\FactFinder\Core\Export\ExportEntity;
use Elio\FactFinder\Core\Export\ExportItem;
use Elio\FactFinder\Core\Export\OutputStream;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class CategoryExportGenerator
 * @package Elio\FactFinder\Core\Export\Generator
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Andrey Baev <anb@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class CategoryExportGenerator implements ExportGeneratorInterface
{
    public const CATEGORY_TYPE = 'page';
    public const TYPE = 'category';
    protected const SLOT_CONFIG_MAX_LENGTH = 255;
    public const PLUGIN_CONFIG_PREFIX = 'FactFinder.config.';
    private EntityRepositoryInterface $categoryRepository;
    private RouterInterface $router;
    private SystemConfigService $systemConfigService;

    /**
     * Checks if the generator can be used for the given export
     * @param ExportEntity $export
     * @return bool
     */
    public function supports(ExportEntity $export): bool
    {
        return $export->getType() === static::TYPE;
    }

    /**
     * CategoryExportGenerator constructor.
     * @param EntityRepositoryInterface $categoryRepository
     * @param RouterInterface $router
     * @param SystemConfigService $systemConfigService
     */
    public function __construct(EntityRepositoryInterface $categoryRepository, RouterInterface $router, SystemConfigService $systemConfigService)
    {
        $this->categoryRepository = $categoryRepository;
        $this->router = $router;
        $this->systemConfigService = $systemConfigService;
    }

    /**
     * Generates the category export
     *
     * @param ExportEntity $export
     * @param OutputStream $output
     * @param SalesChannelContext $context
     */
    public function generate(ExportEntity $export, OutputStream $output, SalesChannelContext $context): void
    {
        // @todo: don't fetch all categories (memory) use some pagination stuff
        $categories = $this->categoryRepository->search($this->getCriteria($context), $context->getContext());

        /** @var CategoryEntity $category */
        foreach ($categories as $category) {
            $item = new ExportItem();
            $item = $this->setExportItem($item, $category, $context);

            // @todo: rewrite url
            $item->set(
                'LinkURL',
                $this->router->generate(
                    'frontend.navigation.page',
                    ['navigationId' => $category->getId()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                )
            );

            $output->write($item);
        }
    }

    /**
     * @param SalesChannelContext $context
     * @return Criteria
     */
    public function getCriteria(SalesChannelContext $context): Criteria
    {
        $ids = $this->systemConfigService->get(self::PLUGIN_CONFIG_PREFIX.'categoriesToExport', $context->getSalesChannelId());
        $criteria = new Criteria($ids);
        $criteria->addAssociation('cmsPage');
        $criteria->addAssociation('seoUrls');
        $criteria->addAssociation('translations');
        //$criteria->addAssociation('cmsPage.sections');
        //$criteria->addAssociation('cmsPage.sections.blocks');
        //$criteria->addAssociation('cmsPage.sections.blocks.slots');
        $criteria->addAssociation('cmsPage.sections.blocks.slots.translations');

        $criteria->addFilter(new EqualsFilter('category.visible', true));
        $criteria->addFilter(new EqualsFilter('category.active', true));
        $criteria->addFilter(new EqualsFilter('category.type', static::CATEGORY_TYPE));
        return $criteria;
    }

    /**
     * @param ExportItem $item
     * @param CategoryEntity $category
     * @param SalesChannelContext $context
     * @return ExportItem
     */
    public function setExportItem(ExportItem $item, CategoryEntity $category, SalesChannelContext $context): ExportItem
    {
        $slotConfig = '';

        if ($category->getTranslated()['slotConfig']) {
            if (gettype($category->getTranslated()['slotConfig']) == 'array') {
                foreach ($category->getTranslated()['slotConfig'] as $slotConfigBlock) {
                    if (key_exists('content', $slotConfigBlock)) {
                        if (key_exists('value', $slotConfigBlock['content'])) {
                            $slotConfig = $slotConfigBlock['content']['value'] ?? '';
                            continue;
                        }
                    }
                }
            } else {
                if (gettype($category->getTranslated()['slotConfig']) == 'string') {
                    $slotConfig = $category->getTranslated()['slotConfig'] ?? '';
                }
            }
        } else {
            foreach ($category->getCmsPage()->getSections()->getBlocks()->getSlots() as $slot) {
                if ($slot->getType() === 'text') {
                    if (key_exists('config', $slot->getTranslated())) {
                        if (key_exists('content', $slot->getTranslated()['config'])) {
                            if (key_exists('value', $slot->getTranslated()['config']['content'])) {
                                $slotConfig .= $slot->getTranslated()['config']['content']['value'];
                            }
                        }
                    }
                }
            }
        }

        $slotConfig = (strlen($slotConfig) > static::SLOT_CONFIG_MAX_LENGTH) ? substr(
                $slotConfig,
                0,
                static::SLOT_CONFIG_MAX_LENGTH - 3
            ) . '...' : $slotConfig;

        $item->set('CategoryID', $category->getId());
        $item->set('Name', $this->cleanValue($category->getName()));
        $item->set('Description', $this->cleanValue($category->getDescription()));
        $item->set('Path', $this->cleanValue($category->getPath()));
        $item->set('Keywords', $this->cleanValue($category->getKeywords()));
        $item->set('PageContent', $this->cleanValue($slotConfig));
        return $item;
    }

    /**
     * @param string|null $value
     * @return string
     */
    protected function cleanValue(?string $value): string
    {
        $value = empty($value) ? "" : $value;
        return trim(strip_tags($value));
    }
}