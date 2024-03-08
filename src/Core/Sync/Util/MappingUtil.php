<?php declare(strict_types=1);

namespace Elio\ElioSearch\Core\Sync\Util;

use Elio\ElioSearch\Core\Sync\DataTypes\DataTypeInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class MappingUtil
{
    public static function addMappedProperties(
        DataTypeInterface $dataType, array $mappings, PropertyAccessorInterface $propertyAccessor
    ): array
    {
        $mappedProperties = [];
        foreach ($mappings as $mapping) {
            if (str_contains((string) $mapping['source'], '.')) {
                $parts = explode('.',(string) $mapping['source']);
                $previousObj = $dataType;
                foreach ($parts as $part) {
                    if ($part === 'first') {
                        $previousObj = array_values($propertyAccessor->getValue($previousObj, 'elements'))[0];
                    } elseif (is_object($previousObj) || is_array($previousObj)) {
                        $previousObj = $propertyAccessor->getValue($previousObj, $part);
                    }
                }
                $mappedProperties[$mapping['target']] = $previousObj;
            } else {
                $mappedProperties[$mapping['target']] = $propertyAccessor->getValue($dataType, $mapping['source']);
            }
        }
        return $mappedProperties;
    }
}
