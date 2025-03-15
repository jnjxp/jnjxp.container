<?php

declare(strict_types=1);

namespace Jnjxp\Container\ServiceProvider;

use Interop\Container\ServiceProviderInterface;
use Jnjxp\Container\ContainerException;

class AggregateServiceProvider extends BaseServiceProvider
{
    /** @var array<ServiceProviderInterface> $providers */
    protected array $providers = [];

    /** @var array<string|ServiceProviderInterface> $specs */
    protected array $specs = [];

    public function __construct(string|ServiceProviderInterface ...$specs)
    {
        foreach (array_merge($this->specs, $specs) as $spec) {
            $this->providers[] = $spec instanceof ServiceProviderInterface ? $spec : $this->provider($spec);
        }
    }

    protected function provider(string $spec): ServiceProviderInterface
    {
        class_exists($spec) || throw new ContainerException("Class '$spec' does not exist");
        $provider = new $spec();
        $provider instanceof ServiceProviderInterface || throw new ContainerException("Invalid provider: $spec");
        return $provider;
    }

    /**
     * getFactories
     *
     * @return array<string, string|callable>
     */
    #[\Override]
    public function getFactories(): array
    {
        return array_reduce($this->providers, function ($carry, $provider) {
            return array_merge($carry, $provider->getFactories());
        }, []);
    }

    /**
     * getExtensions
     *
     * @return array<string, array<string|callable>>
     */
    #[\Override]
    public function getExtensions(): array
    {
        return array_reduce($this->providers, function ($carry, $provider) {
            return array_merge($carry, $provider->getExtensions());
        }, []);
    }
}
