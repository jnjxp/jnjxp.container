<?php

declare(strict_types=1);

namespace Jnjxp\Container\ServiceProvider;

use Interop\Container\ServiceProviderInterface;
use Jnjxp\Container\ContainerException;

/**
 * @psalm-api
 */
class AggregateServiceProvider extends BaseServiceProvider
{
    /** @var array<ServiceProviderInterface> $providers */
    protected array $providers = [];

    /** @var array<class-string|ServiceProviderInterface> $specs */
    protected array $specs = [];

    public function __construct(string|ServiceProviderInterface ...$specs)
    {
        /** @var class-string|ServiceProviderInterface $spec */
        foreach (array_merge($this->specs, $specs) as $spec) {
            $this->providers[] = $spec instanceof ServiceProviderInterface ? $spec : $this->provider($spec);
        }
    }

    /**
     * @template T of \Interop\Container\ServiceProviderInterface
     * @param class-string<T> $spec
     * @return ServiceProviderInterface
     */
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
     * @return array<string, (callable(): mixed)|string>
     *
     * @psalm-suppress ImplementedReturnTypeMismatch
     * @psalm-suppress MixedReturnTypeCoercion
     */
    #[\Override]
    public function getFactories(): array
    {
        return array_reduce($this->providers, function (array $carry, ServiceProviderInterface $provider) {
            return array_merge($carry, $provider->getFactories());
        }, []);
    }

    /**
     * getExtensions
     *
     * @return array<string, array<(callable(): mixed)|string>>
     *
     * @psalm-suppress MixedReturnTypeCoercion
     */
    #[\Override]
    public function getExtensions(): array
    {
        return array_reduce($this->providers, function (array $carry, ServiceProviderInterface $provider) {
            return array_merge($carry, $provider->getExtensions());
        }, []);
    }
}
