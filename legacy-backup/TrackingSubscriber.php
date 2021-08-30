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

namespace Elio\FactFinder\Subscriber;

use Elio\FactFinder\Components\ElioFactFinderService;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Shopware\Storefront\Page\Product\ProductPageLoadedEvent;
use Shopware\Core\Checkout\Cart\Event\LineItemAddedEvent;
use Shopware\Core\Checkout\Cart\Event\CheckoutOrderPlacedEvent;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepositoryInterface;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextFactory;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextService;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Symfony\Component\HttpFoundation\Request;
use Shopware\Storefront\Page\Search\SearchPageLoader;
use Shopware\Storefront\Page\Search\SearchPage;
use Symfony\Component\HttpFoundation\RequestStack;


/**
 * @todo: Keep what we need and remove this file
 * Class TrackingSubscriber
 * @category  Subscriber
 * @package   Shopware\Plugins\FactFinder\Subscriber
 * @author    Raoul Yemetio <ry@elio-systems.com>
 * @copyright Copyright (c) 2020, elio GmbH (http://www.elio-systems.com)
 */
class TrackingSubscriber implements EventSubscriberInterface
{
    /**
     * @var array
     */
    private $params = [];

    /**
     * @var ElioFactFinderService
     */
    private $ffService;

    /**
     * @var SalesChannelRepositoryInterface
     */
    private $productRepository;

    /**
     * @var SalesChannelContextFactory
     */
    private $salesChannelContextFactory;

    /**
     * @var SearchPageLoader
     */
    private $searchPageLoader;

    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(
        ElioFactFinderService $ffService,
        SalesChannelRepositoryInterface $productRepository,
        SalesChannelContextFactory $salesChannelContextFactory,
        SearchPageLoader $searchPageLoader,
        RequestStack $requestStack
    )
    {
        $this->ffService = $ffService;
        $this->productRepository = $productRepository;
        $this->salesChannelContextFactory = $salesChannelContextFactory;
        $this->searchPageLoader = $searchPageLoader;
        $this->requestStack = $requestStack;
    }

    public static function getSubscribedEvents()
    {
        return [
            ProductPageLoadedEvent::class => 'trackClick',
            LineItemAddedEvent::class => 'trackCart',
            CheckoutOrderPlacedEvent::class => 'trackCheckout',
        ];
    }

    public function trackClick(ProductPageLoadedEvent $event):void
    {
        $request = $event->getRequest();
        $session = $request->getSession();

        $this->doTrackClick(
            $request,
            $event->getSalesChannelContext(),
            $event->getPage()->getProduct(),
            $session->getId()
        );
    }

    private function doTrackClick(
        Request $request,
        SalesChannelContext $salesChannelContext,
        SalesChannelProductEntity $product,
        string $sessionId = null
    ):void
    {
        $query = $this->getQuery($request);

        if (empty($query))
            return;

        $request->query->set('search', $query);

        /** @var SearchPage $origPage */
        $origPage = $this->searchPageLoader->load($request, $salesChannelContext);

        foreach ($this->getFilters($request) as $key => $value){
            $request->query->set($key, $value);
        }

        /** @var SearchPage $page */
        $page = $this->searchPageLoader->load($request, $salesChannelContext);

        $this->setParams(
            $product,
            $salesChannelContext
        );

        $this->addParam('query', $query);
        $this->addParam('pos', $this->getProductPosition($page, $product));
        $this->addParam('origPos', $this->getProductPosition($origPage, $product));
        $this->addParam('page', $page->getListing()->getPage());
        $this->addParam('pageSize', $page->getListing()->getLimit());
        $this->addParam('origPageSize', $origPage->getListing()->getLimit());

        $this->ffService->doTrack(
            $this->ffService->getConfig()::TRACKING_EVENT_CLICK,
            $sessionId,
            $this->getParams()
        );

    }

    private function getQuery(Request $request):?string
    {
        $referer = $request->server->getHeaders()['REFERER'];
        $data = explode('search=', $referer);

        return (count($data) > 1)? $data[1] : null;
    }

