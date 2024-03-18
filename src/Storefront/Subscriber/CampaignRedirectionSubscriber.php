<?php

namespace Elio\ElioDataDiscovery\Storefront\Subscriber;


use Elio\ElioDataDiscovery\Api\Search\Response\CampaignRedirectionResponse;
use Elio\ElioDataDiscovery\Storefront\Exception\CampaignRedirectionException;
use Shopware\Core\Content\Cms\Aggregate\CmsBlock\CmsBlockCollection;
use Shopware\Core\Content\Cms\Aggregate\CmsSection\CmsSectionCollection;
use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotCollection;
use Shopware\Core\Content\Cms\SalesChannel\Struct\ProductListingStruct;
use Shopware\Core\SalesChannelRequest;
use Shopware\Storefront\Page\Navigation\NavigationPageLoadedEvent;
use Shopware\Storefront\Page\Search\SearchPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Handles the redirection campaign redirect
 *
 * Class CampaignRedirectionSubscriber
 * @package Elio\ElioDataDiscovery\Storefront\Subscriber
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class CampaignRedirectionSubscriber implements EventSubscriberInterface
{
    /**
     * @return string[]
     */
    public static function getSubscribedEvents() : array
    {
        return [
            SearchPageLoadedEvent::class => 'onSearchPageLoadedEvent',
            NavigationPageLoadedEvent::class => 'onNavigationPageLoadedEvent',
            KernelEvents::EXCEPTION => 'onCampaignRedirectionException'
        ];
    }

    /**
     * If the result contains a redirection campaign a redirect exception will be thrown to initiate the redirect.
     *
     * @param SearchPageLoadedEvent $event
     */
    public function onSearchPageLoadedEvent(SearchPageLoadedEvent $event) : void
    {
        /** @var CampaignRedirectionResponse|null $campaignRedirection */
        $campaignRedirection = $event->getPage()->getListing()->getExtension(CampaignRedirectionResponse::class);

        if($campaignRedirection) {
            throw new CampaignRedirectionException($campaignRedirection);
        }
    }

    /**
     * If the result contains a redirection campaign a redirect exception will be thrown to initiate the redirect.
     *
     * @param NavigationPageLoadedEvent $event
     */
    public function onNavigationPageLoadedEvent(NavigationPageLoadedEvent $event) : void
    {
        $page = $event->getPage();
        if(!$page->getCmsPage()) {
            return;
        }

        $sections = $page->getCmsPage()->getSections() ?? new CmsSectionCollection();
        foreach ($sections as $section) {
            $blocks = $section->getBlocks() ?? new CmsBlockCollection();
            foreach ($blocks as $block) {
                $slots = $block->getSlots() ?? new CmsSlotCollection();
                foreach ($slots as $slot) {
                    $data = $slot->getData();
                    if (!$data instanceof ProductListingStruct || !$data->getListing()) {
                        continue;
                    }

                    /** @var CampaignRedirectionResponse|null $campaignRedirection */
                    $campaignRedirection = $data->getListing()->getExtension(CampaignRedirectionResponse::class);

                    if ($campaignRedirection) {
                        throw new CampaignRedirectionException($campaignRedirection);
                    }
                }
            }
        }
    }

    /**
     * Executes the redirect to the givne target location
     *
     * @param ExceptionEvent $event
     */
    public function onCampaignRedirectionException(ExceptionEvent $event): void
    {
        if (!$event->getRequest()->attributes->has(SalesChannelRequest::ATTRIBUTE_IS_SALES_CHANNEL_REQUEST)) {
            return;
        }

        $exception = $event->getThrowable();
        if(!$exception instanceof CampaignRedirectionException) {
            return;
        }

        if($event->getRequest()->isXmlHttpRequest()) {
            $event->setResponse(new Response('', Response::HTTP_UNAUTHORIZED));
            return;
        }

        $event->setResponse(new RedirectResponse($exception->getCampaignRedirectionResponse()->getTargetUrl()));
    }
}