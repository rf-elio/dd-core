<?php declare(strict_types=1);

namespace Elio\FactFinder\Core\Export\Writer;


use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Elio\FactFinder\Core\Export\ExportEntity;
use Elio\FactFinder\Core\Export\SeoRoute;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class SeoUrlWriterDecoratorM
 * @package Core\Export\Writer
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class SeoUrlWriterDecorator implements FileWriterInterface
{
    private FileWriterInterface $decorated;
    private Connection $connection;
    private RouterInterface $router;
    private ?SalesChannelContext $salesChannelContext = null;
    private string $baseUrl = '';

    /**
     * @param FileWriterInterface $decorated
     * @param Connection $connection
     * @param RouterInterface $router
     */
    public function __construct(
        FileWriterInterface $decorated,
        Connection $connection,
        RouterInterface $router
    )
    {
        $this->decorated = $decorated;
        $this->connection = $connection;
        $this->router = $router;
    }

    /**
     * All exports are supported for seo injection
     *
     * @param ExportEntity $export
     * @return bool
     */
    public function supports(ExportEntity $export): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function open(SalesChannelContext $context)
    {
        $this->salesChannelContext = $context;

        $salesChannel = $this->salesChannelContext->getSalesChannel();
        foreach ($salesChannel->getDomains() as $domain) {
            if($domain->getLanguageId() === $salesChannel->getLanguageId()) {
                $this->baseUrl = rtrim($domain->getUrl(), '/');
                break;
            }
        }

        $this->router->setContext(RequestContext::fromUri($this->baseUrl));

        return $this->decorated->open($context);
    }

    /**
     * @inheritDoc
     */
    public function registerModel(array $model): void
    {
        $this->decorated->registerModel($model);
    }

    /**
     * Resolves the SeoRoutes before passing to the decorated service
     *
     * @param resource $handle
     * @param array $items
     */
    public function writeList($handle, array $items): void
    {
        $routeResolveGroups = $this->extractRoutes($items);
        $this->resolveSeoUrls($routeResolveGroups);
        $this->resolveUnresolved($routeResolveGroups);
        $this->decorated->writeList($handle, $items);
    }

    /**
     * Extracts all resolvable seo routes given in the current list of items
     *
     * @param array $items
     * @return SeoRoute[][][]
     */
    private function extractRoutes(array $items) : array
    {
        $routeResolveGroups = [];

        foreach ($items as $item) {
            foreach ($item->getParams() as $value) {
                if(!$value instanceof SeoRoute) {

                    continue;
                }

                $routeName = $value->getRouteName();
                if(!isset($routeResolveGroups[$routeName])) {
                    $routeResolveGroups[$routeName] = [];
                }
                if(!isset($routeResolveGroups[$routeName][$value->getId()])) {
                    $routeResolveGroups[$routeName][$value->getId()] = [];
                }

                $routeResolveGroups[$routeName][$value->getId()][] = $value;
            }
        }

        return $routeResolveGroups;
    }

    /**
     * Resolves the seo urls for the given route group
     *
     * @param SeoRoute[][][] $routeResolveGroups
     */
    protected function resolveSeoUrls(array $routeResolveGroups): void
    {
        if(!$this->salesChannelContext) {
            return;
        }

        foreach ($routeResolveGroups as $routeName => $seoRouteGroups) {
            $ids = array_keys($seoRouteGroups);
            $seoUrls = $this->getSeoUrls($ids, $routeName, $this->salesChannelContext);

            foreach ($seoUrls as $seoUrl) {
                $id = $seoUrl['id'];
                $path = $seoUrl['path'];

                foreach ($seoRouteGroups[$id] as $seoRoute) {
                    $seoRoute->setUrl($this->baseUrl.'/'.$path);
                }
            }
        }
    }

    /**
     * Resolves the technical url if no seo url is available
     *
     * @param SeoRoute[][][] $routeResolveGroups
     */
    protected function resolveUnresolved(array $routeResolveGroups) : void
    {
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
     * Clears the seo generation context for the next write process
     *
     * @param ExportEntity $export
     * @param SalesChannelContext $context
     * @param resource $handle
     */
    public function close(ExportEntity $export, SalesChannelContext $context, $handle): void
    {
        $this->salesChannelContext = null;
        $this->decorated->close($export, $context, $handle);
    }

    /**
     * Clears the seo generation content for the next write process
     *
     * @param resource $handle
     */
    public function abort($handle): void
    {
        $this->salesChannelContext = null;
        $this->decorated->abort($handle);
    }

    /**
     * @notice Copied by shopware's AbstractUrlProvider
     *
     * @param array $ids
     * @param string $routeName
     * @param SalesChannelContext $context
     * @return array
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

        return $this->connection->fetchAll(
            $sql,
            [
                'routeName' => $routeName,
                'languageId' => Uuid::fromHexToBytes($context->getSalesChannel()->getLanguageId()),
                'salesChannelId' => Uuid::fromHexToBytes($context->getSalesChannelId()),
                'ids' => Uuid::fromHexToBytesList(array_values($ids)),
            ],
            [
                'ids' => ArrayParameterType::STRING,
            ]
        );
    }
}