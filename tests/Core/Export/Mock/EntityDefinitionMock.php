<?php


namespace Elio\ElioDataDiscovery\Tests\Core\Export\Mock;


use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

/**
 * Class EntityDefinitionMock
 *
 * @package Elio\ElioDataDiscovery\Tests\Core\Export\Mock
 */
class EntityDefinitionMock extends EntityDefinition
{
    public function getEntityName(): string
    {
        return 'entity';
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([]);
    }
}
