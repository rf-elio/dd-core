<?php declare(strict_types=1);

namespace Elio\ElioDataDiscovery\Api\Configuration\Response;

use Elio\ElioDataDiscovery\Api\Response\Response;

abstract class ConfigurationResponse extends Response
{
    protected string $type = '';
    protected string $collection;

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getCollection(): string
    {
        return $this->collection;
    }

    /**
     * @param string $collection
     */
    public function setCollection(string $collection): void
    {
        $this->collection = $collection;
    }
}
