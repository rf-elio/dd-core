<?php


namespace Elio\FactFinder\Core\Logging\Controller;

use Elio\FactFinder\Core\Logging\LoggingService;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"api"})
 * Class LoggingController
 *
 * @package Elio\FactFinder\Core\Logging\Controller
 */
class LoggingController extends AbstractController
{
    private LoggingService $loggingService;

    /**
     * LoggingController constructor.
     *
     * @param LoggingService $loggingService
     */
    public function __construct(LoggingService $loggingService)
    {
        $this->loggingService = $loggingService;
    }

    /**
     * @Route("/api/_action/ff/logging/show", name="api.action.elio-ff.logging.show", methods={"GET"})
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function show(Request $request): JsonResponse
    {
        $logs = $this->loggingService->getLogs();
        $logIndex = $request->get('log', 0);

        $content = $this->loggingService->getLogContent($logIndex);

        return $this->json([
            'success' => true,
            'data' => [
                'logs' => $logs,
                'logContent' => $content
            ]
        ]);
    }
}
