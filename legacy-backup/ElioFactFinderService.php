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

namespace Elio\FactFinder\Components;

use Elio\FactFinder\Components\Helper\FactFinderHelper;
use Shopware\Core\Framework\Context;
use Elio\FactFinder\Service\FactFinderConfigurationInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Psr\Log\LoggerInterface;

/**
 * @todo: keep what we need and remove this file
 * Class ElioFactFinderService
 *
 * @category  Service Component
 * @package   Shopware\Plugins\FactFinder\Components
 * @author    Raoul Yemetio <ry@elio-systems.com>
 * @copyright Copyright (c) 2020, elio GmbH (http://www.elio-systems.com)
 */
class ElioFactFinderService
{
    /**
     * @var FactFinderConfigurationInterface
     */
    private $ffConfig;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var array
     */
    private $params = [
        'query' => []
    ];

    /**
     * @var Context
     */
    private $context;

    /**
     * @var FactFinderHelper
     */
    private $ffHelper;

    /**
     * @var EntityRepositoryInterface
     */
    private $productManufacturerRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $propertyGroupOptionRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        FactFinderConfigurationInterface $ffConfig,
        FactFinderHelper $ffHelper,
        EntityRepositoryInterface $productManufacturerRepository,
        EntityRepositoryInterface $propertyGroupOptionRepository,
        LoggerInterface $logger
    )
    {
        $this->ffConfig = $ffConfig;
        $this->client = new Client();
        $this->setBaseRequestParams();
        $this->context = Context::createDefaultContext();
        $this->ffHelper = $ffHelper;
        $this->productManufacturerRepository = $productManufacturerRepository;
        $this->propertyGroupOptionRepository = $propertyGroupOptionRepository;
        $this->logger = $logger;
    }


    private function setBaseRequestParams(): void
    {
        $this->upsertRequestParam('username', $this->ffConfig->getUserName());
        $this->upsertRequestParam('password', $this->getHashedPassword());
        $this->upsertRequestParam('channel', $this->ffConfig->getChannel());
        $this->upsertRequestParam('version', $this->ffConfig->getVersion());
        $this->upsertRequestParam('format', 'json');
    }

    /**
     * @return string
     */
    private function getHashedPassword(): string
    {
        $hashedPW = "";

        switch ($this->ffConfig->getAuthenticationType()) {
            case FactFinderConfigurationInterface::AUTHENTICATION_ADVANCED:
                $timestamp = time() . '000'; //milliseconds needed;
                $this->upsertRequestParam('timestamp', $timestamp);
                $hashedPW = md5($this->ffConfig->getAuthenticationPrefix()
                    . $timestamp
                    . md5($this->ffConfig->getPassword())
                    . $this->ffConfig->getAuthenticationPostfix()
                );
                break;
            case FactFinderConfigurationInterface::AUTHENTICATION_SIMPLE:
                $hashedPW = md5($this->ffConfig->getPassword());
                break;
        }
        return $hashedPW;
    }

    /**
     * @param bool $context
     * @return string
     */
    public function getBaseUri(bool $context = true): string
    {
        $baseUri = $this->ffConfig->getRequestProtocol() . '://'
            . $this->ffConfig->getServerAddress() . ':'
            . $this->ffConfig->getServerPort();

        if ($context)
            $baseUri = $baseUri . '/' . $this->ffConfig->getContext();

        return $baseUri;
    }

    /**
     * @return FactFinderConfigurationInterface
     */
    public function getConfig(): FactFinderConfigurationInterface
    {
        return $this->ffConfig;
    }

    /**
     * Update request parameter when exist or insert it when it does not exist.
     *
     * @param string $key
     * @param $value
     */
    public function upsertRequestParam(string $key, $value): void
    {
        foreach ($this->params as $param) {
            $param[$key] = $value;
        }
        $this->params['query'] = $param;
    }

    /**
     * @param array $params Associative array of params
     */
    public function addRequestParams(array $params): void
    {
        if (count($params) > 0) {
            foreach ($params as $key => $value) {
                $this->upsertRequestParam($key, $value);
            }
        }
    }

    /**
     * @return array
     */
    public function getRequestParams(): array
    {
        return $this->params;
    }

    /**
     * @param array $params
     */
    public function setRequestParams(array $params): void
    {
        $this->params = $params;
    }

    public function resetRequestParams(): void
    {
        $this->params = [
            'query' => []
        ];
    }

    /**
     * @param string $key
     */
    public function removeRequestParam(string $key): void
    {
        unset($this->params['query'][$key]);
    }

    /**
     * @param string $key
     * @return string
     */
    public function getRequestParam(string $key): string
    {
        $value = "";

        if ($this->requestParamExists($key))
            $value = $this->params['query'][$key];

        return $value;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function requestParamExists(string $key): bool
    {
        return array_key_exists($key, $this->params['query']);
    }

    /**
     * @param string $query
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getSuggestions(string $query): array
    {
        $this->upsertRequestParam('query', $query);

        $request = $this->client->request(
            'GET',
            $this->getBaseUri() . '/Suggest.ff',
            $this->getRequestParams()
        );

        return json_decode($request->getBody()->getContents(), true)['suggestions'];
    }

    /**
     * @param string|null $query
     * @param string|null $searchParams
     * @param bool $queryFromSuggest
     * @return array
     */
    public function search(?string $query, ?string $searchParams = null, bool $queryFromSuggest = false): array
    {
        if (empty($searchParams)) {

            if ($queryFromSuggest)
                $this->trackSuggest($query);

            $this->upsertRequestParam('query', $query);
            $uri = $this->getBaseUri() . '/Search.ff';
            $params = $this->getRequestParams();

        } else {
            $uri = $this->getBaseUri(false). $searchParams . $this->getMissingParams($queryFromSuggest, $query);
            $params = [];
        }

        $request = $this->client->request(
            'GET',
            $uri,
            $params
        );
        #dd($params);

        return json_decode($request->getBody()->getContents(), true)['searchResult'];
    }

    private function getMissingParams(bool $queryFromSuggest = false, string $userInput = ""): string
    {
        $params = "";
        $currentParams = $this->getRequestParams();

        $this->resetRequestParams();

        $this->upsertRequestParam('username', $this->ffConfig->getUserName());
        $this->upsertRequestParam('password', $this->getHashedPassword());

        if ($queryFromSuggest)
            $this->trackSuggest($userInput);

        foreach ($this->getRequestParams() as $requestParam) {
            foreach ($requestParam as $key => $value) {
                $params .= "&$key=$value";
            }
        }

        $this->setRequestParams($currentParams);

        return $params;
    }

    private function trackSuggest(string $userInput):void
    {
        $this->upsertRequestParam('queryFromSuggest', true);
        $this->upsertRequestParam('userInput', $userInput);
    }

    /**
     * @param array $manufacturerIds
     * @throws \Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException
     */
    public function setManufacturerFilter(array $manufacturerIds): void
    {
        if (count($manufacturerIds) > 0) {

            $elements = [];

            $entities = $this->productManufacturerRepository->search(
                new Criteria($manufacturerIds),
                $this->context
            )->getEntities();

            foreach ($entities->getElements() as $manufacturer) {
                $elements[] = $manufacturer->getTranslation('name');
            }

            $manufacturers = $this->ffHelper->concatenateElements(
                FactFinderConfigurationInterface:: OR,
                $elements
            );

            $this->upsertRequestParam(
                FactFinderConfigurationInterface::PREFIX_FILTER .
                FactFinderConfigurationInterface::FILTER_MANUFACTURER,
                $manufacturers
            );
        }
    }

    /**
     * @param array $propertyIds
     * @throws \Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException
     */
    public function setPropertyFilter(array $propertyIds): void
    {
        if (count($propertyIds) > 0) {

            $criteria = new Criteria($propertyIds);
            $criteria->addAssociation('group');

            $entities = $this->propertyGroupOptionRepository->search(
                $criteria,
                $this->context
            )->getEntities();


            foreach ($entities->getElements() as $property) {
                $filterName = FactFinderConfigurationInterface::PREFIX_FILTER . $property
                        ->getGroup()
                        ->getTranslation('name');

                if ($this->requestParamExists($filterName)) {
                    $oldFilterValue = $this->getRequestParam($filterName);
                    $this->removeRequestParam($filterName);

                    $this->upsertRequestParam(
                        $filterName,
                        $oldFilterValue . FactFinderConfigurationInterface:: OR . $property->getTranslation('name')
                    );
                } else {
                    $this->upsertRequestParam(
                        $filterName,
                        $property->getTranslation('name')
                    );
                }
            }
        }
    }

    /**
     * @param $rating
     */
    public function setRatingFilter($rating): void
    {
        if ($rating) {
            $this->upsertRequestParam(
                FactFinderConfigurationInterface::PREFIX_FILTER . FactFinderConfigurationInterface::FILTER_RATING,
                FactFinderConfigurationInterface::GTE . $rating
            );
        }
    }

    /**
     * @param $min
     * @param $max
     */
    public function setPriceFilter($min, $max): void
    {
        $range = "";

        if (!$min && !$max) {
            return;
        }

        if ($min > 0) {
            $range .= FactFinderConfigurationInterface::GTE . $min;
        }
        if ($max > 0) {
            if ($min > 0) {
                $range .= FactFinderConfigurationInterface:: AND;
            }
            $range .= FactFinderConfigurationInterface::LTE . $max;
        }

        $this->upsertRequestParam(
            FactFinderConfigurationInterface::PREFIX_FILTER . FactFinderConfigurationInterface::FILTER_PRICE,
            $range
        );

    }

    public function setShippingFreeFilter($shippingFree): void
    {
        if ($shippingFree) {
            $this->upsertRequestParam(
                FactFinderConfigurationInterface::PREFIX_FILTER . FactFinderConfigurationInterface::FILTER_SHIPPING_FREE,
                $shippingFree
            );
        }
    }

    public function doTrack(string $eventName, ?string $sessionId, array $extraParams)
    {
        $this->upsertRequestParam('event', $eventName);

        if (!empty($sessionId)){
            $this->upsertRequestParam('sid', $sessionId);
        }else{
            $this->upsertRequestParam('sid', session_id());
        }

        $this->addRequestParams($extraParams);

        $promises = [];

        $promises[] = $this->client->requestAsync(
            'GET',
            $this->getBaseUri() . '/Tracking.ff',
            $this->getRequestParams()
        )
            ->then(function ($response) use ($eventName) {
                if ($response->getStatusCode() != 200){
                    $this->logger->error(
                        sprintf(
                            'Tracking(%s) ended with status code %s: %s',
                            $eventName,
                            $response->getStatusCode(),
                            $response->getBody()
                        )
                    );
                }
            });

        return Promise\unwrap($promises);
    }



}
