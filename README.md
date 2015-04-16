# PHP Framework Benchmark

This project attempts to mesure minimum overhead (minimum bootstrap cost) of PHP frameworks in real world.

So I think the minimun applications to benchmark should not include:

* cost of template engine (HTML output)
* cost of database manipulation
* cost of debugging information

Components like Template engine or ORM/Database library are out of scope in this project.

## Benchmarking Policy

This is `optimize` branch.

This branch accepts optimization like:

* removing components/configurations which are not used
* optimized configuration

Caution: Not all the frameworks are optimized!

If you are interested in benchmarking with no optimization/manipulation (removing components/configurations which are not used), See [master](https://github.com/kenjis/php-framework-benchmark/) branch.

If you find something wrong with my code, please feel free to send Pull Requests. But please note optimizing only for "Hello World" is not acceptable. Building fastest "Hello World" application is not the goal in this project.

## Frameworks to Benchmark

* Phalcon 2.0.0-RC1
* CodeIgniter 3.0.0
* Slim 2.6.2
* Yii 2.0.4
* FuelPHP 1.8-dev
* Silex 1.2.4
* BEAR.Sunday 1.0.0-rc3
* CakePHP 3.0.1
* Symfony 2.6.6
* Laravel 5.0.27
* Zend Framework 2.4.0
* TYPO3 Flow 2.3.3
* Lumen 5.0.0

## Results

These are my benchmarks, not yours. I encourage you to run on your environments.

### Benchmarking Environment

* CentOS 6.5 64bit (VM; VirtualBox)
  * PHP 5.5.23 (Remi RPM)
    * Zend OPcache v7.0.4-dev
  * Apache 2.2

### Hello World Benchmark

(2015/04/15)

|framework          |requests per second|relative|peak memory|relative|
|-------------------|------------------:|-------:|----------:|-------:|
|phalcon-2.0        |           1,425.55|    32.2|       0.50|     1.0|
|slim-2.6           |             769.47|    17.4|       0.50|     1.0|
|codeigniter-3.0    |             716.95|    16.2|       0.50|     1.0|
|yii-2.0            |             367.26|     8.3|       1.75|     3.5|
|silex-1.2          |             351.59|     8.0|       1.00|     2.0|
|lumen-5.0          |             361.34|     8.2|       1.00|     2.0|
|bear-1.0           |             345.60|     7.8|       1.00|     2.0|
|fuel-1.8-dev       |             328.28|     7.4|       0.75|     1.5|
|cake-3.0        (*)|             350.60|     7.9|       1.00|     2.0|
|symfony-2.6     (*)|             210.75|     4.8|       2.00|     4.0|
|laravel-5.0        |              73.00|     1.7|       2.75|     5.5|
|zf-2.4             |              69.73|     1.6|       3.00|     6.0|
|typo3-flow-2.3     |              44.21|     1.0|       5.25|    10.5|

Note(1): All the results are run on php with phalcon.so. If you don't load phalcon.so, the rps except for Phalcon probably increase.

Note(2): Only frameworks with (*) mark are optimized. Other frameworks are not optimized yet.

#### Comparision before and after optimization

|framework          |requests per second|relative|peak memory|relative|
|-------------------|------------------:|-------:|----------:|-------:|
|cake-3.0           |             264.58|     1.0|       1.25|     1.3|
|cake-3.0 (*)       |             350.60|     1.3|       1.00|     1.0|
|symfony-2.6        |             123.81|     1.0|       3.00|     1.5|
|symfony-2.6 (*)    |             210.75|     1.7|       2.00|     1.0|

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

## Linux Kernel Configuration

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
* [Zend Framework](http://framework.zend.com/)
* [TYPO3 Flow](http://flow.typo3.org/)
* [Lumen](http://lumen.laravel.com/)

## Related

* [PHP ORM Benchmark](https://github.com/kenjis/php-orm-benchmark)
* [PHP User Agent Parser Benchmarks](https://github.com/kenjis/user-agent-parser-benchmarks)
