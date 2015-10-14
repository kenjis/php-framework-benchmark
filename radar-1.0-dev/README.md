# Radar

Radar is a PSR-7 compliant [Action-Domain-Responder](http://pmjones.io/adr)
(ADR) system. While it may look like a micro-framework, it is more like a
wrapper around the real core of your application domain.

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

## Documentation

You can read the documentation [here](docs/index.md).
