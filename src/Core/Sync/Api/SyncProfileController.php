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

namespace Elio\ElioDataDiscovery\Core\Sync\Api;

use Elio\ElioDataDiscovery\Core\Sync\Output\CSVOutput;
use Elio\ElioDataDiscovery\Core\Sync\ProfileInterface;
use Elio\ElioDataDiscovery\Core\Sync\SyncProfileEntity;
use Elio\ElioDataDiscovery\Core\Sync\Message\SyncProfileMessage;
use Elio\ElioDataDiscovery\Core\Sync\SyncService;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
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
    private const BASE_DIR = 'elio-data-discovery-export';

    /**
     * @param ProfileInterface[] $profiles
     * @param SyncService $syncService
     * @param MessageBusInterface $messageBus
     */
    public function __construct(
        private readonly iterable $profiles,
        private readonly SyncService $syncService,
        private readonly MessageBusInterface $messageBus,
        private readonly FilesystemOperator $fileSystem
    ) {}

    /**
     * @Route("/api/_action/elio-data-discovery/sync-profile/profiles", name="api.action.elio-data-discovery.sync-profile.profiles", methods={"GET"})
     *
     * @return JsonResponse
     */
    public function profiles(): JsonResponse
    {
        $profiles = [];
        /** @var ProfileInterface $profile */
        foreach ($this->profiles as $profile) {
            $name = $profile->getName();
            if (array_key_exists($profile->getName(), $profiles)) {
                return new JsonResponse([
                    'error' => sprintf('Duplicated profile name %s', $name)
                ], 400);
            }

            $profiles[$name] = [
                'name' => $name,
                'dataTypes' => $profile->getDataTypes(),
                'features' => $profile->getFeatures()
            ];
        }

        return new JsonResponse([
            'profiles' => $profiles,
        ]);
    }

    /**
     * @Route("/api/_action/elio-data-discovery/export/status/{id}", name="api.action.elio-data-discovery.export.status", methods={"GET"})
     * @throws FilesystemException
     */
    public function status(string $id, Context $context): JsonResponse
    {
        try {
            $syncProfile = $this->syncService->getSyncProfileConfiguration($id, $context);
            return new JsonResponse([
                'finished' => $syncProfile->getLastGenerationFinishedAt() > $syncProfile->getLastGenerationStartedAt(),
                'started' => $syncProfile->getLastGenerationStartedAt(),
                'finishedAt' => $syncProfile->getLastGenerationFinishedAt(),
            ]);
        } catch (\Exception) {
            return new JsonResponse([
                'finished' => false,
            ]);
        }
    }

    /**
     * Provides the generated file.
     *
     * @Route("/api/_action/elio-data-discovery/export/download/{id}/{humanReadableIdentifier}", name="api.action.elio-data-discovery.export.download", defaults={"auth_required"=false}, methods={"GET"})
     * @throws FileNotFoundException
     * @throws FilesystemException
     */
    public function download(Request $request, string $id, Context $context): Response
    {
        $syncProfile = $this->syncService->getSyncProfileConfiguration($id, $context);

        if($response = $this->authenticate($request, $syncProfile)) {
            return $response;
        }

        return $this->createFileResponse($syncProfile);
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
     * @Route("/api/_action/elio-data-discovery/export/generate/{id}", name="api.action.elio-data-discovery.export.generate", methods={"POST"})
     */
    public function generate(string $id, RequestDataBag $data, Context $context): Response
    {
        $syncProfile = $this->syncService->getSyncProfileConfiguration($id, $context);
        $startedDate = new \DateTime();
        $this->messageBus->dispatch((new Envelope(new SyncProfileMessage($syncProfile, $data->all(), $context)))->with(new DelayStamp(1000)));
        return new JsonResponse(['id' => $id, 'status' => 'starting', 'started' => $startedDate]);
    }

    /**
     * Creates a file response for the given export
     *
     * @param SyncProfileEntity $syncProfileEntity
     * @return Response
     * @throws FilesystemException
     */
    public function createFileResponse(SyncProfileEntity $syncProfileEntity): Response
    {
        if (!$this->exists($syncProfileEntity)) {
            throw new FileNotFoundException($syncProfileEntity->getId());
        }

        $fileName = CSVOutput::createFileName(self::BASE_DIR, $syncProfileEntity);
        $headers = [
            'Content-Disposition' => HeaderUtils::makeDisposition(
                'attachment',
                $syncProfileEntity->getName().'.csv',
                // only printable ascii
                preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $syncProfileEntity->getName().'.csv') ?? ''
            ),
            'Content-Length' => $this->fileSystem->fileSize($fileName),
            'Content-Type' => 'application/octet-stream',
        ];

        $stream = $this->fileSystem->readStream($fileName);
        if (!is_resource($stream)) {
            throw new FileNotFoundException($syncProfileEntity->getId());
        }

        return new StreamedResponse(function () use ($stream): void {
            fpassthru($stream);
        }, Response::HTTP_OK, $headers);
    }

    /**
     * Checks if the export exists
     *
     * @param SyncProfileEntity $syncProfileEntity
     * @return bool
     * @throws FilesystemException
     */
    public function exists(SyncProfileEntity $syncProfileEntity): bool
    {
        $fileName = CSVOutput::createFileName(self::BASE_DIR, $syncProfileEntity);
        return $this->fileSystem->has($fileName);
    }
}