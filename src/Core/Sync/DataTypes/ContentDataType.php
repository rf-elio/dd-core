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

namespace Elio\ElioSearch\Core\Sync\DataTypes;

use DateTimeInterface;
use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Content\Seo\SeoUrl\SeoUrlCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\Framework\Struct\Struct;
use Shopware\Core\System\Tag\TagCollection;

/**
 * Class ContentType
 * @package Elio\ElioSearch\Core\Sync\DataTypes
 * @category Shopware
 * @author elio GmbH <support@elio-systems.com>
 * @author Danil Lukov <dl@elio-systems.com>
 * @copyright Copyright (c) 2023, elio GmbH (https://www.elio-systems.com)
 */
class ContentDataType extends Struct implements DataTypeInterface
{
    use EntityCustomFieldsTrait;
    use EntityIdTrait;
    use IdentifierAware;
    use DeletedAware;
    use TranslationAware;

    private ?string $name = null;
    private ?string $type = null;
    private ?string $description = null;
    private ?string $title = null;
    private ?string $seoText = null;
    private ?SeoUrlCollection $seoUrls = null;
    private ?string $keywords = null;
    private ?MediaEntity $media = null;
    private ?DateTimeInterface $createdAt = null;
    private ?array $breadcrumb = null;
    private ?TagCollection $tags = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getSeoText(): ?string
    {
        return $this->seoText;
    }

    public function setSeoText(?string $seoText): void
    {
        $this->seoText = $seoText;
    }

    public function getKeywords(): ?string
    {
        return $this->keywords;
    }

    public function setKeywords(?string $keywords): void
    {
        $this->keywords = $keywords;
    }

    public function getMedia(): ?MediaEntity
    {
        return $this->media;
    }

    public function setMedia(?MediaEntity $media): void
    {
        $this->media = $media;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?DateTimeInterface $dateTime): void
    {
        $this->createdAt = $dateTime;
    }

    public function getSeoUrls(): ?SeoUrlCollection
    {
        return $this->seoUrls;
    }

    public function setSeoUrls(?SeoUrlCollection $seoUrls): void
    {
        $this->seoUrls = $seoUrls;
    }

    public function getBreadcrumb(): ?array
    {
        return $this->breadcrumb;
    }

    public function setBreadcrumb(?array $breadcrumb): void
    {
        $this->breadcrumb = $breadcrumb;
    }

    public function getTags(): ?TagCollection
    {
        return $this->tags;
    }

    public function setTags(?TagCollection $tags): void
    {
        $this->tags = $tags;
    }
}