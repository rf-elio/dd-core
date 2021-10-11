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

namespace Elio\FactFinder\Storefront\TwigExtension;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\Language\LanguageEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Shopware\Storefront\Framework\Twig\TemplateConfigAccessor;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class ConfigLanguageBased
 * @package Elio\FactFinder\Storefront\TwigExtension
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Andrey Baev <anb@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class ConfigLanguageBased extends AbstractExtension
{

    private TemplateConfigAccessor $config;
    private EntityRepositoryInterface $languageRepository;

    public function __construct(
        TemplateConfigAccessor $config,
        EntityRepositoryInterface $languageRepository
    ) {
        $this->config = $config;
        $this->languageRepository = $languageRepository;
    }

    public function getName(): string
    {
        return 'twig.config_by_language';
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('config_by_lang', [$this, 'configByLanguage'], ['needs_context' => true]),
        ];
    }

    /**
     * Returns plugin configuration based on language and salesChannelId
     *
     * @param array $context
     * @param string $key
     * @param string $languageId
     * @return array|bool|float|int|string|null
     */
    public function configByLanguage(array $context, string $key, string $languageId)
    {
        $languagePrefix = $this->getLanguagePrefix($languageId);
        $salesChannelId = $this->getSalesChannelId($context);

        $parts = explode('.', $key);
        if (count($parts) === 0) {
            return null;
        }
        $parts[count($parts) - 1] = $languagePrefix . $parts[count($parts) - 1];

        $config = $this->config->config(implode('.', $parts), $salesChannelId);
        if ($config === null) {
            $config = $this->config->config($key, $salesChannelId);
        }

        return $config;
    }

    /**
     * @param array $context
     * @return string|null
     */
    private function getSalesChannelId(array $context): ?string
    {
        if (isset($context['context'])) {
            $salesChannelContext = $context['context'];
            if ($salesChannelContext instanceof SalesChannelContext) {
                return $salesChannelContext->getSalesChannelId();
            }
        }
        if (isset($context['salesChannel'])) {
            $salesChannel = $context['salesChannel'];
            if ($salesChannel instanceof SalesChannelEntity) {
                return $salesChannel->getId();
            }
        }

        return null;
    }

    /**
     * get LanguagePrefix by LanguageId
     * @param string $languageId
     * @return string
     */
    public function getLanguagePrefix(string $languageId): string
    {
        $criteria = new Criteria([$languageId]);
        $criteria->addAssociation('locale');
        $language = $this->languageRepository->search($criteria, Context::createDefaultContext())->first();

        /** @var LanguageEntity $language */
        if ($language && $language->getLocale()) {
            return str_replace('-', '_', $language->getLocale()->getCode()) . '_';
        } else {
            return '';
        }
    }
}