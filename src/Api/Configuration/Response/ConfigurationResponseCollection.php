<?php declare(strict_types=1);

namespace Elio\ElioDataDiscovery\Api\Configuration\Response;

use Shopware\Core\Framework\Struct\Struct;

class ConfigurationResponseCollection extends Struct
{
    /**
     * @var ConfigurationResponse[]
     */
    protected array $configurationResponses = [];

    public function getConfigurationResponseByType(string $type): ?ConfigurationResponse
    {
        return $this->configurationResponses[$type] ?? null;
    }

    public function getConfigurationResponses(): array
    {
        return $this->configurationResponses;
    }

    public function addConfigurationResponse(ConfigurationResponse $configurationResponse): void
    {
        $this->configurationResponses[$configurationResponse->getType()] = $configurationResponse;
    }
}
