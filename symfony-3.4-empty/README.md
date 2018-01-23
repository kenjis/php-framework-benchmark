# Symfony Empty Edition

A skeleton allowing you to create an empty Symfony application: it is provided without
any libraries or bundles (except for Symfony's FrameworkBundle).

You can then start building on it, and install the dependencies you need.

> **Note**: The [Symfony Standard Edition](https://github.com/symfony/symfony-standard)
> provides a big set of libraries and bundles (database, email, templating, etc).
> If you don't feel comfortable with picking your own yet, you should probably use it.

## Installation

Use [Composer](https://getcomposer.org/) to create a new application:

```
composer create-project gnugat/symfony-empty-edition my-project
```

## Differences with the Standard Edition

* Only 2 bundles: `src/AppBundle` and `symfony/framework-bundle`, add the ones you really need
* Only 1 front controller (`web/app.php`), change the environment using the `SYMFONY_ENV` environment variable
* No annotations (can be brought back by installing `sensio/framework-extra-bundle`)

## Use cases

There are many real world use cases for this distribution. Here's a small selection:

* tailored made applications: for applications that require "non standard" dependencies (e.g. Propel or Pomm for the database, etc)
* micro applications: for applications that don't need database, templating or mailing systems (Symfony can be a Micro Framework)
* legacy migrations: for applications that need to depend on legacy database, templating, etc while migrating to symfony
* teaching material: [better explained here](http://www.whitewashing.de/2014/04/24/symfony_hello_world.html)

