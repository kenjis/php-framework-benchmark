# PHP Framework Benchmark

## Results

|framework         |request per second |peak memory|
|------------------|------------------:|----------:|
|[CodeIgniter 3.0-dev](http://www.codeigniter.com/)|610.77|0.5 |
|[Yii 2.0](http://www.yiiframework.com/)           |344.38|1.5 |
|[FuelPHP 1.8-dev](http://fuelphp.com/)            |289.71|0.75|
|[CakePHP 3.0-dev](http://cakephp.org/)            |177.52|1.25|
|[Symfony 2.5](http://symfony.com/)                |110.31|2   |
|[Laravel 4.2](http://laravel.com/)                | 72.43|2   |

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
