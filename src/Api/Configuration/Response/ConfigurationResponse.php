<?php declare(strict_types=1);

namespace Elio\ElioDataDiscovery\Api\Configuration\Response;

use Elio\ElioDataDiscovery\Api\Response\Response;

class ConfigurationResponse extends Response
{
    protected array $presets;

    /**
     * @return array
     */
    public function getPresets(): array
    {
        return $this->presets;
    }

    /**
     * @param array $presets
     */
    public function setPresets(array $presets): void
    {
        $this->presets = $presets;
    }
}
