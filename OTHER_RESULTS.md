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

Note: Frameworks that were currently not reporting complete benchmark results are zeroed out in the graphs

#### PHP-FPM 5.6.4 with opcode cache

<img width="1002" alt="php_framework_benchmark" src="https://cloud.githubusercontent.com/assets/793037/11716938/cf4d6c5e-9f55-11e5-90f3-c177a0f6f35c.png">

|framework          |requests per second|relative|peak memory|relative|
|-------------------|------------------:|-------:|----------:|-------:|
|phalcon-2.0        |             965.16|    53.7|       0.27|     1.0|
|ice-1.0            |             887.56|    49.4|       0.26|     1.0|
|tipsy-0.10         |           1,186.60|    66.0|       0.32|     1.2|
|fatfree-3.5        |             549.20|    30.5|       0.43|     1.6|
|slim-2.6           |             839.07|    46.7|       0.48|     1.8|
|ci-3.0             |             114.04|     6.3|       0.43|     1.6|
|nofuss-1.2         |             282.49|    15.7|       0.59|     2.2|
|slim-3.0           |             578.63|    32.2|       0.62|     2.3|
|bear-1.0           |              50.19|     2.8|       0.77|     2.9|
|lumen-5.1          |               0.00|     0.0|       0.00|     0.0|
|ze-1.0             |             270.54|    15.0|       0.80|     3.0|
|radar-1.0-dev      |             271.33|    15.1|       0.71|     2.7|
|yii-2.0            |             316.65|    17.6|       1.36|     5.1|
|silex-1.3          |               0.00|     0.0|       0.00|     0.0|
|cygnite-1.3        |             129.65|     7.2|       0.76|     2.9|
|fuel-1.8-dev       |              56.63|     3.1|       0.71|     2.7|
|phpixie-3.2        |              82.51|     4.6|       1.30|     4.9|
|aura-2.0           |             139.26|     7.7|       0.90|     3.4|
|cake-3.1           |               0.00|     0.0|       0.00|     0.0|
|symfony-2.7        |               0.00|     0.0|       0.00|     0.0|
|laravel-5.1        |               0.00|     0.0|       0.00|     0.0|
|zf-2.5             |              17.98|     1.0|       3.22|    12.2|
|typo3f-3.0         |               0.00|     0.0|       0.00|     0.0|

#### PHP-FPM 7.0.0 with opcode cache

<img width="1002" alt="php_framework_benchmark" src="https://cloud.githubusercontent.com/assets/793037/11716920/c005b09e-9f55-11e5-8cb3-b932a435e725.png">

|framework          |requests per second|relative|peak memory|relative|
|-------------------|------------------:|-------:|----------:|-------:|
|phalcon-2.0        |               0.00|     0.0|       0.00|     0.0|
|ice-1.0            |               0.00|     0.0|       0.00|     0.0|
|tipsy-0.10         |           1,497.81|    35.7|       0.37|     1.0|
|fatfree-3.5        |             664.94|    15.9|       0.44|     1.2|
|slim-2.6           |           1,174.32|    28.0|       0.49|     1.3|
|ci-3.0             |             120.21|     2.9|       0.38|     1.0|
|nofuss-1.2         |             332.31|     7.9|       0.66|     1.8|
|slim-3.0           |             907.03|    21.6|       0.58|     1.6|
|bear-1.0           |               0.00|     0.0|       0.00|     0.0|
|lumen-5.1          |               0.00|     0.0|       0.00|     0.0|
|ze-1.0             |             407.75|     9.7|       0.68|     1.8|
|radar-1.0-dev      |             395.55|     9.4|       0.62|     1.7|
|yii-2.0            |             497.33|    11.9|       1.02|     2.8|
|silex-1.3          |               0.00|     0.0|       0.00|     0.0|
|cygnite-1.3        |             169.69|     4.0|       0.65|     1.8|
|fuel-1.8-dev       |              69.26|     1.7|       0.63|     1.7|
|phpixie-3.2        |             158.76|     3.8|       1.01|     2.7|
|aura-2.0           |             202.28|     4.8|       0.74|     2.0|
|cake-3.1           |               0.00|     0.0|       0.00|     0.0|
|symfony-2.7        |               0.00|     0.0|       0.00|     0.0|
|laravel-5.1        |               0.00|     0.0|       0.00|     0.0|
|zf-2.5             |              41.94|     1.0|       1.88|     5.1|
|typo3f-3.0         |               0.00|     0.0|       0.00|     0.0|

#### HHVM 3.10.1 (Corresponding roughly to an up-to-date PHP 5.6)

<img width="1002" alt="php_framework_benchmark" src="https://cloud.githubusercontent.com/assets/793037/11716924/c83b4724-9f55-11e5-9a3f-a5cf7abf23e4.png">

|framework          |requests per second|relative|peak memory|relative|
|-------------------|------------------:|-------:|----------:|-------:|
|phalcon-2.0        |               0.00|     0.0|       0.00|     0.0|
|ice-1.0            |               0.00|     0.0|       0.00|     0.0|
|tipsy-0.10         |             180.92| 1,292.3|       0.04|     1.0|
|fatfree-3.5        |             200.91| 1,435.1|       0.07|     1.7|
|slim-2.6           |             124.64|   890.3|       0.05|     1.2|
|ci-3.0             |              41.37|   295.5|       0.05|     1.2|
|nofuss-1.2         |             156.60| 1,118.6|       0.05|     1.2|
|slim-3.0           |              38.04|   271.7|       0.07|     1.7|
|bear-1.0           |               0.22|     1.6|       0.13|     3.2|
|lumen-5.1          |              47.54|   339.6|       0.24|     5.9|
|ze-1.0             |              20.85|   148.9|       0.16|     4.0|
|radar-1.0-dev      |              22.95|   163.9|       0.09|     2.2|
|yii-2.0            |              31.43|   224.5|       0.51|    12.6|
|silex-1.3          |              26.85|   191.8|       0.11|     2.7|
|cygnite-1.3        |              10.27|    73.4|       0.18|     4.4|
|fuel-1.8-dev       |              14.93|   106.6|       0.13|     3.2|
|phpixie-3.2        |               0.21|     1.5|       0.31|     7.7|
|aura-2.0           |              21.55|   153.9|       0.17|     4.2|
|cake-3.1           |               4.95|    35.4|       0.18|     4.4|
|symfony-2.7        |               0.16|     1.1|       0.72|    17.8|
|laravel-5.1        |               0.00|     0.0|       0.00|     0.0|
|zf-2.5             |               0.14|     1.0|       0.66|    16.3|
|typo3f-3.0         |               0.00|     0.0|       0.00|     0.0|
