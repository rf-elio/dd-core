<?php


namespace Elio\FactFinder\Core\Logging\Controller;

use Elio\FactFinder\Core\Logging\LoggingService;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

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
     * @Route("/api/_action/ff/logging/{index}", name="api.action.elio-ff.logging.index", methods={"GET"})
     * @param Request $request
     * @param int $index
     *
     * @return JsonResponse
     */
    public function index(Request $request, int $index = 0): JsonResponse
    {
        $offset = $request->get('offset', 0);
        $limit = $request->get('limit', 10);

        try {
            $contents = $this->loggingService->getLogContents($index);

            return $this->json([
                'success' => true,
                'data' => [
                    'logs' => $this->loggingService->getLogs(),
                    'contents' => array_slice($contents, $offset * $limit, $limit),
                    'contentsTotal' => count($contents)
                ]
            ]);
        } catch (Throwable $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * @Route("/api/_action/ff/logging/{index}", name="api.action.elio-ff.logging.delete", methods={"DELETE"})
     * @param int $index
     *
     * @return JsonResponse
     */
    public function delete(int $index): JsonResponse
    {
        try {
            $this->loggingService->delete($index);

            return $this->json([
                'success' => true
            ]);
        } catch (Throwable $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
