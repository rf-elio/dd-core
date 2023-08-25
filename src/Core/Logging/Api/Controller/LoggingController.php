<?php declare(strict_types=1);


namespace Elio\ElioSearch\Core\Logging\Api\Controller;

use Elio\ElioSearch\Core\Logging\LoggingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

/**
 * @Route(defaults={"_routeScope"={"api"}})
 * Class LoggingController
 *
 * @package Elio\ElioSearch\Core\Logging\Api\Controller
 */
#[Route(defaults: ['_routeScope' => ['api']])]
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
