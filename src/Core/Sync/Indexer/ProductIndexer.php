<?php declare(strict_types=1);
/**
 * Copyright (c) 2023, elio GmbH.
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

namespace Elio\ElioSearch\Core\Sync\Indexer;

use Elio\ElioSearch\Core\Sync\Api\EntityStatusCollection;
use Elio\ElioSearch\Core\Sync\Api\EntityStatusEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\Common\RepositoryIterator;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

class ProductIndexer
{
    public const TYPE = 'product';

    public function __construct(private EntityRepository $productRepository)
    {
    }

    public function index(string $syncProfileId, Context $context, EntityStatusCollection $entitiesStatus): EntityStatusCollection
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('active', true));
        $iterator = new RepositoryIterator($this->productRepository, $context, $criteria);
        $indexingStatusCollection = new EntityStatusCollection();
        while ($products = $iterator->fetch()) {
            /** @var ProductEntity $product */
            foreach ($products as $product) {
                $hash = $this->hash($product);
                /** @var EntityStatusEntity $entityStatus */
                if (!$entityStatus = $entitiesStatus->get($product->getId())) {
                    $this->addEntityStatus($syncProfileId, $product, $hash, $indexingStatusCollection);
                    continue;
                }

                if ($entityStatus->getHashedContent() !== $hash) {
                    $this->updateEntityStatus($hash, $entityStatus, $indexingStatusCollection);
                }

                $entitiesStatus->remove($product->getId());
            }
        }

        foreach ($entitiesStatus as $item) {
            $this->deleteEntityStatus($item, $indexingStatusCollection);
        }

        return $indexingStatusCollection;
    }

    protected function hash(ProductEntity $product): string
    {
        // TODO: hash required product data, maybe depend on some model
    }

    protected function addEntityStatus(
        string $syncProfileId,
        ProductEntity $product,
        string $hash,
        EntityStatusCollection $indexingStatusCollection
    ): void {
        $entityStatus = new EntityStatusEntity();
        $entityStatus->setId($product->getId());
        $entityStatus->setSyncProfileId($syncProfileId);
        $entityStatus->setType(self::TYPE);
        $entityStatus->setState('open'); // state doesn't need
        $entityStatus->setHashedContent($hash);

        $indexingStatusCollection->add($entityStatus);
    }

    protected function updateEntityStatus(
        string $hash,
        EntityStatusEntity $entityStatus,
        EntityStatusCollection $indexingStatusCollection
    ):void {
        $entityStatus->setHashedContent($hash);
        $indexingStatusCollection->add($entityStatus);
    }

    protected function deleteEntityStatus(
        EntityStatusEntity $entityStatus,
        EntityStatusCollection $indexingStatusCollection
    ): void {
        $entityStatus->setDeletedAt(new \DateTimeImmutable());
        $indexingStatusCollection->add($entityStatus);
    }
}