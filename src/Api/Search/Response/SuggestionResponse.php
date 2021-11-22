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

namespace Elio\FactFinder\Api\Search\Response;

use Elio\FactFinder\Api\Response\Response;
use Elio\FactFinder\Core\Suggest\SuggestGroup;

/**
 * Class SuggestionResponse
 * @package Elio\FactFinder\Api\Search\Response
 * @category Shopware
 * @author elio GmbH <support@elio-systems.com>
 * @author Andrey Baev <anb@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class SuggestionResponse extends Response
{
    /**
     * @var SuggestGroup[]
     */
    protected array $groups = [];

    /**
     * Checks if the suggest is empty
     *
     * @return bool
     */
    public function isEmpty() : bool
    {
        return empty($this->groups);
    }

    /**
     * @param SuggestGroup[] $groups
     */
    public function setGroups(array $groups): void
    {
        $this->groups = $groups;
    }

    /**
     * @return SuggestGroup[]
     */
    public function getGroups(): array
    {
        return $this->groups;
    }

    /**
     * @param string $identifier
     * @return SuggestGroup
     */
    public function getGroup(string $identifier) : SuggestGroup
    {
        return $this->groups[$identifier];
    }

    /**
     * @param string $identifier
     * @return bool
     */
    public function hasGroup(string $identifier) : bool
    {
        return isset($this->groups[$identifier]);
    }

    /**
     * @return int
     */
    public function count() : int
    {
        return array_sum(array_map(static function (SuggestGroup $group) {
            return $group->count();
        }, $this->groups));
    }
}