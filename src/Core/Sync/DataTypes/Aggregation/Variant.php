<?php
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

namespace Elio\ElioSearch\Core\Sync\DataTypes\Aggregation;


use Elio\ElioSearch\Core\Sync\DataTypes\ProductDataType;

/**
 *
 */
class Variant
{
    private ?ProductDataType $parentProduct = null;
    private string $groupingKey;
    private bool $displayByDefault;
    private bool $displayByDefaultInListing;
    private bool $displayByDefaultInSearch;
    private int $position = 0;


    public function getGroupingKey(): string
    {
        return $this->groupingKey;
    }

    public function setGroupingKey(string $groupingKey)
    {
        $this->groupingKey = $groupingKey;
    }

    public function setDisplayByDefault(bool $displayByDefault): void
    {
        $this->displayByDefault = $displayByDefault;
    }

    public function getParentProduct(): ?ProductDataType
    {
        return $this->parentProduct;
    }

    public function setParentProduct(?ProductDataType $parentProduct): void
    {
        $this->parentProduct = $parentProduct;
    }

    public function isDisplayByDefaultInListing(): bool
    {
        return $this->displayByDefaultInListing;
    }

    public function setDisplayByDefaultInListing(bool $displayByDefaultInListing): void
    {
        $this->displayByDefaultInListing = $displayByDefaultInListing;
    }

    public function isDisplayByDefaultInSearch(): bool
    {
        return $this->displayByDefaultInSearch;
    }

    public function setDisplayByDefaultInSearch(bool $displayByDefaultInSearch): void
    {
        $this->displayByDefaultInSearch = $displayByDefaultInSearch;
    }

    public function isDisplayByDefault(): bool
    {
        return $this->displayByDefault;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }
}
