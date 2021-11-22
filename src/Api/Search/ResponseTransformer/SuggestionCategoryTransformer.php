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

namespace Elio\FactFinder\Api\Search\ResponseTransformer;

use Elio\FactFinder\Api\Request\ApiRequest;
use Elio\FactFinder\Api\Response\ResponseCollection;
use Elio\FactFinder\Api\Search\Response\SuggestionResponse;
use Elio\FactFinder\Api\Transform\ResponseTransformerInterface;
use Elio\FactFinder\Core\Exception\InvalidTypeException;
use Elio\FactFinder\Core\Suggest\SuggestGroup;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Elio\FactFinder\Core\Suggest\SuggestItem;
use Swagger\Client\Model\ModelInterface;
use Swagger\Client\Model\SuggestionResult;

/**
 * Enriches the category suggest group
 *
 * Class SuggestionCategoryTransformer
 * @package Elio\FactFinder\Api\Search\ResponseTransformer
 * @author Ralf Frommherz <ralf@frommherz.me>
 */
class SuggestionCategoryTransformer implements ResponseTransformerInterface
{
    private const TYPE = 'category';
    private const CATEGORY_ID_ATTRIBUTE = 'CategoryID';
    private const URL_ATTRIBUTE = 'ProductURL';

    private EntityRepositoryInterface $categoryRepository;

    /**
     * SuggestionTransformer constructor.
     * @param EntityRepositoryInterface $categoryRepository
     */
    public function __construct(EntityRepositoryInterface $categoryRepository) {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @inheritDoc
     */
    public function supports(ModelInterface $model, ApiRequest $request, SalesChannelContext $context): bool
    {
        return $model instanceof SuggestionResult;
    }

    /**
     * Enriches the product suggest result with product urls and product images
     *
     * @param ModelInterface $model
     * @param ResponseCollection $responseCollection
     * @param SalesChannelContext $context
     * @param ApiRequest $request
     */
    public function transform(
        ModelInterface $model,
        ResponseCollection $responseCollection,
        SalesChannelContext $context,
        ApiRequest $request
    ): void {
        if (!$model instanceof SuggestionResult) {
            throw new InvalidTypeException($model, SuggestionResult::class);
        }

        /** @var SuggestionResponse|null $suggestionResponse */
        $suggestionResponse = $responseCollection->get(SuggestionResponse::class) ?? new SuggestionResponse();
        if(!$suggestionResponse || !$suggestionResponse->hasGroup(self::TYPE)) {
            return;
        }

        $categoryGroup = $suggestionResponse->getGroup(self::TYPE);
        $categories = $this->collect($categoryGroup, $context->getContext());
        $this->enrich($categoryGroup, $categories);
    }

    /**
     * Collects all category entities
     *
     * @param SuggestGroup $group
     * @param Context $context
     * @return EntityCollection<CategoryEntity>
     */
    protected function collect(SuggestGroup $group, Context $context): EntityCollection
    {
        $categoryIds = [];
        foreach ($group->getItems() as $item) {
            if($productNumber = $this->getCategoryId($item)) {
                $categoryIds[] = $productNumber;
            }
        }

        if(empty($categoryIds)) {
            return new EntityCollection();
        }

        $criteria = new Criteria($categoryIds);
        $criteria->addAssociation('media');
        return $this->categoryRepository->search($criteria, $context);
    }

    /**
     * Adds the url and the image if present
     *
     * @param SuggestGroup $group
     * @param EntityCollection $categories
     */
    protected function enrich(SuggestGroup $group, EntityCollection $categories): void
    {
        foreach ($group->getItems() as $item) {
            // add url
            $attributes = $item->getAttributes();
            if(isset($attributes[self::URL_ATTRIBUTE])) {
                $item->setUrl($attributes[self::URL_ATTRIBUTE]);
            }

            // add image
            $categoryId = $this->getCategoryId($item);
            if(!$item->hasImage() && $categories->has($categoryId)) {
                $category = $categories->get($categoryId);

                if ($category->getMedia()) {
                    $item->setUrl($category->getMedia()->getUrl());
                }
            }
        }
    }

    /**
     * Extracts the product number by the given item
     *
     * @param SuggestItem $item
     * @return string|null
     */
    protected function getCategoryId(SuggestItem $item): ?string
    {
        $attributes = $item->getAttributes();
        return $attributes[self::CATEGORY_ID_ATTRIBUTE] ?? null;
    }
}