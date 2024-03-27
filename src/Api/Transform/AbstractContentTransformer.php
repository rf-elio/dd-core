<?php

namespace Elio\ElioDataDiscovery\Api\Transform;

use Elio\ElioDataDiscovery\Api\Search\Response\ContentListingResponse;
use Elio\ElioDataDiscovery\Core\Content\Content\SalesChannel\ContentGroup;

abstract class AbstractContentTransformer implements ResponseTransformerInterface
{
    protected const TOP_CONTENT_PREFIX = 'top-';

    /**
     * Groups the content items by the given type
     *
     * @param ContentListingResponse $listing
     */
    protected function createContentGroups(ContentListingResponse $listing): void
    {
        $regularContentGroups = [];
        $topContentGroups = [];

        foreach ($listing->getContentItems() as $contentItem) {
            $type = $contentItem->getType();

            if (empty($type)) {
                continue;
            }

            // top content
            if (str_starts_with($type, self::TOP_CONTENT_PREFIX)) {
                if(!isset($topContentGroups[$type])) {
                    $topContentGroups[$type] = new ContentGroup($type, $type);
                }
                $topContentGroups[$type]->addContentItem($contentItem);
            } else {
                if(!isset($regularContentGroups[$type])) {
                    $regularContentGroups[$type] = new ContentGroup($type, $type);
                }
                $regularContentGroups[$type]->addContentItem($contentItem);
            }
        }

        $listing->setContentGroups($regularContentGroups);
        $listing->setTopContentGroups($topContentGroups);
    }
}