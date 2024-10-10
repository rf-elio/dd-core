<?php declare(strict_types=1);

namespace Elio\ElioDataDiscovery\Api\Configuration\Response;

class PresetConfigurationResponse extends ConfigurationResponse
{
    protected array $presets;

    public function __construct(array $presets)
    {
        $this->type = 'preset';
        $this->presets = $presets;
    }

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
