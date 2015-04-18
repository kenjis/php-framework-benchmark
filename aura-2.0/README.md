# Aura.Web_Project

This package provides a minimal framework for web projects.

By "minimal" we mean *very* minimal. The package provides only a dependency
injection container, a configuration system, a router, a dispatcher, a pair of
request and response objects, and a logging instance.

This minimal implementation should not be taken as "restrictive". The DI
container, with its two-stage configuration system, allows a wide range of
programmatic service definitions. The router and dispatcher are built with
iterative refactoring in mind, so you can start with micro-framework-like
closure controllers, and work your way into more complex controller objects of
your own design.

## Foreword

### Requirements

This project requires PHP 5.4 or later; we recommend using the latest available version of PHP as a matter of principle.

Unlike Aura library packages, this project package has userland dependencies, which themselves may have other dependencies:

- [aura/web-kernel](https://packagist.org/packages/aura/web-kernel)
- [monolog/monolog](https://packagist.org/packages/monolog/monolog)

### Installation

Install this project via Composer to a `{$PROJECT_PATH}` of your choosing:

    composer create-project aura/web-project {$PROJECT_PATH}

This will create the project skeleton and install all of the necessary packages.

### Tests

[![Build Status](https://travis-ci.org/auraphp/Aura.Web_Project.png)](https://travis-ci.org/auraphp/Aura.Web_Project)

To run the unit tests at the command line, issue `./phpunit.sh` at the package root. This requires [PHPUnit](http://phpunit.de/) to be available as `phpunit`.

Alternatively, after you have installed the project, start the built-in PHP server with the `web/` directory as the document root:

    cd {$PROJECT_PATH}
    php -S localhost:8000 -t web/

When you browse to <http://localhost:8000> you should see "Hello World!" as the output. Terminate the built-in server process thereafter. (Be sure to use the built-in PHP server only for testing, never for production.)

### PSR Compliance

This projects attempts to comply with [PSR-1][], [PSR-2][], and [PSR-4][]. If you notice compliance oversights, please send a patch via pull request.

[PSR-1]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md
[PSR-2]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md
[PSR-4]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md

### Community

To ask questions, provide feedback, or otherwise communicate with the Aura community, please join our [Google Group](http://groups.google.com/group/auraphp), follow [@auraphp on Twitter](http://twitter.com/auraphp), or chat with us on #auraphp on Freenode.

### Services

This package uses services defined by:

- [Aura.Project_Kernel](https://github.com/auraphp/Aura.Project_Kernel#services)
- [Aura.Web_Kernel](https://github.com/auraphp/Aura.Web_Kernel#services)

This project resets the following services:

- `aura/project-kernel:logger`: an instance of `Monolog\Logger`

## Getting Started

### Component Packages

This project combines a collection of independent Aura packages into a cohesive whole. The operation of each package is documented separately.

The dependency injection _Container_ is absolutely central to the operation of an Aura project. Please be familiar with [the Aura.Di docs](https://github.com/auraphp/Aura.Di) before continuing.

You should also familiarize yourself with [Aura.Router](https://github.com/auraphp/Aura.Router), [Aura.Dispatcher](https://github.com/auraphp/Aura.Dispatcher), and the [Aura.Web](https://github.com/auraphp/Aura.Web) _Request_ and _Response_ objects.

### Project Configuration

Every Aura project is configured the same way. Please see the [shared configuration docs](https://github.com/auraphp/Aura.Project_Kernel#configuration) for more information.

### Logging

The project automatically logs to `{$PROJECT_PATH}/tmp/log/{$mode}.log`. If
you want to change the logging behaviors for a particular config mode,
edit the related config file (e.g., `config/Dev.php`) file to modify the
`aura/project-kernel:logger` service.

### Routing and Dispatching

We configure routing and dispatching via the project-level `config/`
class files. If a route needs to be available in every config mode,
edit the project-level `config/Common.php` class file. If it only needs
to be available in a specific mode, e.g. `dev`, then edit the config file for
that mode.

Here are three different styles of routing and dispatching.

#### Micro-Framework Style

Aura is the first framework which follows the
[Action Domain Responder](https://github.com/pmjones/mvc-refinement) pattern.
The following is an example of a micro-framework style route, where the
action logic is embedded in the route params. In the `modifyWebRouter()`
config method, we retrieve the shared `aura/web-kernel:request` and
`aura/web-kernel:response` services, along with the `aura/web-kernel:router`
service. We then add a route names `blog.read` and embed the action code as a
closure.

```php
<?php
namespace Aura\Web_Project\_Config;

use Aura\Di\Config;
use Aura\Di\Container;

class Common extends Config
{
    // ...

    public function modifyWebRouter(Container $di)
    {
        $request = $di->get('aura/web-kernel:request');
        $response = $di->get('aura/web-kernel:response');

        $router = $di->get('aura/web-kernel:router');
        $router
            ->add('blog.read', '/blog/read/{id}')
            ->addValues(array(
                'action' => function ($id) use ($request, $response) {
                    $content = "Reading blog post $id";
                    $response->content->set(htmlspecialchars(
                        $content, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'
                    ));
                }
            ));
    }

    // ...
}
?>
```

You can now start up the built-in PHP server to get the application running ...

    cd {$PROJECT_PATH}
    php -S localhost:8000 -t web/

... and browse to <http://localhost:8000/blog/read/88> to see the application output.

#### Modified Micro-Framework Style

We can modify the above example to put the controller logic in the
dispatcher instead of the route itself.

Extract the action closure to the dispatcher under the name
`blog.read`. Then, in the route, use a `action` value that
matches the name in the dispatcher.

```php
<?php
namespace Aura\Web_Project\_Config;

use Aura\Di\Config;
use Aura\Di\Container;

class Common extends Config
{
    // ...


    public function modifyWebRouter(Container $di)
    {
        $router = $di->get('aura/web-kernel:router');
        $router
            ->add('blog.read', '/blog/read/{id}')
            ->addValues(array(
                'action' => 'blog.read',
            ));
    }

    public function modifyWebDispatcher(Container $di)
    {
        $request = $di->get('aura/web-kernel:request');
        $response = $di->get('aura/web-kernel:response');

        $dispatcher = $di->get('aura/web-kernel:dispatcher');
        $dispatcher->setObject(
            'blog.read',
            function ($id) use ($request, $response) {
                $content = "Reading blog post $id";
                $response->content->set(htmlspecialchars(
                    $content, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'
                ));
            }
        );

    }

    // ...
}
?>
```

You can now start up the built-in PHP server to get the application running ...

    cd {$PROJECT_PATH}
    php -S localhost:8000 -t web/

... and browse to <http://localhost:8000/blog/read/88> to see the application
output.

#### Full-Stack Style

You can migrate from a micro style to a full-stack style (or start
with full-stack style in the first place).

First, define an action class and place it in the project `src/` directory.

```php
<?php
/**
 * {$PROJECT_PATH}/src/App/Actions/BlogReadAction.php
 */
namespace App\Actions;

use Aura\Web\Request;
use Aura\Web\Response;

class BlogReadAction
{
    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    public function __invoke($id)
    {
        $content = "Reading blog post $id";
        $this->response->content->set(htmlspecialchars(
            $content, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'
        ));
    }
}
?>
```

Next, tell the project how to build the _BlogReadAction_ through the DI
_Container_. Edit the project `config/Common.php` file to configure the
_Container_ to pass the `aura/web-kernel:request` and `aura/web-kernel:response` service objects to
the _BlogReadAction_ constructor.

```php
<?php
namespace Aura\Web_Project\_Config;

use Aura\Di\Config;
use Aura\Di\Container;

class Common extends Config
{
    public function define(Container $di)
    {
        $di->set('aura/project-kernel:logger', $di->lazyNew('Monolog\Logger'));

        $di->params['App\Actions\BlogReadAction'] = array(
            'request' => $di->lazyGet('aura/web-kernel:request'),
            'response' => $di->lazyGet('aura/web-kernel:response'),
        );
    }

    // ...
}
?>
```

After that, put the _App\Actions\BlogReadAction_ object in the dispatcher
under the name `blog.read` as a lazy-loaded instantiation ...

```php
<?php
namespace Aura\Web_Project\_Config;

use Aura\Di\Config;
use Aura\Di\Container;

class Common extends Config
{
    // ...

    public function modifyWebDispatcher(Container $di)
    {
        $dispatcher = $di->get('aura/web-kernel:dispatcher');
        $dispatcher->setObject(
            'blog.read',
            $di->lazyNew('App\Actions\BlogReadAction')
        );
    }

    // ...
}
?>
```

... and finally, point the router to the `blog.read` action object:

```php
<?php
namespace Aura\Web_Project\_Config;

use Aura\Di\Config;
use Aura\Di\Container;

class Common extends Config
{
    // ...

    public function modifyWebRouter(Container $di)
    {
        $router = $di->get('aura/web-kernel:router');
        $router->add('blog.read', '/blog/read/{id}');
    }

    // ...
}
?>
```

You can now start up the built-in PHP server to get the application
running ...

    cd {$PROJECT_PATH}
    php -S localhost:8000 -t web/

... then browse to <http://localhost:8000/blog/read/88> to see the application
output.

#### Other Variations

These are only some common variations of router and dispatcher interactions;
[there are many other combinations](https://github.com/auraphp/Aura.Dispatcher/tree/develop-2#refactoring-to-architecture-changes).
