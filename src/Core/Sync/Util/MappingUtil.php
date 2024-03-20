<?php declare(strict_types=1);

namespace Elio\ElioDataDiscovery\Core\Sync\Util;

use Elio\ElioDataDiscovery\Core\Sync\DataTypes\DataTypeInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class MappingUtil
{
    public static function addMappedProperties(
        DataTypeInterface $dataType, array $mappings, PropertyAccessorInterface $propertyAccessor
    ): array
    {
        $mappedProperties = [];
        foreach ($mappings as $mapping) {
            if (str_contains((string)$mapping['source'], '.')) {
                $parts = explode('.', (string)$mapping['source']);
                $previousObj = $dataType;
                foreach ($parts as $part) {
                    if ($part === 'first') {
                        $previousObj = array_values($propertyAccessor->getValue($previousObj, 'elements'))[0];
                    } elseif (is_object($previousObj) || is_array($previousObj)) {
                        $previousObj = $propertyAccessor->getValue($previousObj, $part);
                    }
                }
                $mappedProperties[$mapping['target']] = $previousObj;
            } else if ($propertyAccessor->isReadable($dataType, $mapping['source'])) {
                $mappedProperties[$mapping['target']] = $propertyAccessor->getValue($dataType, $mapping['source']);
            } else {
                $mappedProperties[$mapping['target']] = null;
            }
        }
        return $mappedProperties;
    }
}
