# Introduction

## What is Action-Domain-Responder?

You are probably used to separating concerns in terms of Model-View-Controller
(MVC).  [Action-Domain-Responder](http://pmjones.io/adr) (ADR) represents a
refinement of MVC, and should not be difficult to understand if you are already
familiar with MVC.

With ADR, concerns are separated like this:

- the Domain is the logic to manipulate the domain, session, application, and
environment data, modifying state and persistence as needed. This is the real
core of the application, where all the interesting work happens; think in terms
of "Domain Model" or "Domain Driven Design" here.

- the Responder is the logic to build an HTTP response. It deals with body
content, templates and views, headers and cookies, status codes, and so on.
Think in terms of "Presentation" or "View" here, where the View represents the
entirety of the HTTP response, including both the headers and the body.

- the Action is the logic that connects the Domain and Responder. It passes the
user input to the Domain, and passes the Domain output to the Responder. It
would be tempting to think in terms of "Controller" here, but the Action is
intentionally very simple, even trivial. It should contain no logic aside from
connecting the Domain and Responder.

## How Does Radar Work?

Radar is superficially similar to a micro-framework. It has a routing system to
point URLs to actions, a wrapper- or chain-style middleware system to modify the
incoming HTTP request and outgoing HTTP response, and a dependency injection
container and configuration system to wire everything together.

However, with Radar, you don't specify "controllers" or "closures" for your
routes. Instead, you specify up to three callables per route, all of which are
optional:

1. A _Domain_ callable to be invoked with the user input. (If you don't specify
a Domain callable, the _Responder_ will be invoked directly; this is unusual but
sometimes convenient.)

2. An _Input_ callable to extract user input from the incoming HTTP
_ServerRequest_. The default Radar _Input_ callable will naively merge the
route path attributes (path-info parameters), the query parameters (`$_GET`),
the parsed body parameters (`$_POST`), and the uploaded files array (`$_FILES`)
into a single associative array of user input.

3. A _Responder_ callable to convert the _Domain_ output to an HTTP response.
The default Radar _Responder_ expects a
[_Payload_](https://github.com/auraphp/Aura.Payload) object from the _Domain_;
it delivers JSON output and sets proper HTTP status codes for a wide range of
scenarios.

These three callables are invoked within a standardized _ActionHandler_. As a
result, the Action logic in Radar is always the same for every route. The only
variations are in how input is collected, how output is presented, and of course
in how your core application domain operates.

So, don't think of Radar as a micro-framework. Think of it more like a wrapper
around the core of your real application domain. Its only purpose is to guide
input from the user into the domain, and to present output from the domain back
to the user.

## Installing Radar

You will need [Composer](https://getcomposer.org) to install Radar.

Pick a project name, and use Composer to create it with Radar; here we create
one called `example-project`:

    composer create-project -s dev radar/project example-project

Confirm the installation by changing into the project directory and starting the
built-in PHP web server:

    cd example-project
    php -S localhost:8080 -t web/

You can then browse to <http://localhost:8080/> and see JSON output:

    {"phrase":"Hello world"}

You can also browse to <http://localhost:8080/your-name> and see modified JSON output:

    {"phrase":"Hello your-name"}

## Project Structure

A Radar project looks like this:

    project/            # The Radar project directory
    ├── .env            # Environment variable definitions
    ├── composer.json   # Project composer file
    ├── src/            # Project class files
    │   ├── ...
    │   ├── ...
    │   └── ...
    ├── vendor/         # Composer-loaded packages
    │   ├── ...
    │   ├── ...
    │   └── ...
    └── web/            # Web document root
        └── index.php   # Bootstrap script


Of note, the `web/index.php` file is where you will:

- Boostrap the Radar `$adr` instance
- Define routes
- Define the middleware queue

## Setting A Project Namespace

In a Radar project, by default, Composer autoloads any unknown PSR-4 namespace
name from the `src/` directory. This means that you can create any PSR-4 style
class structure in the `src/` directory for your Radar project.

If you want to define a specific namespace for your project, modify
`composer.json` so that namespace points to the `src/` directory ...

    "autoload": {
        "psr-4": {
            "ProjectName\\": "src/"
        }
    },

... then regenerate the Composer autoloader by calling `composer dump-autoload`.

### Navigation

* Continue to [Domain Design](/docs/domain.md)
* Back to [the Index](/docs/index.md)
