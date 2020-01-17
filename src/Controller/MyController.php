<?php declare(strict_types=1);

namespace Elio\ElioFactFinder\Controller;

use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\Framework\Context;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;


/**
 * @RouteScope(scopes={"storefront"})
 */
class MyController extends AbstractController
{

    /**
     * @Route("/first", name="my.firt", methods={"GET"})
     */
    public function myFirst(Request $request, Context $context): JsonResponse
    {
        $myService = $this->container->get('Elio\ElioFactFinder\Service\MyService');
        $exportDirectoryService = $myService->getExportDirectory();
        $fileSystemService = $myService->getFileSystem();

        $filePath = sprintf('%s/factfinder_Headless.csv', $exportDirectoryService);
        $fileContent = $fileSystemService->read($filePath);

        return new JsonResponse($fileContent);
    }

}