    private function getProductPosition(SearchPage $searchPage, SalesChannelProductEntity $product):?int
    {
        $pos = 0;

        /** @var SalesChannelProductEntity $entity */
        foreach ($searchPage->getListing()->getEntities()->getElements() as $entity){
            if ($entity->getId() === $product->getId())
                return $pos + 1;
            ++$pos;
        }

        return null;
    }

    private function filterExist(Request $request):bool
    {
        $referer = $request->server->getHeaders()['REFERER'];

        return ( str_contains($referer, 'manufacturer=') |
                 str_contains($referer, 'properties=') |
                 str_contains($referer, 'min-price=')  |
                 str_contains($referer, 'max-price=') |
                 str_contains($referer, 'rating=') |
                 str_contains($referer, 'shipping-free=') |
                 str_contains($referer, 'order=') |
                 str_contains($referer, 'p=')
               ) ? true : false;

    }

    private function getFilters(Request $request):array
    {
        if (!$this->filterExist($request))
            return [];

        $filters = [];
        $referer = $request->server->getHeaders()['REFERER'];
        $data = explode('&', explode('?', $referer)[1]);

        foreach ($data as $element){
            $filter = explode('=', $element);

            if ($filter[0] != 'search')
                $filters[$filter[0]] = urldecode($filter[1]);
        }

        return $filters;
    }

    private function setParams(SalesChannelProductEntity $product, SalesChannelContext $context):void
    {
        $this->clearParams();
        $this->addParam('id', $product->getId());
        $this->addParam('masterId', $product->getProductNumber());
        $this->addParam('title', $product->getTranslation("name"));
        $this->addParam('channel', $this->ffService->getConfig()->getChannel());
        if(!empty($context->getCustomer())) $this->addParam('userId',  $context->getCustomer()->getId());
    }

    private function getParams(): array
    {
        return $this->params;
    }

    private function clearParams():void
    {
        $this->params = [];
    }

    private function addParam(string $key, $value):void
    {
        $this->params[$key] = $value;
    }

    public function trackCart(LineItemAddedEvent $event): void
    {
        $lineItem = $event->getLineItem();
        $request = $this->requestStack->getMasterRequest();

        if($lineItem->getType() === LineItem::PRODUCT_LINE_ITEM_TYPE){
            /** @var SalesChannelProductEntity $product */
            $product = $this->productRepository->search(
                new Criteria([$lineItem->getId()]),
                $event->getContext()
            )->first();

            $this->setParams(
                $product,
                $event->getContext()
            );

            $this->addParam('count', $lineItem->getQuantity());
            $this->addParam('price', $product->getCalculatedPrice()->getTotalPrice());

            $this->ffService->doTrack(
                $this->ffService->getConfig()::TRACKING_EVENT_CART,
                null,
                $this->getParams()
            );

            $this->doTrackClick($request, $event->getContext(), $product);

        }
    }

    public function trackCheckout(CheckoutOrderPlacedEvent $event): void
    {
        /** @var OrderLineItemEntity $lineItem */
        foreach ($event->getOrder()->getLineItems()->getElements() as $lineItem){
            if($lineItem->getType() === LineItem::PRODUCT_LINE_ITEM_TYPE) {

                $salesChannelContext = $this->salesChannelContextFactory->create(
                    Uuid::randomHex(),
                    $event->getSalesChannelId(),
                    [SalesChannelContextService::LANGUAGE_ID => $event->getOrder()->getLanguageId()]
                );

                /** @var SalesChannelProductEntity $product */
                $product = $this->productRepository->search(
                    new Criteria([$lineItem->getProductId()]),
                    $salesChannelContext
                )->first();

                $this->setParams($product, $salesChannelContext);

                $this->addParam('count', $lineItem->getQuantity());
                $this->addParam('price', $product->getCalculatedPrice()->getTotalPrice());

                $this->ffService->doTrack(
                    $this->ffService->getConfig()::TRACKING_EVENT_CHECKOUT,
                    null,
                    $this->getParams()
                );
            }
        }
    }
}
