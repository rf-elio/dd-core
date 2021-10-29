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

namespace Elio\FactFinder\Core\Export\Generator\Content;

use Elio\FactFinder\Core\Export\ExportEntity;
use Elio\FactFinder\Core\Export\ExportItem;
use Elio\FactFinder\Core\Export\Generator\ExportGeneratorInterface;
use Elio\FactFinder\Core\Export\Generator\Util\ValueUtil;
use Elio\FactFinder\Core\Export\OutputStream;
use Shopware\Core\Content\LandingPage\LandingPageEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Framework\Seo\SeoUrlRoute\LandingPageSeoUrlRoute;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Elio\FactFinder\Core\Export\Generator\Content\ContentExportDefaults as Defaults;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class LandingPagePartialGenerator
 * @package Elio\FactFinder\Core\Export\Generator\Content
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class LandingPagePartialGenerator implements ExportGeneratorInterface
{
    public const TYPE = 'content';
    protected const EXPORT_TYPE = 'landingpage';
    private EntityRepositoryInterface $landingPageRepository;
    private RouterInterface $router;

    /**
     * LandingPageGenerator constructor.
     * @param EntityRepositoryInterface $landingPageRepository
     * @param RouterInterface $router
     */
    public function __construct(
        EntityRepositoryInterface $landingPageRepository,
        RouterInterface $router
    )
    {
        $this->landingPageRepository = $landingPageRepository;
        $this->router = $router;
    }

    public function supports(ExportEntity $export): bool
    {
        return $export->getType() === static::TYPE;
    }

    /**
     * Exports all landing pages assigned to this sales channel
     *
     * @param ExportEntity $export
     * @param OutputStream $output
     * @param SalesChannelContext $context
     */
    public function generate(ExportEntity $export, OutputStream $output, SalesChannelContext $context): void
    {
        $criteria = new Criteria();
        $criteria->addAssociation('salesChannels');
        $criteria->addFilter(new EqualsFilter('salesChannels.id', $context->getSalesChannelId()));
        $landingPages = $this->landingPageRepository->search($criteria, $context->getContext());

        /** @var LandingPageEntity $landingPage */
        foreach ($landingPages as $landingPage) {
            $item = new ExportItem();
            $item->set(Defaults::FIELD_ID, $landingPage->getId());
            $item->set(Defaults::FIELD_TYPE, self::EXPORT_TYPE);
            $item->set(Defaults::FIELD_TITLE, ValueUtil::cleanValue($landingPage->getName()));
            $item->set(Defaults::FIELD_SEO_TEXT, ValueUtil::cleanValue($landingPage->getMetaDescription()));
            $item->set(
                Defaults::FIELD_URL,
                $this->router->generate(
                    LandingPageSeoUrlRoute::ROUTE_NAME,
                    ['landingPageId' => $landingPage->getId()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                )
            );
            $item->set(Defaults::FIELD_KEYWORDS, ValueUtil::cleanValue($landingPage->getKeywords()));
            $item->set(Defaults::FIELD_DESCRIPTION, ValueUtil::cleanValue($landingPage->getMetaDescription()));
            $item->set(Defaults::FIELD_IMAGE_URL, '');
            $item->set(Defaults::FIELD_PUBLICATION_DATE, ValueUtil::cleanValue($landingPage->getCreatedAt()->format('Y-m-d H:i:s')));
            $item->set(Defaults::FIELD_PRIORITY, Defaults::DEFAULT_PRIORITY);
            $item->set(Defaults::FIELD_CONTENT_STRUCTURE, '');
            $output->write($item);
        }
    }
}