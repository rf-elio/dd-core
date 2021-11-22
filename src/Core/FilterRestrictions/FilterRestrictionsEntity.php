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
use Shopware\Core\System\SalesChannel\SalesChannelEntity;

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
     * @var string|null
     */
    protected string $categoryId;
    /**
     * @var CategoryEntity|null
     */
    protected $category;
    /**
     * @var string|null
     */
    protected $salesChannelId;

    /**
     * @var SalesChannelEntity|null
     */
    protected $salesChannel;
    /**
     * is it collection of filters for allowed or blocked column
     * @var bool
     */
    protected bool $isAllowed;
    /**
     * is it inherited from all-saleschannel restriction
     * @var bool
     */
    protected bool $isInherited;
    /**
     * is all-option is checked or only selected (if false)
     * @var bool
     */
    protected bool $isAllChecked;
    /**
     * @var FilterCollection|null
     */
    protected $filters;

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
     * @return bool
     */
    public function isInherited(): bool
    {
        return $this->isInherited;
    }

    /**
     * @param bool $isInherited
     */
    public function setIsInherited(bool $isInherited): void
    {
        $this->isInherited = $isInherited;
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
     * @return string|null
     */
    public function getSalesChannelId(): ?string
    {
        return $this->salesChannelId;
    }

    /**
     * @param string|null $salesChannelId
     */
    public function setSalesChannelId(?string $salesChannelId): void
    {
        $this->salesChannelId = $salesChannelId;
    }

    /**
     * @return SalesChannelEntity|null
     */
    public function getSalesChannel(): ?SalesChannelEntity
    {
        return $this->salesChannel;
    }

    /**
     * @param SalesChannelEntity|null $salesChannel
     */
    public function setSalesChannel(?SalesChannelEntity $salesChannel): void
    {
        $this->salesChannel = $salesChannel;
    }

    /**
     * @return string|null
     */
    public function getCategoryId(): ?string
    {
        return $this->categoryId;
    }

    /**
     * @param string|null $categoryId
     */
    public function setCategoryId(?string $categoryId): void
    {
        $this->categoryId = $categoryId;
    }
}