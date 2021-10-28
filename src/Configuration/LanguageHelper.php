<?php

namespace Elio\FactFinder\Configuration;

use Shopware\Core\Framework\Context;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class LanguageHelper
{
    /**
     * Extracts the first language id from the language chain in the given context
     *
     * @param Context $context
     * @return string|null
     */
    public static function getLanguageIdByContext(Context $context): ?string
    {
        if ($context->getLanguageIdChain() && count($context->getLanguageIdChain()) > 0) {
            return $context->getLanguageIdChain()[0];
        }

        return null;
    }

    /**
     * Extracts the first language id from the language chain in the given context
     *
     * @param SalesChannelContext $salesChannelContext
     * @return string|null
     */
    public static function getLanguageIdBySalesChannelContext(SalesChannelContext $salesChannelContext): ?string
    {
        if(count($salesChannelContext->getLanguageIdChain()) > 0) {
            return $salesChannelContext->getLanguageIdChain()[0];
        }

        return null;
    }


}