<?php declare(strict_types=1);

namespace Elio\ElioDataDiscovery\Api\Configuration\Response;

use Elio\ElioDataDiscovery\Api\Response\Response;

abstract class ConfigurationResponse extends Response
{
    protected string $type = '';

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }
}
