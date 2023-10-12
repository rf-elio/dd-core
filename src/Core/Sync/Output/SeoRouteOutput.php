<?php

namespace Elio\ElioSearch\Core\Sync\Output;


use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Elio\ElioSearch\Core\Sync\DataTypes\DataTypeInterface;
use Elio\ElioSearch\Core\Sync\SalesChannelContextCollection;
use Elio\ElioSearch\Core\Sync\SyncContext;
use Shopware\Core\Framework\Struct\Collection;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class SeoRoute
 * @package Elio\ElioSearch\Core\Sync\Output\File
 * @category Shopware
 * @author elio GmbH <support@elio-systems.com>
 * @author Danil Lukov <dl@elio-systems.com>
 * @copyright Copyright (c) 2023, elio GmbH (https://www.elio-systems.com)
 */
class SeoRouteOutput implements OutputInterface, WriteAwareInterface, HandleInterface
{
    public const TYPE = self::class;

    private ?SalesChannelContextCollection $salesChannelContexts;
    private array $baseUrl = [];

    public function __construct(
        private readonly Connection $connection,
        private readonly RouterInterface $router,
    )
    {
        $this->salesChannelContexts = new SalesChannelContextCollection();
    }

    public function supports(string $type): bool
    {
        return self::TYPE === $type;
    }

    public function open(SyncContext $syncContext): void
    {
        $this->salesChannelContexts = $syncContext->getSalesChannelContexts();

        foreach ($this->salesChannelContexts as $languageId => $context) {
            $salesChannel = $context->getSalesChannel();
            foreach ($salesChannel->getDomains() as $domain) {
                if ($domain->getLanguageId() === $context->getLanguageId()) {
                    $this->baseUrl[$languageId] = rtrim($domain->getUrl(), '/');
                    break;
                }
            }
        }
    }

    public function close(): void
    {
        $this->baseUrl = [];
        $this->salesChannelContexts = new SalesChannelContextCollection();
    }

    /**
     * @throws Exception
     */
    public function write(Collection $collection, SyncContext $syncContext): void
    {
        foreach ($this->salesChannelContexts as $salesChannelContext) {
            $routeResolveGroups = $this->extractRoutes($collection, $salesChannelContext);
            $this->resolveSeoUrls($routeResolveGroups, $salesChannelContext);
            $this->resolveUnresolved($routeResolveGroups, $salesChannelContext);
        }
    }

    /**
     * Extracts all resolvable seo routes given in the current list of items
     *
     * @param Collection $items
     * @param SalesChannelContext $context
     * @return SeoRoute[][][]
     */
    private function extractRoutes(Collection $items, SalesChannelContext $context) : array
    {
        $routeResolveGroups = [];

        /** @var DataTypeInterface $item */
        foreach ($items as $item) {
            $seoRoute = $item->getDataTypeTranslation($context->getLanguageId())?->getExtension(SeoRoute::class);

            if(!$seoRoute) {
                continue;
            }

            $routeName = $seoRoute->getRouteName();
            if(!isset($routeResolveGroups[$routeName])) {
                $routeResolveGroups[$routeName] = [];
            }
            if(!isset($routeResolveGroups[$routeName][$seoRoute->getId()])) {
                $routeResolveGroups[$routeName][$seoRoute->getId()] = [];
            }

            $routeResolveGroups[$routeName][$seoRoute->getId()][] = $seoRoute;
        }

        return $routeResolveGroups;
    }

    /**
     * Resolves the seo urls for the given route group
     *
     * @param SeoRoute[][][] $routeResolveGroups
     * @throws Exception
     */
    protected function resolveSeoUrls(array $routeResolveGroups, SalesChannelContext $context): void
    {
        foreach ($routeResolveGroups as $routeName => $seoRouteGroups) {
            $ids = array_keys($seoRouteGroups);

            $seoUrls = $this->getSeoUrls($ids, $routeName, $context);

            foreach ($seoUrls as $seoUrl) {
                $id = $seoUrl['id'];
                $path = $seoUrl['path'];

                foreach ($seoRouteGroups[$id] as $seoRoute) {
                    $seoRoute->setUrl($this->baseUrl[$context->getLanguageId()] . '/' . $path);
                }
            }
        }
    }

    /**
     * Resolves the technical url if no seo url is available
     *
     * @param SeoRoute[][][] $routeResolveGroups
     */
    protected function resolveUnresolved(array $routeResolveGroups, SalesChannelContext $context) : void
    {
        $baseUrl = $this->baseUrl[$context->getLanguageId()];
        $this->router->setContext(RequestContext::fromUri($baseUrl));

        foreach ($routeResolveGroups as $seoRouteGroups) {
            foreach ($seoRouteGroups as $seoRouteGroup) {
                foreach ($seoRouteGroup as $seoRoute) {
                    if(!$seoRoute->isResolved()) {
                        $seoRoute->setUrl($this->router->generate(
                            $seoRoute->getRouteName(), $seoRoute->getParameters(), UrlGeneratorInterface::ABSOLUTE_URL
                        ));
                    }
                }
            }
        }
    }

    /**
     * @notice Copied by shopware's AbstractUrlProvider
     *
     * @param array $ids
     * @param string $routeName
     * @param SalesChannelContext $context
     * @return array
     * @throws Exception
     */
    protected function getSeoUrls(array $ids, string $routeName, SalesChannelContext $context): array
    {
        $sql = 'SELECT LOWER(HEX(foreign_key)) as id, seo_path_info as path
                    FROM seo_url WHERE foreign_key IN (:ids)
                     AND `seo_url`.`route_name` =:routeName
                     AND `seo_url`.`is_canonical` = 1
                     AND `seo_url`.`is_deleted` = 0
                     AND `seo_url`.`language_id` =:languageId
                     AND (`seo_url`.`sales_channel_id` =:salesChannelId OR seo_url.sales_channel_id IS NULL)';

        return $this->connection->fetchAllAssociative(
            $sql,
            [
                'routeName' => $routeName,
                'languageId' => Uuid::fromHexToBytes($context->getLanguageId()),
                'salesChannelId' => Uuid::fromHexToBytes($context->getSalesChannelId()),
                'ids' => Uuid::fromHexToBytesList(array_values($ids)),
            ],
            [
                'ids' => ArrayParameterType::STRING,
            ]
        );
    }
}