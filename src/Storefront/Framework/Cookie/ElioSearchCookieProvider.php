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

namespace Elio\ElioSearch\Storefront\Framework\Cookie;

use Elio\ElioSearch\Configuration\ElioSearchConfigServiceInterface;
use Shopware\Core\PlatformRequest;
use Shopware\Storefront\Framework\Cookie\CookieProviderInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class ElioSearchCookieProvider
 * @package Elio\ElioSearch\Storefront\Framework\Cookie
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class ElioSearchCookieProvider implements CookieProviderInterface
{
    private const TRACKING_COOKIE = [
        'snippet_name' => 'elioSearch.cookies.tracking.name',
        'snippet_description' => 'elioSearch.cookies.tracking.description',
        'cookie' => 'elio_search_tracking',
        'value'=> '1',
        'expiration' => '30'
    ];

    /**
     * ElioSearchCookieProvider constructor.
     * @param CookieProviderInterface $cookieProvider
     * @param ElioSearchConfigServiceInterface $configService
     * @param RequestStack $requestStack
     */
    public function __construct(
        private readonly CookieProviderInterface $cookieProvider,
        private readonly ElioSearchConfigServiceInterface $configService,
        private readonly RequestStack $requestStack
    ) {}

    /**
     * @return array<array>
     */
    public function getCookieGroups(): array
    {
        $cookieGroups = $this->cookieProvider->getCookieGroups();
        $masterRequest = $this->requestStack->getMainRequest();

        if($masterRequest === null) {
            return $cookieGroups;
        }

        $salesChannelContext = $masterRequest->attributes->get(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_CONTEXT_OBJECT);
        $config = $this->configService->getByContext($salesChannelContext);
        if($config->isTrackRequireConsent()) {
            foreach ($cookieGroups as &$cookieGroup) {
                if($cookieGroup['snippet_name'] !== 'cookie.groupRequired') {
                    continue;
                }

                $cookieGroup['entries'] = array_merge(
                    $cookieGroup['entries'],
                    [self::TRACKING_COOKIE]
                );

                break;
            }
        }

        return $cookieGroups;
    }
}
