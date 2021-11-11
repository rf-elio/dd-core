<?php

namespace Elio\FactFinder\Api\Search\ResponseTransformer;


use DateTimeImmutable;
use DateTimeInterface;
use Elio\FactFinder\Api\Request\ApiRequest;
use Elio\FactFinder\Api\Response\ResponseCollection;
use Elio\FactFinder\Api\Search\Request\ContentSearchRequest;
use Elio\FactFinder\Api\Search\Response\ContentListingResponse;
use Elio\FactFinder\Api\Transform\ResponseTransformerInterface;
use Elio\FactFinder\Core\Content\Content\SalesChannel\ContentGroup;
use Elio\FactFinder\Core\Content\Content\SalesChannel\ContentItem;
use Elio\FactFinder\Core\Exception\InvalidTypeException;
use Elio\FactFinder\Core\Export\Generator\Content\ContentExportDefaults;
use Elio\FactFinder\Core\Export\Generator\ExportDefaults;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Swagger\Client\Model\ModelInterface;
use Swagger\Client\Model\Result;

/**
 * Adds the content responses (content channel) to the search result
 *
 * Class ContentTransformer
 * @package Elio\FactFinder\Api\Search\ResponseTransformer
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class ContentTransformer implements ResponseTransformerInterface
{
    protected const MASTER_VALUE_MASTER_PRODUCT_NUMBER = 'MasterProductNumber';
    protected const MASTER_VALUE_CATEGORY_PATH = 'CategoryPath';
    protected const MASTER_VALUE_NAME = 'Name';
    protected const MASTER_VALUE_DESCRIPTION = 'Description';
    protected const MASTER_VALUE_URL = 'ProductURL';
    protected const MASTER_VALUE_IMAGE_URL = 'ImageURL';

    public function supports(ModelInterface $model, ApiRequest $request, SalesChannelContext $context): bool
    {
        return $model instanceof Result && $request instanceof ContentSearchRequest;
    }

    /**
     * Adds the content responses to the result collection
     *
     * @param ModelInterface $model
     * @param ResponseCollection $responseCollection
     * @param SalesChannelContext $context
     * @param ApiRequest $request
     */
    public function transform(ModelInterface $model, ResponseCollection $responseCollection, SalesChannelContext $context, ApiRequest $request): void
    {
        if(!$model instanceof Result) {
            throw new InvalidTypeException($model, Result::class);
        }

        $listing = $responseCollection->get(ContentListingResponse::class) ?? new ContentListingResponse();
        $responseCollection->set(ContentListingResponse::class, $listing);

        foreach ($model->getHits() as $hit) {
            $masterValues = $hit->getMasterValues();
            $content = new ContentItem(
                $hit->getId(),
                $this->getFirstValue($masterValues, ContentExportDefaults::FIELD_TYPE) ?? '',
                $this->getFirstValue($masterValues, ContentExportDefaults::FIELD_CONTENT_STRUCTURE) ?? '',
                $this->getFirstValue($masterValues, ContentExportDefaults::FIELD_TITLE) ?? '',
                $this->getFirstValue($masterValues, ContentExportDefaults::FIELD_DESCRIPTION) ?? '',
                $this->getFirstValue($masterValues, ContentExportDefaults::FIELD_URL) ?? '',
                $this->getFirstValue($masterValues, ContentExportDefaults::FIELD_IMAGE_URL) ?? '',
                $this->restoreDateTime(
                    $this->getFirstValue($masterValues, ContentExportDefaults::FIELD_PUBLICATION_DATE) ?? ''
                ),
                (int)($this->getFirstValue($masterValues, ContentExportDefaults::FIELD_PRIORITY) ?? ContentExportDefaults::DEFAULT_PRIORITY),
                $hit->getPosition()
            );
            $listing->addContentItem($content);
        }

        $this->createContentGroups($listing);
    }

    /**
     * @param array $masterValues
     * @param string $key
     * @return mixed|null
     */
    protected function getFirstValue(array $masterValues, string $key)
    {
        if(!isset($masterValues[$key])) {
            return null;
        }

        if(!is_array($masterValues[$key])) {
            return $masterValues[$key];
        }

        $values = $masterValues[$key];
        return array_shift($values);
    }

    /**
     * Restores the date time by the default export format
     *
     * @param string|null $value
     * @return DateTimeInterface|null
     */
    protected function restoreDateTime(?string $value) : ?DateTimeInterface
    {
        if(empty($value)) {
            return null;
        }

        $value = trim($value, '"');
        return DateTimeImmutable::createFromFormat(ExportDefaults::DATE_TIME_FORMAT, $value);
    }

    /**
     * Groups the content items by the given type
     *
     * @param ContentListingResponse $listing
     */
    protected function createContentGroups(ContentListingResponse $listing): void
    {
        $contentGroups = [];

        foreach ($listing->getContentItems() as $contentItem) {
            $type = $contentItem->getType();

            if(!isset($contentGroups[$type])) {
                $contentGroups[$type] = new ContentGroup($type, $type);
            }

            $contentGroups[$type]->addContentItem($contentItem);
        }

        $listing->setContentGroups($contentGroups);
    }
}