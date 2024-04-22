<?php
/**
 * Copyright (c) 2024, elio GmbH.
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

namespace Elio\ElioDataDiscovery\Core\Sync\Api;

use Elio\ElioDataDiscovery\Core\Sync\ChangeSet\ChangeSetService;
use Elio\ElioDataDiscovery\Core\Sync\ChangeSet\Message\StartIndexMessage;
use Elio\ElioDataDiscovery\Core\Sync\SyncProfileCollection;
use Elio\ElioDataDiscovery\Core\Sync\SyncService;
use Exception;
use Shopware\Core\Defaults;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Shopware\Core\Framework\Context;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class IndexController
 *
 * @category Shopware
 * @author Andrei Baev <anb@elio-systems.com>
 * @author elio GmbH <support@elio-systems.com>
 * @copyright Copyright (c) 2024, elio GmbH (https://www.elio-systems.com)
 */
#[Route(defaults: ['_routeScope' => ['api']])]
class IndexController extends AbstractController
{
    public function __construct(
        private readonly SyncService         $syncService,
        private readonly ChangeSetService    $changeSetService,
        private readonly SystemConfigService $configService,
        private readonly MessageBusInterface $messageBus
    )
    {
    }

    /**
     * @throws Exception
     */
    #[Route(path:'/api/_action/elio-data-discovery/index-cleanup', name: 'api.custom.elio_data_discovery_index.index-cleanup', methods: ['GET'])]
    public function indexCleanup(): Response
    {
        $context = Context::createDefaultContext();
        /** @var SyncProfileCollection $syncProfiles */
        $syncProfiles = $this->syncService->getSyncProfileConfigurations($context)->getEntities();

        if (
            $syncProfiles->count() <= 0
            || $syncProfiles->hasNotExecutedSyncProfiles()
            || !($sortedProfile = $syncProfiles->getLeastRecentlyFinishedSyncProfile())
        ) {
            return new Response('<error>Cannot cleanup. A sync profile must exist and it must be executed before performing a cleanup.</error>', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $daysBeforeCleanup = (int)($this->configService->get('entityStatusMaxCleanupAgeInDays') ?? 14);
        $cleanupDate = (new DateTimeImmutable($sortedProfile->getLastGenerationFinishedAt()
            ->format(Defaults::STORAGE_DATE_TIME_FORMAT)))
            ->modify('-' . $daysBeforeCleanup . 'day');

        try {
            $this->changeSetService->cleanup($cleanupDate, $context);
        } catch (Exception $e) {
            return new Response('<error>' . $e->getMessage() . '</error>', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return new Response('', Response::HTTP_NO_CONTENT);
    }

    #[Route(path:'/api/_action/elio-data-discovery/index-update', name: 'api.custom.elio_data_discovery_index.index-update', methods: ['GET'])]
    public function indexUpdate(): Response
    {
        try {
            $this->messageBus->dispatch(new StartIndexMessage(Context::createDefaultContext()));
        } catch (Exception $e) {
            return new Response('<error>' . $e->getMessage() . '</error>', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return new JsonResponse(['mode' => 'async']);
    }
}