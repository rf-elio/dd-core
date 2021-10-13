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
use Shopware\Core\Content\Cms\CmsPageEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

/**
 * Class CMSExportGenerator
 * @package Elio\FactFinder\Core\Export\Generator
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Andrey Baev <anb@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class CMSExportGenerator implements ExportGeneratorInterface
{
    public const TYPE = 'cms';
    private EntityRepositoryInterface $cmsRepository;

    /**
     * Checks if the generator can be used for the given export
     * @param ExportEntity $export
     * @return bool
     */
    public function supports(ExportEntity $export): bool
    {
        return $export->getType() === self::TYPE;
    }

    /**
     * CMSExportGenerator constructor.
     * @param EntityRepositoryInterface $cmsRepository
     */
    public function __construct(EntityRepositoryInterface $cmsRepository)
    {
        $this->cmsRepository = $cmsRepository;
    }

    /**
     * Generates the cms export
     *
     * @param ExportEntity $export
     * @param OutputStream $output
     * @param SalesChannelContext $context
     */
    public function generate(ExportEntity $export, OutputStream $output, SalesChannelContext $context): void
    {
        $criteria = new Criteria();
        $criteria->addAssociation('sections');
        $criteria->addAssociation('translations');
        //$criteria->addAssociation('categories');

        // @todo: don't fetch all cms_pages (memory) use some pagination stuff
        $cmsPages = $this->cmsRepository->search($criteria, $context->getContext());

        /** @var CmsPageEntity $page */
        foreach ($cmsPages as $page) {
            $item = new ExportItem();
            $item = $this->setExportItem($item, $page, $context);

            //$output->write($item);
        }
    }

    /**
     * @param ExportItem $item
     * @param CmsPageEntity $page
     * @param SalesChannelContext $context
     * @return ExportItem
     */
    public function setExportItem(ExportItem $item, CmsPageEntity $page, SalesChannelContext $context): ExportItem
    {
        $item->set('PageID', $page->getId());
        $item->set('Name', $this->cleanValue($page->getTranslation('name')));

        return $item;
    }

    /**
     * @param string|null $value
     * @return string
     */
    private function cleanValue(?string $value): string
    {
        $value = empty($value) ? "" : $value;
        return trim(strip_tags($value));
    }
}