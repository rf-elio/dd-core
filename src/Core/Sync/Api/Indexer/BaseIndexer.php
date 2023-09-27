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

namespace Elio\ElioSearch\Core\Sync\Api\Indexer;

use Elio\ElioSearch\Core\Sync\Api\EntityStatusCollection;
use Elio\ElioSearch\Core\Sync\Api\EntityStatusEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\Common\RepositoryIterator;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Struct\Struct;

/**
 * Class BaseIndexer
 * @package Elio\ElioSearch\Core\Sync\Api\Indexer
 * @category Shopware
 * @author elio GmbH <support@elio-systems.com>
 * @author Danil Lukov <dl@elio-systems.com>
 * @copyright Copyright (c) 2023, elio GmbH (https://www.elio-systems.com)
 */
abstract class BaseIndexer implements IndexerInterface
{
    public function __construct(
        private readonly string $type,
        private readonly EntityRepository $repository
    ){
    }

    /**
     * Indexing entity states
     *
     * @param Context $context
     * @param EntityStatusCollection $entitiesStatus
     * @return EntityStatusCollection
     */
    public function index(Context $context, EntityStatusCollection $entitiesStatus): EntityStatusCollection
    {
        $criteria = $this->getCriteria($context);
        $iterator = new RepositoryIterator($this->repository, $context, $criteria);
        $indexingStatusCollection = new EntityStatusCollection();
        while ($entities = $iterator->fetch()) {
            foreach ($entities as $entity) {
                $hash = $this->hash($entity);
                /** @var EntityStatusEntity $entityStatus */
                if (!$entityStatus = $entitiesStatus->get($entity->getId())) {
                    $this->setCreated($this->type, $entity, $hash, $indexingStatusCollection);
                    continue;
                }

                if ($entityStatus->getHashedContent() !== $hash) {
                    $this->setUpdated($hash, $entityStatus, $indexingStatusCollection);
                }

                $entitiesStatus->remove($entity->getId());
            }
        }

        foreach ($entitiesStatus as $item) {
            $this->setDeleted($item, $indexingStatusCollection);
        }

        return $indexingStatusCollection;
    }

    /**
     * Gets criteria
     *
     * @param Context $context
     * @return Criteria
     */
    abstract protected function getCriteria(Context $context): Criteria;

    /**
     * Hash entity data
     *
     * @param Struct $struct
     * @return string
     */
    protected function hash(Struct $struct): string
    {
        return md5(serialize($struct));
    }

    /**
     * Adds created entity into collection
     *
     * @param string $type
     * @param Struct $entity
     * @param string $hash
     * @param EntityStatusCollection $indexingStatusCollection
     * @return void
     */
    protected function setCreated(
        string $type,
        Struct $entity,
        string $hash,
        EntityStatusCollection $indexingStatusCollection
    ): void {
        $entityStatus = new EntityStatusEntity();
        $entityStatus->setId($entity->getId());
        $entityStatus->setState(EntityStatusEntity::STATE_CREATED);
        $entityStatus->setType($type);
        $entityStatus->setHashedContent($hash);

        $indexingStatusCollection->add($entityStatus);
    }

    protected function setUpdated(
        string $hash,
        EntityStatusEntity $entityStatus,
        EntityStatusCollection $indexingStatusCollection
    ):void {
        $entityStatus->setState(EntityStatusEntity::STATE_UPDATED);
        $entityStatus->setHashedContent($hash);

        $indexingStatusCollection->add($entityStatus);
    }

    protected function setDeleted(
        EntityStatusEntity $entityStatus,
        EntityStatusCollection $indexingStatusCollection
    ): void {
        $entityStatus->setState(EntityStatusEntity::STATE_DELETED);
        $entityStatus->setDeletedAt(new \DateTimeImmutable());

        $indexingStatusCollection->add($entityStatus);
    }
}