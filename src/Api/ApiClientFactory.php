<?php

namespace Elio\FactFinder\Api;

require_once __DIR__.'/../../vendor/autoload.php';

use Elio\FactFinder\Api\Search\Request\SearchRequest;
use Elio\FactFinder\Core\Logging\LoggingService;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Swagger\Client\Api\CampaignApi;
use Swagger\Client\Api\ImportApi;
use Swagger\Client\Api\ManagementApi;
use Swagger\Client\Api\PredbasketApi;
use Swagger\Client\Api\RecordsApi;
use Swagger\Client\Api\SearchApi;
use Swagger\Client\Api\TrackingApi;
use Elio\FactFinder\Configuration\FactFinderConfigServiceInterface;
use GuzzleHttp\ClientInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Swagger\Client\Configuration;
use Elio\FactFinder\Configuration\Configuration as PluginConfiguration;
use GuzzleHttp\Client;

/**
 * Class ApiClientFactory
 * @package Elio\FactFinder\Api
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class ApiClientFactory implements ApiClientFactoryInterface
{
    private FactFinderConfigServiceInterface $configService;
    private string $logDir;

    /**
     * ApiFactory constructor.
     * @param FactFinderConfigServiceInterface $configService
     * @param string $logDir
     */
    public function __construct(
        FactFinderConfigServiceInterface $configService,
        string $logDir
    )
    {
        $this->configService = $configService;
        $this->logDir = $logDir;
    }

    /**
     * Creates the campaign api instance, configured for the given sales channel.
     *
     * @param SalesChannelContext $salesChannelContext
     * @return CampaignApi
     */
    public function createCampaignApi(SalesChannelContext $salesChannelContext): CampaignApi
    {
        return new CampaignApi(
            $this->createClient($salesChannelContext->getSalesChannelId(), $salesChannelContext),
            $this->createConfiguration($salesChannelContext->getSalesChannelId(), $salesChannelContext)
        );
    }

    /**
     * Creates the import api instance, configured for the given sales channel.
     *
     * @param SalesChannelContext $salesChannelContext
     * @return ImportApi
     */
    public function createImportApi(SalesChannelContext $salesChannelContext): ImportApi
    {
        return new ImportApi(
            $this->createClient($salesChannelContext->getSalesChannelId(), $salesChannelContext),
            $this->createConfiguration($salesChannelContext->getSalesChannelId(), $salesChannelContext)
        );
    }

    /**
     * Creates the management api instance, configured for the given sales channel.
     *
     * @param SalesChannelContext $salesChannelContext
     * @return ManagementApi
     */
    public function createManagementApi(SalesChannelContext $salesChannelContext): ManagementApi
    {
        return new ManagementApi(
            $this->createClient($salesChannelContext->getSalesChannelId(), $salesChannelContext),
            $this->createConfiguration($salesChannelContext->getSalesChannelId(), $salesChannelContext)
        );
    }

    /**
     * Creates the predictive basket api instance, configured for the given sales channel.
     *
     * @param SalesChannelContext $salesChannelContext
     * @return PredbasketApi
     */
    public function createPredictiveBasketApi(SalesChannelContext $salesChannelContext): PredbasketApi
    {
        return new PredbasketApi(
            $this->createClient($salesChannelContext->getSalesChannelId(), $salesChannelContext),
            $this->createConfiguration($salesChannelContext->getSalesChannelId(), $salesChannelContext)
        );
    }

    /**
     * Creates the records api to update data directly in ff.
     *
     * @param string $salesChannelId
     * @return RecordsApi
     */
    public function createRecordsApi(string $salesChannelId): RecordsApi
    {
        return new RecordsApi(
            $this->createClient($salesChannelId),
            $this->createConfiguration($salesChannelId)
        );
    }

    /**
     * Creates the search api instance, configured for the given sales channel.
     *
     * @param SalesChannelContext $salesChannelContext
     * @param string|null $remoteIp
     *
     * @return SearchApi
     */
    public function createSearchApi(SalesChannelContext $salesChannelContext, ?string $remoteIp = null): SearchApi
    {
        return new SearchApi(
            $this->createClient($salesChannelContext->getSalesChannelId(), $salesChannelContext, [
                'remoteIp' => $remoteIp
            ]),
            $this->createConfiguration($salesChannelContext->getSalesChannelId(), $salesChannelContext)
        );
    }

    /**
     * Creates the tracking api instance, configured for the given sales channel.
     *
     * @param string $salesChannelId
     * @return TrackingApi
     */
    public function createTrackingApi(string $salesChannelId): TrackingApi
    {
        return new TrackingApi(
            $this->createClient($salesChannelId),
            $this->createConfiguration($salesChannelId)
        );
    }

    /**
     * Creates the api client wit the configured settings
     * @param string $salesChannelId
     * @param SalesChannelContext|null $salesChannelContext
     * @param array $params
     *
     * @return ClientInterface
     */
    protected function createClient(string $salesChannelId, SalesChannelContext $salesChannelContext = null, array $params = []) : ClientInterface
    {
        if ($salesChannelContext === null) {
            $configuration = $this->configService->get($salesChannelId);
        } else {
            $configuration = $this->configService->getByContext($salesChannelContext);
        }

        $config = [
            'max' => $configuration->getApiTimeout(),
        ];

        if ($this->loggingIsEnabled($configuration, $params)) {
            $logger = new Logger('FactFinder');
            $handler = new RotatingFileHandler(
                $this->logDir . '/' . LoggingService::FILE_NAME . '.log',
                LoggingService::MAX_LOG_FILES
            );
            $logger->pushHandler($handler);
            $stack = HandlerStack::create();
            $stack->push(
                Middleware::log($logger, new MessageFormatter(LoggingService::LOG_FORMAT))
            );

            $config['handler'] = $stack;
        }
        return new Client($config);
    }

    /**
     * @param PluginConfiguration $configuration
     * @param array $params
     *
     * @return bool
     */
    protected function loggingIsEnabled(PluginConfiguration $configuration, array $params): bool
    {
        if (!$configuration->isApiDebugActive()) {
            return false;
        }
        if (!isset($params['remoteIp'])) {
            return true;
        }
        $logDebugFilter = array_map(static fn ($item) => trim($item), $configuration->getLogDebugIpFilter());
        return in_array(trim($params['remoteIp']), $logDebugFilter, true);
    }

    /**
     * Creates the configuration struct that contains the api address and credentials
     *
     * @param string $salesChannelId
     * @param SalesChannelContext|null $salesChannelContext
     * @return Configuration
     */
    protected function createConfiguration(string $salesChannelId, SalesChannelContext $salesChannelContext = null) : Configuration
    {
        $credentials = $this->configService->getApiCredentials($salesChannelId);
        if ($salesChannelContext === null) {
            $configuration = $this->configService->get($salesChannelId);
        } else {
            $configuration = $this->configService->getByContext($salesChannelContext);
        }

        $apiConfiguration = new Configuration();
        $apiConfiguration->setHost($credentials->getApiUrl());
        $apiConfiguration->setUsername($credentials->getApiUsername());
        $apiConfiguration->setPassword($credentials->getApiPassword());
        $apiConfiguration->setAccessToken(null);
        $apiConfiguration->setUserAgent($this->getUserAgent($salesChannelId));

        return $apiConfiguration;
    }

    /**
     * Creates the user agent based on the current sales channel host.
     *
     * @param string $salesChannelId
     * @return string
     */
    protected function getUserAgent(string $salesChannelId) : string
    {
        return 'ElioFactFinder/'.$salesChannelId.'-';
    }
}
