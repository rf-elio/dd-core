<?php
declare(strict_types=1);

namespace Elio\ElioDataDiscovery\Storefront\TwigExtension;

use Elio\ElioDataDiscovery\Core\Features\FeatureService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FeatureActive extends AbstractExtension
{
    public function __construct(
        private readonly FeatureService $featureService
    ) {}

    public function getName(): string
    {
        return 'twig.feature_active';
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_feature_active', $this->isFeatureActive(...)),
        ];
    }

    /**
     * @param string $feature
     * @return bool
     */
    public function isFeatureActive(string $feature): bool
    {
        return $this->featureService->getContext()->isEnabled($feature);
    }
}
