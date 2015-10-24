# Expressive Skeleton and Installer

[![Build Status](https://secure.travis-ci.org/zendframework/zend-expressive-skeleton.svg?branch=master)](https://secure.travis-ci.org/zendframework/zend-expressive-skeleton)

*Begin developing PSR-7 middleware applications in seconds!*

[zend-expressive](https://github.com/zendframework/zend-expressive) builds on
[zend-stratigility](https://github.com/zendframework/zend-stratigility) to
provide a minimalist PSR-7 middleware framework for PHP with routing, DI
container, optional templating, and optional error handling capabilities.

This installer will setup a skeleton application based on zend-expressive by
choosing optional packages based on user input as demonstrated in the following
screenshot:

![screenshot-installer](https://cloud.githubusercontent.com/assets/459648/10410494/16bdc674-6f6d-11e5-8190-3c1466e93361.png)

The user selected packages are saved into `composer.json` so that everyone else
working on the project have the same packages installed. Configuration files and
templates are prepared for first use. The installer command is removed from
`composer.json` after setup succeeded, and all installer related files are
removed.

## Getting Started

Start your new Expressive project with composer:

```bash
$ composer create-project zendframework/zend-expressive-skeleton <project-path>
```

After choosing and installing the packages you want, go to the
`<project-path>` and start PHP's built-in web server to verify installation:

```bash
$ php -S 0.0.0.0:8000 -t public/ public/index.php
```
