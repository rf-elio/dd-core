<?php declare(strict_types=1);
/**
 * Copyright (c) 2023, elio GmbH.
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

namespace Elio\ElioSearch\Core\Sync\Administration\Controller;

use Elio\ElioSearch\Core\Sync\Export\ExportStorageService;
use Elio\ElioSearch\Core\Sync\Profile\SyncProfileInterface;
use Elio\ElioSearch\Core\Sync\SyncProfileEntity;
use Elio\ElioSearch\Core\Sync\SyncProfileMessage;
use Elio\ElioSearch\Core\Sync\SyncService;
use League\Flysystem\FilesystemException;
use Shopware\Core\Framework\Context;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SyncProfileController
 * @category Shopware
 * @author elio GmbH <support@elio-systems.com>
 * @author Danil Lukov <dl@elio-systems.com>
 * @copyright Copyright (c) 2023, elio GmbH (https://www.elio-systems.com)
 */
#[Route(defaults: ['_routeScope' => ['administration']])]
class SyncProfileController extends AbstractController
{
    public function __construct(
        private readonly iterable $profiles,
        private readonly ExportStorageService $exportStorageService,
        private readonly SyncService $syncService,
        private readonly MessageBusInterface $messageBus
    ){
    }

    /**
     * @Route("/api/_action/elio-search/sync-profile/profiles", name="api.action.elio-search.sync-profile.profiles", methods={"GET"})
     *
     * @return JsonResponse
     */
    public function profiles(): JsonResponse
    {
        $profiles = [];
        /** @var SyncProfileInterface $profile */
        foreach ($this->profiles as $profile) {
            $name = $profile->getName();
            if (array_key_exists($profile->getName(), $profiles)) {
                return new JsonResponse([
                    'error' => sprintf('Duplicated profile name %s', $name)
                ], 400);
            }

            $profiles[$name] = [
                'name' => $name,
                'type' => $profile->getType(),
                'outputs' => $profile->getOutputs(),
                'dataTypes' => $profile->getDataTypes(),
                'features' => $profile->getFeatures()
            ];
        }

        return new JsonResponse([
            'profiles' => $profiles,
        ]);
    }

    /**
     * @Route("/api/_action/elio-search/export/status/{id}", name="api.action.elio-search.export.status", methods={"GET"})
     * @throws FilesystemException
     */
    public function features(string $id, Context $context): JsonResponse
    {
        try {
            $syncProfile = $this->syncService->getSyncProfileConfiguration($id, $context);
            return new JsonResponse([
                'exists' => $this->exportStorageService->exists($syncProfile),
                'path' => $this->exportStorageService->createFileName($syncProfile)
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'exists' => false,
                'location' => ''
            ]);
        }
    }

    /**
     * Provides the generated file.
     *
     * @Route("/api/_action/elio-search/export/download/{id}/{humanReadableIdentifier}", name="api.action.elio-search.export.download", defaults={"auth_required"=false}, methods={"GET"})
     * @throws FileNotFoundException
     * @throws FilesystemException
     */
    public function download(Request $request, string $id, Context $context): Response
    {
        $syncProfile = $this->syncService->getSyncProfileConfiguration($id, $context);

        if($response = $this->authenticate($request, $syncProfile)) {
            return $response;
        }

        return $this->exportStorageService->createFileResponse($syncProfile);
    }

    /**
     * Validates the given auth set in the elio search export
     *
     * @param Request $request
     * @param SyncProfileEntity $syncProfile
     * @return Response|null
     */
    private function authenticate(Request $request, SyncProfileEntity $syncProfile): ?Response
    {
        $givenUsername = $request->server->get('PHP_AUTH_USER');
        $givenPassword = $request->server->get('PHP_AUTH_PW');
        $requiredUsername = $syncProfile->getDownloadUsername();
        $requiredPassword = $syncProfile->getDownloadPassword();

        if (empty($requiredUsername) || empty($requiredPassword)) {
            return null;
        }

        if ($givenUsername === $requiredUsername && $givenPassword === $requiredPassword) {
            return null;
        }

        $response = new Response();
        $response->setStatusCode(Response::HTTP_UNAUTHORIZED);
        $response->headers->set('WWW-Authenticate', 'Basic realm="Site Administration Area"');
        $response->headers->set('Status', '401 Unauthorized');
        $response->headers->set('HTTP-Status' , '401 Unauthorized');
        return $response;
    }

    /**
     * Generates the export in background
     *
     * @Route("/api/_action/elio-search/export/generate/{id}", name="api.action.elio-search.export.generate", methods={"GET"})
     */
    public function generate(string $id, Context $context): Response
    {
        $syncProfile = $this->syncService->getSyncProfileConfiguration($id, $context);

        $startedDate = new \DateTime();
        $this->messageBus->dispatch((new Envelope(new SyncProfileMessage($syncProfile, $context)))->with(new DelayStamp(1000)));
        return new JsonResponse(['id' => $id, 'status' => 'starting', 'started' => $startedDate]);
    }
}