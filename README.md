# PHP Framework Benchmark

## Results

### Hello World Benchmark

|framework          |request per second|peak memory|
|-------------------|-----------------:|----------:|
|phalcon-1.3        |           1100.85|        0.5|
|codeigniter-3.0-dev|            609.61|        0.5|
|yii-2.0            |            345.73|        1.5|
|fuel-1.8-dev       |            301.11|       0.75|
|cake-3.0-dev       |            193.42|       1.25|
|symfony-2.5        |            107.86|          2|
|laravel-4.2        |              80.6|          2|

## How to Benchmark

Install source code as <http://localhost/php-framework-benchmark/>.

~~~
$ git clone https://github.com/kenjis/php-framework-benchmark.git
$ cd php-framework-benchmark
$ sh setup.sh
~~~

Run benchmarks.

~~~
$ sh benchmark.sh
~~~

See <http://localhost/php-framework-benchmark/>.

## Reference

* [Phalcon](http://phalconphp.com/)
* [CodeIgniter](http://www.codeigniter.com/)
* [Yii](http://www.yiiframework.com/)
* [FuelPHP](http://fuelphp.com/)
* [CakePHP](http://cakephp.org/)
* [Symfony](http://symfony.com/)
* [Laravel](http://laravel.com/)
