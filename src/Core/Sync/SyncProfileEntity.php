<?php declare(strict_types=1);
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

namespace Elio\ElioSearch\Core\Sync;


use DateTimeInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\System\Language\LanguageCollection;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;

/**
 * Class SyncProfileEntity
 * @package Elio\ElioSearch\Core\Sync
 * @category Shopware
 * @author elio GmbH <support@elio-systems.com>
 * @author Danil Lukov <dl@elio-systems.com>
 * @copyright Copyright (c) 2023, elio GmbH (https://www.elio-systems.com)
 */
class SyncProfileEntity extends Entity
{
    use EntityIdTrait;

    protected string $name;
    protected bool $active;
    protected string $profile;
    protected string $dataType;
    protected string $interval;
    protected array $mapping = [];
    protected array $config = [];
    protected ?DateTimeInterface $lastGenerationStartedAt = null;
    protected ?DateTimeInterface $lastGenerationFinishedAt = null;
    protected ?DateTimeInterface $nextGenerationDueAt = null;
    protected string $salesChannelId;
    protected ?SalesChannelEntity $salesChannel = null;
    protected ?LanguageCollection $languages = null;
    protected $baseCategoryIds;
    protected $downloadUsername;
    protected $downloadPassword;

    /**
     * @return mixed
     */
    public function getDownloadUsername(): mixed
    {
        return $this->downloadUsername;
    }

    /**
     * @param mixed $downloadUsername
     */
    public function setDownloadUsername(mixed $downloadUsername): void
    {
        $this->downloadUsername = $downloadUsername;
    }

    /**
     * @return mixed
     */
    public function getDownloadPassword(): mixed
    {
        return $this->downloadPassword;
    }

    /**
     * @param mixed $downloadPassword
     */
    public function setDownloadPassword(mixed $downloadPassword): void
    {
        $this->downloadPassword = $downloadPassword;
    }

    /**
     * Returns an identifier that is unique for every channel and language
     *
     * @return string
     */
    public function getIdentifier() : string
    {
        return $this->salesChannelId.'-'.$this->languageId;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    /**
     * @return string
     */
    public function getProfile(): string
    {
        return $this->profile;
    }

    /**
     * @param string $profile
     */
    public function setProfile(string $profile): void
    {
        $this->profile = $profile;
    }

    public function getDataType(): string
    {
        return $this->dataType;
    }

    public function setDataType(string $dataType): void
    {
        $this->dataType = $dataType;
    }

    /**
     * @return string
     */
    public function getInterval(): string
    {
        return $this->interval;
    }

    /**
     * @param string $interval
     */
    public function setInterval(string $interval): void
    {
        $this->interval = $interval;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getLastGenerationStartedAt(): ?DateTimeInterface
    {
        return $this->lastGenerationStartedAt;
    }

    /**
     * @param DateTimeInterface|null $lastGenerationStartedAt
     */
    public function setLastGenerationStartedAt(?DateTimeInterface $lastGenerationStartedAt): void
    {
        $this->lastGenerationStartedAt = $lastGenerationStartedAt;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getLastGenerationFinishedAt(): ?DateTimeInterface
    {
        return $this->lastGenerationFinishedAt;
    }

    /**
     * @param DateTimeInterface|null $lastGenerationFinishedAt
     */
    public function setLastGenerationFinishedAt(?DateTimeInterface $lastGenerationFinishedAt): void
    {
        $this->lastGenerationFinishedAt = $lastGenerationFinishedAt;
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
     * @return LanguageCollection|null
     */
    public function getLanguages(): ?LanguageCollection
    {
        return $this->languages;
    }

    /**
     * @param LanguageCollection $languages
     */
    public function setLanguages(LanguageCollection $languages): void
    {
        $this->languages = $languages;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getNextGenerationDueAt(): ?DateTimeInterface
    {
        return $this->nextGenerationDueAt;
    }

    /**
     * @param DateTimeInterface|null $nextGenerationDueAt
     */
    public function setNextGenerationDueAt(?DateTimeInterface $nextGenerationDueAt): void
    {
        $this->nextGenerationDueAt = $nextGenerationDueAt;
    }

    /**
     * @return mixed
     */
    public function getBaseCategoryIds(): mixed
    {
        return $this->baseCategoryIds;
    }

    /**
     * @param mixed $baseCategoryIds
     */
    public function setBaseCategoryIds(mixed $baseCategoryIds): void
    {
        $this->baseCategoryIds = $baseCategoryIds;
    }

    /**
     * @return array
     */
    public function getMapping(): array
    {
        return $this->mapping;
    }

    /**
     * @param mixed $mapping
     */
    public function setMapping(mixed $mapping): void
    {
        $this->mapping = $mapping;
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @param mixed $config
     */
    public function setConfig(mixed $config): void
    {
        $this->config = $config;
    }
}
