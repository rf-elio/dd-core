<?php declare(strict_types=1);

namespace Elio\ElioDataDiscovery\Core\Configuration\Api;

use Elio\ElioDataDiscovery\Api\Configuration\ConfigurationAdapter;
use Elio\ElioDataDiscovery\Api\Configuration\Request\ConfigurationRequest;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\EntityNotFoundException;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\Context\AbstractSalesChannelContextFactory;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(defaults: ['_routeScope' => ['api']])]
class ConfigurationController extends AbstractController
{
    public function __construct(
        private readonly ConfigurationAdapter $configurationAdapter,
        private readonly EntityRepository $salesChannelRepository,
        private readonly AbstractSalesChannelContextFactory $salesChannelContextFactory,
    ) {}

    #[Route(path: '/api/_action/elio-data-discovery/configuration/{type}', name: 'api.custom.elio_data_discovery.configuration.get', methods: ['GET'])]
    public function getConfiguration(string $type, Request $request, Context $context): Response
    {
        /** @var SalesChannelEntity $salesChannel */
        $salesChannel = $this->salesChannelRepository->search(new Criteria(), $context)->first();
        if (!$salesChannel) {
            throw new EntityNotFoundException(SalesChannelEntity::class, 'first');
        }
        $salesChannelContext = $this->salesChannelContextFactory->create('', $salesChannel->getId());

        $configurationRequest = new ConfigurationRequest('');
        $configurationRequest->setType($type);
        $configurationRequest->setSearchTerm($request->query->has('searchTerm') ? $request->query->getString('searchTerm') : null);
        $configurationRequest->setOffset($request->query->has('offset') ? $request->query->getInt('offset') : null);
        $configurationRequest->setLimit($request->query->has('limit') ? $request->query->getInt('limit') : null);

        $response = $this->configurationAdapter->getConfig($configurationRequest, $salesChannelContext);
        return new JsonResponse($response->getConfigurationResponseByType($type));
    }
}
