# PHP ORM Benchmark

## Other Results

### [motin](https://github.com/motin)

(2015-12-09)

Using [the supplied Docker Stack]((docker/README.md)):

* Ubuntu 15.04 64bit (Docker)
  * Nginx 1.7.12
  * PHP-FPM 5.6.4
    * Zend OPcache 7.0.4-dev
    * PhalconPHP 2.0.9
  * PHP-FPM 7.0.0
    * Zend OPcache 7.0.6-dev
    * PhalconPHP 2.0.9
  * HHVM 3.10.1

Running on a MacBook Pro (Retina, 15-inch, Mid 2014).

By sharing underlying software stacks, the benchmark results vary only according to the host machine's hardware specs and differing code implementations.

![Benchmark Results Graph](:large)

## PHP-FPM 5.6.4 with opcode cache

## HHVM CLI 3.10.1 (Corresponding roughly to an up-to-date PHP 5.6)

## PHP CLI 7.0.0 with opcode cache



