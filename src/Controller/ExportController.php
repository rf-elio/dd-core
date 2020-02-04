<?php declare(strict_types=1);

/**
 * Copyright (c) 2020, elio GmbH.
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

namespace Elio\FactFinder\Controller;

use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\Framework\Context;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Elio\FactFinder\Service\Export\ExportManagerInterface;

/**
 * Controller for installing and generating product export
 *
 * Class ExportController
 * @category  Controller
 * @package   Shopware\Plugins\FactFinder\Controller
 * @author    Raoul Yemetio <ry@elio-systems.com>
 * @copyright Copyright (c) 2020, elio GmbH (http://www.elio-systems.com)
 *
 * @RouteScope(scopes={"storefront"})
 */
class ExportController extends AbstractController
{

    /**
     * @Route("/product/export/install", name="product_export_install", methods={"GET"})
     */
    public  function install(Request $request, Context $context): JsonResponse
    {
        /** @var ExportManagerInterface */
        $productExportManager = $this->container->get('Elio\FactFinder\Service\Export\ExportManager');

        return new JsonResponse(['product_export_installed'=>$productExportManager->install()]);
    }


    /**
     * @Route("/product/export/generate", name="product_export_generate", methods={"GET"})
     */
    public function generate(Request $request, Context $context): JsonResponse
    {
        /** @var ExportManagerInterface */
        $productExportManager = $this->container->get('Elio\FactFinder\Service\Export\ExportManager');

        return new JsonResponse(['csv_product_export_generated' =>$productExportManager->generate()]);
    }


}
