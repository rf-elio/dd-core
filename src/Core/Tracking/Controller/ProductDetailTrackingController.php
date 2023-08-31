<?php declare(strict_types=1);
/**
 * Copyright (c) 2021, elio GmbH.
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

namespace Elio\ElioSearch\Core\Tracking\Controller;

use Elio\ElioSearch\Api\Tracking\Request\ProductDetailTrackingRequest;
use Elio\ElioSearch\Configuration\ElioSearchConfigServiceInterface;
use Elio\ElioSearch\Core\Tracking\AllowedChecker\TrackingAllowedCheckerInterface;
use Elio\ElioSearch\Core\Tracking\Event\ProductDetailTrackingRequestCreatedEvent;
use Elio\ElioSearch\Core\Tracking\Message\TrackingMessage;
use Elio\ElioSearch\Core\Tracking\Utils\TrackingSessionTrait;
use Psr\EventDispatcher\EventDispatcherInterface;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SalesChannel\SuccessResponse;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ProductDetailTrackingController
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Simon Greiner <sg@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 * @Route(defaults={"_routeScope"={"storefront"}})
 */
#[Route(defaults: ['_routeScope' => ['storefront']])]
class ProductDetailTrackingController extends StorefrontController
{
    private ElioSearchConfigServiceInterface $configService;
    private MessageBusInterface $bus;
    private EventDispatcherInterface $eventDispatcher;
    private TrackingAllowedCheckerInterface $trackingAllowedChecker;
    private EntityRepository $productRepository;
    private RequestStack $requestStack;
    use TrackingSessionTrait;

    /**
     * @param ElioSearchConfigServiceInterface $configService
     * @param TrackingAllowedCheckerInterface $trackingAllowedChecker
     * @param MessageBusInterface $bus
     * @param EventDispatcherInterface $eventDispatcher
     * @param EntityRepository $productRepository
     * @param RequestStack $requestStack
     */
    public function __construct(
        ElioSearchConfigServiceInterface $configService,
        TrackingAllowedCheckerInterface $trackingAllowedChecker,
        MessageBusInterface $bus,
        EventDispatcherInterface $eventDispatcher,
        EntityRepository $productRepository,
        RequestStack $requestStack
    )
    {
        $this->configService = $configService;
        $this->bus = $bus;
        $this->eventDispatcher = $eventDispatcher;
        $this->trackingAllowedChecker = $trackingAllowedChecker;
        $this->productRepository = $productRepository;
        $this->requestStack = $requestStack;
    }

    /**
     * @Route("/widgets/elio-search/productDetailTrack", name="widgets.elio-search.tracking.product-detail", methods={"POST"}, defaults={"XmlHttpRequest"=true,"csrf_protected"=false})
     *
     * @param RequestDataBag $dataBag
     * @param SalesChannelContext $salesChannelContext
     * @return Response
     */
    public function trackProductDetail(RequestDataBag $dataBag, SalesChannelContext $salesChannelContext): Response
    {
        $config = $this->configService->getByContext($salesChannelContext);

        if(
            !$config->isActive() ||
            !$config->isTrackProductView() ||
            !$dataBag->has('elioSearchProductTrackingData') ||
            empty($dataBag->get('elioSearchProductTrackingData')->get('query')) ||
            !$this->trackingAllowedChecker->isTrackingAllowed($salesChannelContext)
        ) {
            return new SuccessResponse();
        }

        /** @var RequestDataBag $trackingData */
        $trackingData = $dataBag->get('elioSearchProductTrackingData');
        $masterProductNumber = $productNumber = $trackingData->get('productNumber');
        $parentProductId = $trackingData->get('parentProductId');

        if (!empty($parentProductId)) {
            /** @var ProductEntity|null $parentProduct */
            $parentProduct = $this->productRepository->search(new Criteria([$parentProductId]), $salesChannelContext->getContext())->first();
            $masterProductNumber = $parentProduct ? $parentProduct->getProductNumber() : $productNumber;
        }

        $customerId = $salesChannelContext->getCustomer() ? $salesChannelContext->getCustomer()->getId() : null;
        $trackingRequest = new ProductDetailTrackingRequest();
        $trackingRequest->addEvent(
            $productNumber,
            $this->getTrackingSessionId($this->requestStack),
            $masterProductNumber,
            $trackingData->get('label'),
            $trackingData->get('query'),
            $trackingData->get('pos'),
            $trackingData->get('page'),
            $trackingData->get('pageSize'),
            $trackingData->get('campaign'),
            $customerId
        );

        $requestCreatedEvent = new ProductDetailTrackingRequestCreatedEvent($trackingRequest);
        $this->eventDispatcher->dispatch($requestCreatedEvent);
        $this->bus->dispatch(new TrackingMessage(
            $requestCreatedEvent->getRequest(),
            $salesChannelContext->getSalesChannelId()
        ));
        return new SuccessResponse();
    }
}
