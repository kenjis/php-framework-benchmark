# PHP Framework Benchmark

## Results

### Hello World Benchmark

|framework         |request per second |peak memory|
|------------------|------------------:|----------:|
|[Phalcon 1.3](http://phalconphp.com/)             |1100.85|0.5 |
|[CodeIgniter 3.0-dev](http://www.codeigniter.com/)| 609.61|0.5 |
|[Yii 2.0](http://www.yiiframework.com/)           | 345.73|1.5 |
|[FuelPHP 1.8-dev](http://fuelphp.com/)            | 301.11|0.75|
|[CakePHP 3.0-dev](http://cakephp.org/)            | 193.42|1.25|
|[Symfony 2.5](http://symfony.com/)                | 107.86|2   |
|[Laravel 4.2](http://laravel.com/)                |  80.60|2   |

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
