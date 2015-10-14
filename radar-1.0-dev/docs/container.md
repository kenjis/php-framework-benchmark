# Container Configuration

## Aura.Di

The Radar dependency injection container is an instance of
[Aura.Di](https://github.com/auraphp/Aura.Di). The Radar boot process
runs a list of container configuration classes when building the container.
You can learn about container configuration classes
[here](https://github.com/auraphp/Aura.Di/blob/3.x/docs/index.md#container-builder-and-config-classes).

## Configuration (aka "Providers")

To tell the boot process which container configuration (aka "provider") classes
to load, and in which order, pass an array of config class names to the `Boot()`
call in `web/index.php`. This array will automatically be merged with the default Config that comes with ADR.

```php
$boot = new Boot();
$adr = $boot->adr([
    'Foo\Bar\ContainerConfig',
    'Baz\Dib\_Config',
    'Some\Other\ConfigClass'
]);
```

These will be loaded by the container builder in order.

Of special importance, if your _Domain_ is in a separate package and has its
own configuration class, be sure to include that class name in the list.

## Extracting Setup To Configuration

If you like, you can greatly reduce the size of your `web/index.php` file by
moving all the middleware and route setup logic to a configuration class,
and then specifying that class as part of the container configuration. For
example, if your `web/index.php` has this setup logic ...

```php
$adr->middle('Radar\Adr\Handler\ExceptionHandler');
$adr->middle('My\Middleware\BarHandler');
$adr->middle('Radar\Adr\Handler\RoutingHandler');

...

$adr->get(...);
$adr->post(...);
$adr->delete(...);
```

... you can extract that to a DI configuration class like so. The follwing
_My\Config_ class might be saved in `src/My/Config.php`; note the use of
`$di->get()` to retrieve the `$adr` service.

```php
namespace My;

use Aura\Di\Container;
use Aura\Di\ContainerConfig;

class Config extends ContainerConfig
{
    public function modify(Container $di)
    {
        $adr = $di->get('radar/adr:adr');

        $adr->middle('Radar\Adr\Handler\ExceptionHandler');
        $adr->middle('My\Middleware\FooHandler');
        $adr->middle('My\Middleware\BarHandler');
        $adr->middle('Radar\Adr\Handler\RoutingHandler');
        $adr->middle('Radar\Adr\Handler\ActionHandler');
        $adr->middle('Radar\Adr\Handler\SendingHandler');

        $adr->get(...);
        $adr->post(...);
        $adr->delete(...);
    }
}
```

Then you can replace the setup logic from `web/index.php` by specifying a
Config file in the `Boot()` params:

```php
$boot = new Boot();
$adr = $boot->adr([
    'Foo\Bar\ContainerConfig',
    'My\Config',
]);
```

### Navigation

* Continue to [Execution Path](/docs/execution.md)
* Back to [Environment Variables](/docs/environment.md)
