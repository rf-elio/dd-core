<?php
/**
 * Copyright (c) 2022, elio GmbH.
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

namespace Elio\ElioSearch\Core\Ranking;


use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Elio\ElioSearch\Configuration\Configuration;
use Elio\ElioSearch\Configuration\ElioSearchConfigServiceInterface;
use Elio\ElioSearch\ElioSearch;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Uuid\Uuid;

/**
 * Class ProductRankingUpdateService
 * @package Elio\ElioSearch\Core\Ranking
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2022, elio GmbH (https://www.elio-systems.com)
 */
class ProductRankingUpdateService
{
    private ElioSearchConfigServiceInterface $elioSearchConfigService;
    private EntityRepository $salesChannelRepository;
    private Connection $connection;

    /**
     * @param ElioSearchConfigServiceInterface $elioSearchConfigService
     * @param EntityRepository $salesChannelRepository
     * @param Connection $connection
     */
    public function __construct(
        ElioSearchConfigServiceInterface $elioSearchConfigService,
        EntityRepository $salesChannelRepository,
        Connection $connection
    )
    {
        $this->elioSearchConfigService = $elioSearchConfigService;
        $this->salesChannelRepository = $salesChannelRepository;
        $this->connection = $connection;
    }

    /**
     * Updates the product ranking values
     *
     * @param Context $context
     * @return void
     * @throws Exception
     */
    public function updateProductRanking(Context $context): void
    {
        foreach ($this->getSalesChannelIds($context) as $salesChannelId) {
            $config = $this->elioSearchConfigService->get($salesChannelId);
            if (!$config->isProductRankingActive()) {
                continue;
            }

            $this->updateProductData(
                ElioSearch::CUSTOM_FIELD_RANKING_PRODUCT_ORDER_AMOUNT,
                'oli.total_price',
                $config
            );

            $this->updateProductData(
                ElioSearch::CUSTOM_FIELD_RANKING_PRODUCT_ORDER_COUNT,
                'oli.quantity',
                $config
            );

            break;
        }
    }

    /**
     * Fetches all sales channels for that we load the update configuration.
     *
     * @param Context $context
     * @return array
     */
    protected function getSalesChannelIds(Context $context): array
    {
        $criteria = new Criteria();
        return $this->salesChannelRepository->searchIds($criteria, $context)->getIds();
    }

    /**
     * Updates the product data for the given target field based on the orders using the given select.
     * @note Don't try to use the dal for this query, you will suffer on performance issued!
     *
     * @param string $targetField
     * @param Configuration $config
     * @return void
     * @throws Exception
     */
    protected function updateProductData(string $targetField, string $field, Configuration $config): void
    {
        $additionalConditions = [];
        $types = [];
        $parameters = [
            'lineItemType' => LineItem::PRODUCT_LINE_ITEM_TYPE,
            'defaultLanguageId' => Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM),
            'liveVersionId' => Uuid::fromHexToBytes(Defaults::LIVE_VERSION),
            'maxOrderAge' => $config->getProductRankingMaxOrderAge()
        ];

        // order states
        $allowedOrderStates = $config->getProductRankingOrderStates();
        $allowedOrderStates = array_map([Uuid::class, 'fromHexToBytes'], $allowedOrderStates);
        if (!empty($allowedOrderStates)) {
            $additionalConditions[] = 'o.state_id IN (:orderStates)';
            $parameters['orderStates'] = $allowedOrderStates;
            $types['orderStates'] = Connection::PARAM_STR_ARRAY;
        }

        // delivery states
        $allowedOrderDeliveryStates = $config->getProductRankingOrderDeliveryStates();
        $allowedOrderDeliveryStates = array_map([Uuid::class, 'fromHexToBytes'], $allowedOrderDeliveryStates);

        if (!empty($allowedOrderDeliveryStates)) {
            $additionalConditions[] = '(SELECT od.state_id FROM order_delivery od WHERE od.order_id = o.id AND od.order_version_id = o.version_id ORDER BY od.created_at LIMIT 1) IN (:allowedOrderDeliveryState)';
            $parameters['allowedOrderDeliveryState'] = $allowedOrderDeliveryStates;
            $types['allowedOrderDeliveryState'] = Connection::PARAM_STR_ARRAY;
        }

        $additionalConditions = empty($additionalConditions) ? '' : 'AND '.implode(' AND ', $additionalConditions);

        $maxValueFromAllOrders = $this->getMaxValueFromAllOrders($field, $parameters);

        if ($maxValueFromAllOrders <= 0) {
            return;
        }

        // set rounded percentage value
        $sql = 'UPDATE `product_translation` pt
                SET custom_fields = JSON_SET(custom_fields, "$.'.$targetField.'", IFNULL((
                    SELECT ROUND(SUM('.$field.') / '.$maxValueFromAllOrders.'  * 100)
                    FROM `order_line_item` oli
                    INNER JOIN `order` o ON o.id = oli.order_id AND o.version_id = oli.order_version_id
                    WHERE
                        o.version_id = :liveVersionId AND
                        o.order_date >= DATE_SUB(NOW(), INTERVAL :maxOrderAge DAY) AND
                        oli.type = :lineItemType AND
                        oli.product_id = pt.product_id AND
                        oli.product_version_id = pt.product_version_id
                        '.$additionalConditions.'
                    GROUP BY oli.product_id
                ), 0))
                WHERE pt.language_id = :defaultLanguageId;';

        $this->connection->executeStatement($sql, $parameters, $types);
    }

    protected function getMaxValueFromAllOrders(string $field, array $parameters): int
    {
        $sql = 'SELECT MAX(summary)
                FROM (
                    SELECT ROUND(SUM('.$field.')) as summary
                        FROM `order_line_item` oli
                        INNER JOIN `order` o ON o.id = oli.order_id AND o.version_id = oli.order_version_id
                    WHERE
                        o.version_id = :liveVersionId AND
                        o.order_date >= DATE_SUB(NOW(), INTERVAL :maxOrderAge DAY) AND
                        oli.type = :lineItemType
                    GROUP BY oli.product_id
                ) summary
        ';
        return (int)$this->connection->executeQuery($sql, $parameters)->fetchOne();
    }
}
