<?php declare(strict_types=1);


namespace Elio\ElioDataDiscovery\Core\Logging\Api\Controller;

use Elio\ElioDataDiscovery\Core\Logging\LoggingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

/**
 * @Route(defaults={"_routeScope"={"api"}})
 * Class LoggingController
 *
 * @package Elio\ElioDataDiscovery\Core\Logging\Api\Controller
 */
#[Route(defaults: ['_routeScope' => ['api']])]
class LoggingController extends AbstractController
{
    /**
     * LoggingController constructor.
     *
     * @param LoggingService $loggingService
     */
    public function __construct(
        private readonly LoggingService $loggingService
    ) {}

    /**
     * @Route("/api/_action/elio-data-discovery/logging/{index}", name="api.action.elio-data-discovery.logging.index", methods={"GET"})
     * @param Request $request
     * @param int $index
     *
     * @return JsonResponse
     */
    public function index(Request $request, int $index = 0): JsonResponse
    {
        $offset = (int)$request->get('offset', 0);
        $limit = (int)$request->get('limit', 10);

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
     * @Route("/api/_action/elio-data-discovery/logging/{index}", name="api.action.elio-data-discovery.logging.delete", methods={"DELETE"})
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
