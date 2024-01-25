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

namespace Elio\ElioSearch\Core\Sync\Sorting\Controller;

use Elio\ElioSearch\Core\Sync\Sorting\ProductSortingService;
use Exception;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

/**
 * Class ProductSortingController
 * @package Elio\ElioSearch\Core\Sync\Sorting\Controller
 * @category Shopware
 * @author elio GmbH <support@elio-systems.com>
 * @author Danil Lukov <dl@elio-systems.com>
 * @copyright Copyright (c) 2023, elio GmbH (https://www.elio-systems.com)
 */
#[Route(defaults: ['_routeScope' => ['api']])]
class ProductSortingController extends AbstractController
{
    public function __construct(
        private readonly ProductSortingService $productSortingService,
        private readonly LoggerInterface $logger
    ){
    }

    /**
     * @param Context $context
     * @return JsonResponse
     */
    #[Route(path: '/api/_action/elio-search/product-sorting/sort', name: 'api.action.elio-search.product-sorting.sort', methods: ['POST'])]
    public function sort(Context $context): JsonResponse
    {
        try {
            $this->productSortingService->sort($context);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage(), [
                'plugin' => 'ElioSearch',
                'trace' => $e->getTraceAsString()
            ]);
            return new JsonResponse('Something went wrong', 500);
        }

        return new JsonResponse();
    }

    /**
     * @param Request $request
     * @param Context $context
     * @return JsonResponse
     */
    #[Route(path: '/api/_action/elio-search/product-sorting/position', name: 'api.action.elio-search.product-sorting.position', methods: ['PATCH'])]
    public function changePosition(Request $request, Context $context): JsonResponse
    {
        $position = $request->get('position');
        $categoryId = $request->get('categoryId');
        $productId = $request->get('productId');

        if (!$position || !$categoryId || !$productId) {
            return new JsonResponse('Position, categoryId or productId is not set', 400);
        }

        try {
            $this->productSortingService->changePosition($position, $categoryId, $productId, $context);
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), [
                'plugin' => 'ElioSearch',
                'trace' => $e->getTraceAsString()
            ]);
            return new JsonResponse($e->getMessage(), 500);
        }

        return new JsonResponse();
    }
}