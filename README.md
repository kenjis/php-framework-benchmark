# PHP Framework Benchmark

This project attempts to mesure minimum overhead (minimum bootstrap cost) of PHP frameworks in real world.

So I think the minimun applications to benchmark should not include:

* cost of template engine (HTML output)
* cost of database manipulation
* cost of debugging information

Components like Template engine or ORM/Database library are out of scope in this project.

If you find something wrong with my code, please feel free to send Pull Requests. But please note optimizing only for "Hello World" is not acceptable. Building fastest "Hello World" application is not the goal in this project.

## Frameworks to Benchmark

* Phalcon 1.3.4
* CodeIgniter 3.0-rc3
* Slim 2.6.2
* Yii 2.0.4
* FuelPHP 1.8-dev
* Silex 1.2.3
* BEAR.Sunday 1.0.0-rc2
* CakePHP 3.0.0
* Symfony 2.6.5
* Laravel 5.0.20

## Results

### Hello World Benchmark

(2015/03/30)

|framework          |requests per second|relative|peak memory|relative|
|-------------------|------------------:|-------:|----------:|-------:|
|phalcon-1.3        |           1,622.50|    20.8|       0.50|     1.0|
|codeigniter-3.0    |             727.46|     9.3|       0.50|     1.0|
|slim-2.6           |             799.06|    10.2|       0.50|     1.0|
|yii-2.0            |             383.81|     4.9|       1.50|     3.0|
|fuel-1.8-dev       |             312.29|     4.0|       0.75|     1.5|
|silex-1.2          |             352.96|     4.5|       1.00|     2.0|
|bear-1.0           |             357.58|     4.6|       1.00|     2.0|
|cake-3.0           |             256.58|     3.3|       1.00|     2.0|
|symfony-2.6        |             269.91|     3.5|       1.00|     2.0|
|laravel-5.0        |              78.09|     1.0|       2.75|     5.5|

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

## Kernel Configuration

I added below in `/etc/sysctl.conf`

~~~
# Added
net.netfilter.nf_conntrack_max = 100000
net.nf_conntrack_max = 100000
net.ipv4.tcp_max_tw_buckets = 180000
net.ipv4.tcp_tw_recycle = 1
net.ipv4.tcp_tw_reuse = 1
net.ipv4.tcp_fin_timeout = 10
~~~

and run `sudo sysctl -p`.

If you want to see current configuration, run `sudo sysctl -a`.

## Reference

* [Phalcon](http://phalconphp.com/)
* [CodeIgniter](http://www.codeigniter.com/)
* [Slim](http://www.slimframework.com/)
* [Yii](http://www.yiiframework.com/)
* [FuelPHP](http://fuelphp.com/)
* [Silex](http://silex.sensiolabs.org/)
* [BEAR.Sunday](https://bearsunday.github.io/)
* [CakePHP](http://cakephp.org/)
* [Symfony](http://symfony.com/)
* [Laravel](http://laravel.com/)

## Related

* [PHP ORM Benchmark](https://github.com/kenjis/php-orm-benchmark)
* [PHP User Agent Parser Benchmarks](https://github.com/kenjis/user-agent-parser-benchmarks)
