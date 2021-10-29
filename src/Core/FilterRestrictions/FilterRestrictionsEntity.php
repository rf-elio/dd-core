<?php
/**
 * Copyright (c) 2021, elio GmbH.
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

namespace Elio\FactFinder\Core\FilterRestrictions;

use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

/**
 * Class FilterRestrictionsEntity
 * @package Elio\FactFinder\Core\FilterRestrictions
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Andrey Baev <anb@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class FilterRestrictionsEntity extends Entity
{
    use EntityIdTrait;

    /**
     * @var bool
     */
    protected bool $isCategory;
    /**
     * @var string
     */
    protected string $layer;
    /**
     * @var CategoryEntity|null
     */
    protected $category;
    /**
     * @var string
     */
    protected $salesChannelId;
    /**
     * @var bool
     */
    protected bool $isAllowed;
    /**
     * @var FilterCollection|null
     */
    protected $filters;
    /**
     * @var bool
     */
    protected bool $isAllChecked;

    /**
     * @return FilterCollection|null
     */
    public function getFilters(): ?FilterCollection
    {
        return $this->filters;
    }

    /**
     * @param FilterCollection|null $filters
     */
    public function setFilters(?FilterCollection $filters): void
    {
        $this->filters = $filters;
    }

    /**
     * @return bool
     */
    public function isAllowed(): bool
    {
        return $this->isAllowed;
    }

    /**
     * @param bool $isAllowed
     */
    public function setIsAllowed(bool $isAllowed): void
    {
        $this->isAllowed = $isAllowed;
    }

    /**
     * @return CategoryEntity|null
     */
    public function getCategory(): ?CategoryEntity
    {
        return $this->category;
    }

    /**
     * @param CategoryEntity|null $category
     */
    public function setCategory(?CategoryEntity $category): void
    {
        $this->category = $category;
    }

    /**
     * @return string
     */
    public function getLayer(): string
    {
        return $this->layer;
    }

    /**
     * @param string $layer
     */
    public function setLayer(string $layer): void
    {
        $this->layer = $layer;
    }

    /**
     * @return bool
     */
    public function isCategory(): bool
    {
        return $this->isCategory;
    }

    /**
     * @param bool $isCategory
     */
    public function setIsCategory(bool $isCategory): void
    {
        $this->isCategory = $isCategory;
    }

    /**
     * @return bool
     */
    public function isAllChecked(): bool
    {
        return $this->isAllChecked;
    }

    /**
     * @param bool $isAllChecked
     */
    public function setIsAllChecked(bool $isAllChecked): void
    {
        $this->isAllChecked = $isAllChecked;
    }

    /**
     * @return string
     */
    public function getSalesChannelId(): string
    {
        return $this->salesChannelId;
    }

    /**
     * @param string $salesChannelId
     */
    public function setSalesChannelId(string $salesChannelId): void
    {
        $this->salesChannelId = $salesChannelId;
    }
}