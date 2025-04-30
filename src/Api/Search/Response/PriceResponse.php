<?php
declare(strict_types=1);

namespace Elio\ElioDataDiscovery\Api\Search\Response;

use Elio\ElioDataDiscovery\Api\Response\Response;
use Elio\ElioDataDiscovery\Core\Content\CustomPrice\SalesChannel\CustomPriceItem;

class PriceResponse extends Response
{
    protected array $customPriceItems = [];

    public function getCustomPriceItems(): array
    {
        return $this->customPriceItems;
    }

    public function addCustomPriceItem(CustomPriceItem $customPriceItem): void
    {
        $this->customPriceItems[] = $customPriceItem;
    }
}