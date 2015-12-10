# PHP ORM Benchmark

## Other Results

### [motin](https://github.com/motin)

(2015-12-10)

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

## PHP-FPM 5.6.4 with opcode cache

![Benchmark Results Graph](:large)

|framework          |requests per second|relative|peak memory|relative|
|-------------------|------------------:|-------:|----------:|-------:|
|phalcon-2.0        |             975.53| 5,419.6|       0.27|     1.0|
|ice-1.0            |             611.45| 3,396.9|       0.26|     1.0|
|tipsy-0.10         |             752.09| 4,178.3|       0.32|     1.2|
|fatfree-3.5        |             363.96| 2,022.0|       0.42|     1.6|
|slim-2.6           |             578.86| 3,215.9|       0.48|     1.8|
|ci-3.0             |              67.70|   376.1|       0.43|     1.6|
|nofuss-1.2         |             226.13| 1,256.3|       0.59|     2.2|
|slim-3.0           |             386.59| 2,147.7|       0.62|     2.4|
|bear-1.0           |               0.20|     1.1|       0.76|     2.9|
|lumen-5.1          |             178.44|   991.3|       0.00|     0.0|
|ze-1.0             |             136.61|   758.9|       0.79|     3.0|
|radar-1.0-dev      |             153.33|   851.8|       0.71|     2.7|
|yii-2.0            |             252.91| 1,405.1|       1.34|     5.1|
|silex-1.3          |             327.53| 1,819.6|       0.00|     0.0|
|cygnite-1.3        |              72.30|   401.7|       0.74|     2.8|
|fuel-1.8-dev       |              21.44|   119.1|       0.70|     2.7|
|phpixie-3.2        |              24.92|   138.4|       1.27|     4.8|
|aura-2.0           |             149.58|   831.0|       0.89|     3.4|
|cake-3.1           |             166.20|   923.3|       0.00|     0.0|
|symfony-2.7        |              51.79|   287.7|       0.00|     0.0|
|laravel-5.1        |              76.21|   423.4|       0.00|     0.0|
|zf-2.5             |              32.31|   179.5|       2.93|    11.1|
|typo3f-3.0         |               0.18|     1.0|       0.00|     0.0|

## PHP CLI 7.0.0 with opcode cache

![Benchmark Results Graph](:large)

|framework          |requests per second|relative|peak memory|relative|
|-------------------|------------------:|-------:|----------:|-------:|
|phalcon-2.0        |           1,295.21| 7,618.9|       0.00|     0.0|
|ice-1.0            |           1,066.86| 6,275.6|       0.00|     0.0|
|tipsy-0.10         |             727.06| 4,276.8|       0.37|     1.0|
|fatfree-3.5        |             405.82| 2,387.2|       0.43|     1.2|
|slim-2.6           |             677.22| 3,983.6|       0.48|     1.3|
|ci-3.0             |              77.41|   455.4|       0.38|     1.0|
|nofuss-1.2         |             275.86| 1,622.7|       0.66|     1.8|
|slim-3.0           |             616.27| 3,625.1|       0.58|     1.6|
|bear-1.0           |              69.47|   408.6|       0.00|     0.0|
|lumen-5.1          |             310.64| 1,827.3|       0.00|     0.0|
|ze-1.0             |             212.23| 1,248.4|       0.67|     1.8|
|radar-1.0-dev      |             196.84| 1,157.9|       0.62|     1.7|
|yii-2.0            |             274.29| 1,613.5|       1.00|     2.7|
|silex-1.3          |             252.21| 1,483.6|       0.00|     0.0|
|cygnite-1.3        |              65.33|   384.3|       0.64|     1.7|
|fuel-1.8-dev       |              31.17|   183.4|       0.62|     1.7|
|phpixie-3.2        |             108.73|   639.6|       0.98|     2.7|
|aura-2.0           |             198.32| 1,166.6|       0.73|     2.0|
|cake-3.1           |             226.73| 1,333.7|       0.00|     0.0|
|symfony-2.7        |             120.97|   711.6|       0.00|     0.0|
|laravel-5.1        |             182.54| 1,073.8|       0.00|     0.0|
|zf-2.5             |              82.60|   485.9|       1.69|     4.6|
|typo3f-3.0         |               0.17|     1.0|       0.00|     0.0|

## HHVM CLI 3.10.1 (Corresponding roughly to an up-to-date PHP 5.6)

![Benchmark Results Graph](:large)

|framework          |requests per second|relative|peak memory|relative|
|-------------------|------------------:|-------:|----------:|-------:|
|phalcon-2.0        |             544.20| 5,442.0|       0.00|     0.0|
|ice-1.0            |             596.84| 5,968.4|       0.00|     0.0|
|tipsy-0.10         |             104.15| 1,041.5|       0.04|     1.0|
|fatfree-3.5        |             109.40| 1,094.0|       0.07|     1.8|
|slim-2.6           |              64.33|   643.3|       0.04|     1.0|
|ci-3.0             |              24.78|   247.8|       0.05|     1.3|
|nofuss-1.2         |              91.14|   911.4|       0.05|     1.3|
|slim-3.0           |              28.95|   289.5|       0.07|     1.8|
|bear-1.0           |               0.23|     2.3|       0.11|     2.8|
|lumen-5.1          |              19.64|   196.4|       0.21|     5.3|
|ze-1.0             |              10.30|   103.0|       0.14|     3.5|
|radar-1.0-dev      |               7.30|    73.0|       0.08|     2.0|
|yii-2.0            |               1.24|    12.4|       0.49|    12.4|
|silex-1.3          |               5.64|    56.4|       0.10|     2.5|
|cygnite-1.3        |               0.65|     6.5|       0.17|     4.3|
|fuel-1.8-dev       |               6.06|    60.6|       0.12|     3.0|
|phpixie-3.2        |               0.19|     1.9|       0.27|     6.8|
|aura-2.0           |               5.67|    56.7|       0.16|     4.0|
|cake-3.1           |               0.28|     2.8|       0.16|     4.0|
|symfony-2.7        |               0.10|     1.0|       0.64|    16.2|
|laravel-5.1        |               0.33|     3.3|       0.00|     0.0|
|zf-2.5             |               0.14|     1.4|       0.58|    14.7|
|typo3f-3.0         |               0.17|     1.7|       0.00|     0.0|
