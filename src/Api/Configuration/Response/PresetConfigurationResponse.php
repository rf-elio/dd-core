<?php declare(strict_types=1);

namespace Elio\ElioDataDiscovery\Api\Configuration\Response;

class PresetConfigurationResponse extends ConfigurationResponse
{
    public const TYPE = 'preset';

    protected array $presets;

    public function __construct(array $presets, string $collection)
    {
        $this->type = self::TYPE;
        $this->presets = $presets;
        $this->collection = $collection;
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
