# jnjxp.container
[![CI](https://github.com/jnjxp/jnjxp.container/actions/workflows/ci.yml/badge.svg)](https://github.com/jnjxp/jnjxp.container/actions/workflows/ci.yml)

Yet another dependency injection container.

## Install

```bash
$ composer require jnjxp/container
```

## Usage

### Factories

Define how to create entries based on string identifier.

```php
// Given

use Jnjxp\Container\Container;

class Foo {
    public function __construct(public int $dependency) { }
}

class Bar extends Foo { }

class BarFactory {
    public function __invoke() { return new Bar(1); }
}

// Configure Container

$factories = [
    Foo::class => fn() => new Foo(1), // callable
    Bar::class => BarFactory::class, // string identifier
];

$container = new Container(factories: $factories);

// Use Container

$foo = $container->get(Foo::class);
$bar = $container->get(Bar::class);

print_r($foo);
print_r($bar);

// Foo Object
// (
//     [dependency] => 1
// )
// Bar Object
// (
//     [dependency] => 1
// )
```

#### Resolving Dependencies

`Container` is passed to factories.

```php
// Given

use Jnjxp\Container\Container;
use Psr\Container\ContainerInterface as CI;

class Foo {
    public function __construct(public Bar $bar) { }
}

class Bar {}

// Configure Container

$factories = [
    Foo::class => fn(CI $container) => new Foo($container->get(Bar::class)),
    Bar::class => fn() => new Bar(),
];

$container = new Container(factories: $factories);

// Use Container

$foo = $container->get(Foo::class);

print_r($foo);

// Foo Object
// (
//     [bar] => Bar Object
//         (
//         )
//
// )
```


### Aliases

Add additional identifiers to entries.

```php
// Given

use Jnjxp\Container\Container;

interface FooInterface {}

class Foo implements FooInterface {}

// Configure Container

$factories = [ Foo::class => fn() => new Foo() ];

$aliases = [
    FooInterface::class => Foo::class
];

$container = new Container(
    factories: $factories,
    aliases: $aliases,
);

// Use Container

$foo = $container->get(FooInterface::class);

print_r($foo);

// Foo Object
// (
// )
```

### Existing Instances

Container won't look for factories or extensions.

```php
// Given

use Jnjxp\Container\Container;

// Configure Container

$instances = [ 'FOO' => 'foo' ];

$container = new Container(
    instances: $instances,
);

// Use Container

$foo = $container->get('FOO');

echo "$foo\n";

//foo
```

### Extensions

- Should be an `array` of callables or identifiers that resolve to them.
- 1st parameter is the `Container`, 2nd is the existing Instance. ([should be swapped](https://github.com/container-interop/service-provider/issues/50))
- The entry will be **replaced** by what is returned from the extension.

```php
// Given

use Jnjxp\Container\Container;
use Psr\Container\ContainerInterface as CI;

class Foo {}

class FooDecorator
{
    public function __construct(public Foo $foo) { }
}

class Bar { public $bar ; }

// Configure Container

$extensions = [
    Foo::class => [ fn(CI $container, Foo $foo) => new FooDecorator($foo) ],
    Bar::class => [ function(CI $container, Bar $bar) { $bar->bar = 1 ; return $bar; } ],
];

$container = new Container(
    extensions: $extensions,
);

// Use Container

$foo = $container->get(Foo::class);
$bar = $container->get(Bar::class);

print_r($foo);
print_r($bar);

// FooDecorator Object
// (
//     [foo] => Foo Object
//         (
//         )
//
// )
// Bar Object
// (
//     [bar] => 1
// )
```

### Autowire

Enable auto-wiring by providing an `AutowireInterface`

```php
// Given

use Jnjxp\Container\Container;
use Jnjxp\Container\Autowire\Autowire;

class Foo {
    public function __construct(public Bar $bar) { }
}

class Bar { }

// Configure Container

$autowire = new Autowire();

$container = new Container(
    autowire: $autowire,
);

// Use Container

$foo = $container->get(Foo::class);

print_r($foo);

// Foo Object
// (
//     [bar] => Bar Object
//         (
//         )
//
// )
```

#### `AutowireInterface`

```php
use Psr\Container\ContainerInterface;

interface AutowireInterface
{
    public function create(string $className, ?ContainerInterface $container = null): object;
}
```

### Builder

User `ContainerBuilder` to build a `Container`.

```php
use Jnjxp\Container\ContainerBuilder;

$builder = new Builder();

// set factories
$builder->factory($name, $factory);
$builder->factories($factories);

// set aliases
$builder->alias($alias, $identifier);
$builder->aliases($aliases);

//set instances
$builder->instance($name, $instance);
$builder->instances($instances);

// set extensions
$builder->extension($name, $extension);
$builder->extensions($extensions);

// enable auto-wiring
$builder->autowire(true);
$builder->autowire($autowireImplementation);

// disable auto-wiring
$builder->autowire(false);

// Bulid the Container
$container = $builder->build();
```

### Service Providers

Service Providers can define *factories* and *extensions* in the `ContainerBuilder`.

See [container-interop/service-provider](https://github.com/container-interop/service-provider/tree/v0.4.1).

#### `ServiceProviderInterface`
```php
namespace Interop\Container;

interface ServiceProviderInterface
{
    public function getFactories();

    public function getExtensions();
}
```
#### Service Provider Example

```php
// Given

use Jnjxp\Container\ContainerBuilder;
use Jnjxp\Container\ServiceProvider\BaseServiceProvider;

class Foo {
    public function __construct(public int $dependency) { }
}

class ServiceProvider extends BaseServiceProvider
{
    public function getFactories(): array
    {
        return [ Foo::class => fn() => new Foo(1) ];
    }
}

$builder = new ContainerBuilder();
$builder->provider(ServiceProvider::class);
$container = $builder->build();

$foo = $container->get(Foo::class);

print_r($foo);


// Foo Object
// (
//     [dependency] => 1
// )
```
