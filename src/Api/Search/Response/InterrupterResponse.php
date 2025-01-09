<?php
declare(strict_types=1);

namespace Elio\ElioDataDiscovery\Api\Search\Response;

use Elio\ElioDataDiscovery\Api\Response\Response;
use Elio\ElioDataDiscovery\Core\Content\Interrupter\SalesChannel\InterrupterItem;

class InterrupterResponse extends Response
{
    public const KEY = 'InterrupterResponse';

    /**
     * @var InterrupterItem[]
     */
    protected array $interrupterItems = [];

    public function getInterrupterItems(): array
    {
        return $this->interrupterItems;
    }

    public function addInterrupterItem(InterrupterItem $interrupterItem): void
    {
        $this->interrupterItems[] = $interrupterItem;
    }

    public function getInterrupterItemWithPosition(int $position): ?InterrupterItem
    {
        foreach ($this->interrupterItems as $interrupterItem) {
            if ($interrupterItem->getPosition() === $position) {
                return $interrupterItem;
            }
        }
        return null;
    }
}
