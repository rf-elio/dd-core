<?php declare(strict_types=1);

namespace Elio\ElioDataDiscovery\Api\Configuration\Response;

class ConfigurationResponseCollection
{
    public const KEY = "ConfigurationResponseCollection";

    /**
     * @var ConfigurationResponse[]
     */
    protected array $configurationResponses = [];

    public function getConfigurationResponses(): array
    {
        return $this->configurationResponses;
    }

    public function addConfigurationResponses(ConfigurationResponse $configurationResponses): void
    {
        $this->configurationResponses[] = $configurationResponses;
    }
}
