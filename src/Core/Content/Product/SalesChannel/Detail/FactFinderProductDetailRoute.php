<?php


namespace Elio\FactFinder\Core\Content\Product\SalesChannel\Detail;


use Elio\FactFinder\Api\Records\RecordsApi;
use Elio\FactFinder\Api\Records\Request\DetailPageRequest;
use Elio\FactFinder\Api\Search\Response\CampaignFeedbackResponseCollection;
use Elio\FactFinder\Configuration\FactFinderConfigServiceInterface;
use Psr\Log\LoggerInterface;
use Shopware\Core\Content\Product\SalesChannel\Detail\AbstractProductDetailRoute;
use Shopware\Core\Content\Product\SalesChannel\Detail\ProductDetailRouteResponse;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

/**
 * Class FactFinderProductDetailRoute
 *
 * @package Elio\FactFinder\Core\Content\Product\SalesChannel\Detail
 */
class FactFinderProductDetailRoute extends AbstractProductDetailRoute
{
    private AbstractProductDetailRoute $decorated;
    private FactFinderConfigServiceInterface $configService;
    private RecordsApi $recordsApi;
    private LoggerInterface $logger;

    /**
     * FactFinderProductDetailRoute constructor.
     *
     * @param AbstractProductDetailRoute $decorated
     * @param FactFinderConfigServiceInterface $configService
     * @param RecordsApi $recordsApi
     * @param LoggerInterface $logger
     */
    public function __construct(
        AbstractProductDetailRoute $decorated,
        FactFinderConfigServiceInterface $configService,
        RecordsApi $recordsApi,
        LoggerInterface $logger
    ) {
        $this->decorated = $decorated;
        $this->configService = $configService;
        $this->recordsApi = $recordsApi;
        $this->logger = $logger;
    }

    public function getDecorated(): AbstractProductDetailRoute
    {
        return $this->decorated;
    }

    public function load(
        string $productId,
        Request $request,
        SalesChannelContext $context,
        Criteria $criteria
    ): ProductDetailRouteResponse {
        $config = $this->configService->getByContext($context);
        $productDetailResponse = $this->decorated->load($productId, $request, $context, $criteria);

        if (!$config->isActive()) {
            return $productDetailResponse;
        }

        try {
            $detailPageRequest = (new DetailPageRequest($config->getApiChannel()))
                ->setId($productDetailResponse->getProduct()->getProductNumber())
                ->setWithSimilarProducts('false')
                ->setWithRecommendations('false')
                ->setWithRecord('false');

            $responseCollection = $this->recordsApi->getDetailPage($detailPageRequest, $context);

            $productDetailResponse->getProduct()->addExtension(
                CampaignFeedbackResponseCollection::KEY,
                $responseCollection->get(CampaignFeedbackResponseCollection::KEY)
            );

            return $productDetailResponse;
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage());
            return $productDetailResponse;
        }
    }
}
