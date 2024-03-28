<?php

namespace Elio\ElioDataDiscovery\Api\Response;

use Elio\ElioDataDiscovery\Swagger\ModelInterface;
use Shopware\Core\Framework\Struct\Struct;

class StructWrapper extends Struct
{
    public function __construct(
        private readonly ModelInterface $model
    ) {}

    public function getModel(): ModelInterface
    {
        return $this->model;
    }
}