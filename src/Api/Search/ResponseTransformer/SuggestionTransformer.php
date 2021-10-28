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

use Elio\FactFinder\Api\Response\ResponseCollection;
use Elio\FactFinder\Api\Search\Response\SuggestionResponse;
use Elio\FactFinder\Api\Transform\ResponseTransformerInterface;
use Elio\FactFinder\Core\Exception\InvalidTypeException;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Elio\FactFinder\Core\Suggest\SuggestItem;
use Swagger\Client\Model\ModelInterface;
use Swagger\Client\Model\ResultSuggestion;
use Swagger\Client\Model\SuggestionResult;
use Throwable;

/**
 * Class SuggestionTransformer
 * @package Elio\FactFinder\Api\Search\ResponseTransformer
 * @category Shopware
 * @author elio GmbH <support@elio-systems.com>
 * @author Andrey Baev <anb@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class SuggestionTransformer implements ResponseTransformerInterface
{
    protected const TYPE_PRODUCT = 'productName';
    protected const TYPE_CATEGORY = 'category';

    private EntityRepositoryInterface $categoryRepository;
    private EntityRepositoryInterface $productRepository;
    private SalesChannelContext $context;

    /**
     * SuggestionTransformer constructor.
     * @param EntityRepositoryInterface $productRepository
     * @param EntityRepositoryInterface $categoryRepository
     */
    public function __construct(
        EntityRepositoryInterface $productRepository,
        EntityRepositoryInterface $categoryRepository
    ) {
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @inheritDoc
     */
    public function supports(ModelInterface $model, SalesChannelContext $context): bool
    {
        return $model instanceof SuggestionResult;
    }

    /**
     * @param ModelInterface $model
     * @param ResponseCollection $responseCollection
     * @param SalesChannelContext $context
     */
    public function transform(
        ModelInterface $model,
        ResponseCollection $responseCollection,
        SalesChannelContext $context
    ): void {
        if (!$model instanceof SuggestionResult) {
            throw new InvalidTypeException($model, SuggestionResult::class);
        }
        $this->context = $context;

        /** @var SuggestionResponse $listing */
        $listing = $responseCollection->get(SuggestionResponse::class) ?? new SuggestionResponse();
        $responseCollection->set(SuggestionResponse::class, $listing);

        $suggestions = [];
        foreach ($model->getSuggestions() as $suggestion) {
            $suggestions[] = $this->transformSuggestion($suggestion);
        }
        $listing->setSuggestions($suggestions);
    }

    /**
     * @param ResultSuggestion $suggestion
     * @return SuggestItem
     */
    private function transformSuggestion(ResultSuggestion $suggestion): SuggestItem
    {
        $suggestItem = new SuggestItem();
        $suggestItem->setName($suggestion->getName());
        $suggestItem->setType($suggestion->getType());
        if (!$suggestion->getImage() && $suggestion->getImage() !== '') {
            $suggestItem->setImgUrl($suggestion->getImage());
        } else {
            $suggestItem->setImgUrl($this->getImgUrl($suggestion->getType(), $this->getId($suggestion->getType(), $suggestion)));
        }

        /** @var array $attributes */
        $attributes = $suggestion->getAttributes();
        if (!empty($attributes)) {
            $suggestItem->setAttributes($this->parseAttributes($attributes));
        }
        return $suggestItem;
    }

    /**
     * Parsing attributes from FactFinder attributes to ours
     * @param array $attributes
     * @return array
     */
    private function parseAttributes(array $attributes): array
    {
        $result = [];
        foreach ($attributes as $key => $attribute) {
            try {
                if (is_array($attribute) && count($attribute) > 0 && is_string($attribute[0])) {
                    $result[$key] = $attribute[0];
                }
            } catch (Throwable $e) {}
        }
        return $result;
    }

    /**
     * Trying to get image url from database
     * @param string $type
     * @param string $id
     * @return string
     */
    private function getImgUrl(string $type, string $id): string
    {
        if (!$id) {
            return '';
        }

        $criteria = new Criteria([$id]);
        $criteria->addAssociation('media');

        if ($type === self::TYPE_PRODUCT) {
            /** @var ProductEntity|null $product */
            $product = $this->productRepository->search($criteria, $this->context->getContext())->first();
            if (
                $product && $product->getMedia() && $product->getMedia()->first() &&
                $product->getMedia()->first()->getMedia()
            ) {
                return $product->getMedia()->first()->getMedia()->getUrl();
            }
        } elseif ($type === self::TYPE_CATEGORY) {
            /** @var CategoryEntity|null $category */
            $category = $this->categoryRepository->search($criteria, $this->context->getContext())->first();
            if ($category && $category->getMedia()) {
                return $category->getMedia()->getUrl();
            }
        }

        return '';
    }

    /**
     * Getting Entity Id from ResultSuggestion
     * @param string $type
     * @param ResultSuggestion $suggestion
     * @return string
     */
    private function getId(string $type, ResultSuggestion $suggestion): string
    {
        /** @var array $attributes */
        $attributes = $suggestion->getAttributes();

        if ($type === self::TYPE_PRODUCT) {
            return $attributes['ProductID'][0];
        }

        if ($type === self::TYPE_CATEGORY) {
            return $attributes['CategoryID'][0];
        }

        return '';
    }
}