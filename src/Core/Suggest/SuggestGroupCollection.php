<?php declare(strict_types=1);

namespace Elio\ElioDataDiscovery\Core\Suggest;

use Shopware\Core\Framework\Struct\Collection;

class SuggestGroupCollection extends Collection
{
    public function sortGroups(array $acceptedTypes): array
    {
        if(empty($acceptedTypes)) {
            return $this->elements;
        }

        // set visibility and position
        foreach ($this->elements as $group) {
            $type = $group->getType();
            $acceptedTypePosition = array_search($type, $acceptedTypes, true);

            if($acceptedTypePosition === false) {
                $group->setVisible(false);
            } else {
                $group->setVisible(true);
                $group->setPosition((int)$acceptedTypePosition);
            }
        }

        // sort groups
        uasort($this->elements, static function (SuggestGroup $a, SuggestGroup $b) {
            $posA = $a->getPosition();
            $posB = $b->getPosition();
            return $posA <=> $posB;
        });
        return $this->elements;
    }
}