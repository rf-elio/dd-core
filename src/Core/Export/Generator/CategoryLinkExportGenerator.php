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

use Elio\FactFinder\Core\Export\ExportItem;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class CategoryExportGenerator
 * @package Elio\FactFinder\Core\Export\Generator
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Andrey Baev <anb@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class CategoryLinkExportGenerator extends CategoryExportGenerator implements ExportGeneratorInterface
{
    public const CATEGORY_TYPE = 'link';
    public const TYPE = 'category_link';

    /**
     * CategoryLinkExportGenerator constructor.
     * @param EntityRepositoryInterface $categoryRepository
     * @param RouterInterface $router
     * @param SystemConfigService $systemConfigService
     */
    public function __construct(EntityRepositoryInterface $categoryRepository, RouterInterface $router, SystemConfigService $systemConfigService)
    {
        parent::__construct($categoryRepository, $router, $systemConfigService);
    }

    /**
     * @param ExportItem $item
     * @param CategoryEntity $category
     * @param SalesChannelContext $context
     * @return ExportItem
     */
    public function setExportItem(ExportItem $item, CategoryEntity $category, SalesChannelContext $context): ExportItem
    {
        $item->set('CategoryID', $category->getId());
        $item->set('Name', $this->cleanValue($category->getTranslation('name')));
        $item->set('Description', $this->cleanValue($category->getTranslation('description')));
        $item->set('Path', $this->cleanValue($category->getPath()));
        $item->set('Keywords', $this->cleanValue($category->getKeywords()));
        $item->set('LanguageId', $context->getLanguageIdChain()[0]);
        $item->set('LinkType', $this->cleanValue($category->getLinkType()));
        // $item->set('Link', $this->cleanValue($category->getInternalLink() ?? $category->getExternalLink() ));

        return $item;
    }
}