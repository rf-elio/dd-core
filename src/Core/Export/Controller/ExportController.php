<?php declare(strict_types=1);

namespace Elio\ElioSearch\Core\Export\Controller;

use Elio\ElioSearch\Core\Export\ExportEntity;
use Elio\ElioSearch\Core\Export\ExportGenerateMessage;
use Elio\ElioSearch\Core\Export\ExportService;
use Elio\ElioSearch\Core\Export\ExportStorageService;
use League\Flysystem\FilesystemException;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_routeScope"={"api"}})
 */
#[Route(defaults: ['_routeScope' => ['api']])]
class ExportController extends AbstractController
{
    private ExportStorageService $exportStorageService;
    private ExportService $exportService;
    private MessageBusInterface $messageBus;

    /**
     * @param ExportStorageService $exportStorageService
     * @param ExportService $exportService
     * @param MessageBusInterface $messageBus
     */
    public function __construct(
        ExportStorageService $exportStorageService,
        ExportService $exportService,
        MessageBusInterface $messageBus
    )
    {
        $this->exportStorageService = $exportStorageService;
        $this->exportService = $exportService;
        $this->messageBus = $messageBus;
    }

    /**
     * @Route("/api/_action/elio-search/export/status/{id}", name="api.action.elio-search.export.status", methods={"GET"})
     * @throws FilesystemException
     */
    public function features(string $id, Context $context): JsonResponse
    {
        $criteria = new Criteria([$id]);
        $export = $this->exportService->getExports($criteria, $context)->first();

        if(!$export) {
            return new JsonResponse([
                'exists' => false,
                'location' => ''
            ]);
        }

        return new JsonResponse([
            'exists' => $this->exportStorageService->exists($export),
            'path' => $this->exportStorageService->createFileName($export)
        ]);
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
        $criteria = new Criteria([$id]);
        $export = $this->exportService->getExports($criteria, $context)->first();

        if(!$export) {
            throw new NotFoundHttpException(sprintf('Export "%s" does not exists', $id));
        }

        if($response = $this->authenticate($request, $export)) {
            return $response;
        }

        return $this->exportStorageService->createFileResponse($export);
    }

    /**
     * Validates the given auth set in the elio search export
     *
     * @param Request $request
     * @param ExportEntity $export
     * @return Response|null
     */
    private function authenticate(Request $request, ExportEntity $export): ?Response
    {
        $givenUsername = $request->server->get('PHP_AUTH_USER');
        $givenPassword = $request->server->get('PHP_AUTH_PW');
        $requiredUsername = $export->getDownloadUsername();
        $requiredPassword = $export->getDownloadPassword();

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
        $criteria = new Criteria([$id]);
        $export = $this->exportService->getExports($criteria, $context)->first();

        if(!$export) {
            throw new NotFoundHttpException(sprintf('Export "%s" does not exists', $id));
        }

        $startedDate = new \DateTime();

        $this->messageBus->dispatch((new Envelope(new ExportGenerateMessage($export, $context)))->with(new DelayStamp(1000)));
        return new JsonResponse(['id' => $id, 'status' => 'starting', 'started' => $startedDate]);
    }
}
