<?php

namespace Elio\FactFinder\Core\Export\Controller;

use Elio\FactFinder\Core\Export\ExportService;
use Elio\FactFinder\Core\Export\ExportStorageService;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"api"})
 */
class ExportController extends AbstractController
{
    private ExportStorageService $exportStorageService;
    private ExportService $exportService;

    /**
     * @param ExportStorageService $exportStorageService
     * @param ExportService $exportService
     */
    public function __construct(ExportStorageService $exportStorageService, ExportService $exportService)
    {
        $this->exportStorageService = $exportStorageService;
        $this->exportService = $exportService;
    }

    /**
     * @Route("/api/_action/ff/export/status/{id}", name="api.action.elio-ff.export.status", methods={"GET"})
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
     * @Route("/api/_action/ff/export/download/{id}", name="api.action.elio-ff.export.download", defaults={"auth_required"=false}, methods={"GET"})
     */
    public function download(string $id, Context $context): Response
    {
        $criteria = new Criteria([$id]);
        $export = $this->exportService->getExports($criteria, $context)->first();

        if(!$export) {
            throw new NotFoundHttpException(sprintf('Export "%s" does not exists', $id));
        }

        return $this->exportStorageService->createFileResponse($export);
    }
}