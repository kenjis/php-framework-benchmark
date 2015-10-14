# PHP Framework Benchmark

This project attempts to mesure minimum overhead (minimum bootstrap cost) of PHP frameworks in real world.

So I think the minimun applications to benchmark should not include:

* cost of template engine (HTML output)
* cost of database manipulation
* cost of debugging information

Components like Template engine or ORM/Database library are out of scope in this project.

## Benchmarking Policy

This is `master` branch.

* Install a framework according to the official documentation.
* Use the default configuration.
  * Don't remove any components/configurations even if they are not used.
  * With minimum changes to run this benchmark.
* Set environment production/Turn off debug mode.
* Run optimization which you normally do in your production environment, like composer's `--optimize-autoloader`.
* Use controller class if a framework supports.

Some people may think using default configuration is not fair. But I think a framework's default configuration is an assertion of what it is. Default configuration is a good starting point to know a framework. And I can't optimize all the frameworks. Some frameworks are optimized, some are not, it is not fair. So I don't remove any components/configurations.

But if you are interested in benchmarking with optimization (removing components/configurations which are not used), See [optimize](https://github.com/kenjis/php-framework-benchmark/tree/optimize) branch.

If you find something wrong with my code, please feel free to send Pull Requests. But please note optimizing only for "Hello World" is not acceptable. Building fastest "Hello World" application is not the goal in this project.

## Frameworks to Benchmark

* Phalcon 2.0.8
* Ice 1.0.13
* Slim 2.6.2
* Slim 3.0-RC1
* CodeIgniter 3.0.0
* Lumen 5.0.8
* Yii 2.0.4
* Silex 1.2.5
* Cygnite 1.3.1
* BEAR.Sunday 1.0.1
* FuelPHP 1.8-dev
* CakePHP 3.0.14
* Aura 2.0.2
* Symfony 2.7.5
* Laravel 5.1.19
* Zend Framework 2.4.0
* TYPO3 Flow 2.3.3

## Results

### Benchmarking Environment

* CentOS 6.6 64bit (VM; VirtualBox)
  * PHP 5.5.30 (Remi RPM)
    * Zend OPcache v7.0.4-dev
  * Apache 2.2

### Hello World Benchmark

These are my benchmarks, not yours. **I encourage you to run on your environments.**

(2015/10/14)

|framework          |requests per second|relative|peak memory|relative|
|-------------------|------------------:|-------:|----------:|-------:|
|phalcon-2.0        |           1,418.87|    35.8|       0.50|     1.0|
|ice-1.0            |           1,182.30|    29.9|       0.50|     1.0|
|slim-2.6           |             795.78|    20.1|       0.50|     1.0|
|codeigniter-3.0    |             719.76|    18.2|       0.50|     1.0|
|slim-3.0           |             544.03|    13.7|       0.75|     1.5|
|bear-1.0           |             449.94|    11.4|       1.00|     2.0|
|yii-2.0            |             368.59|     9.3|       1.75|     3.5|
|silex-1.2          |             354.93|     9.0|       1.00|     2.0|
|cygnite-1.3        |             354.57|     9.0|       0.75|     1.5|
|lumen-5.0          |             350.33|     8.8|       1.25|     2.5|
|fuel-1.8-dev       |             310.30|     7.8|       0.75|     1.5|
|cake-3.0           |             262.46|     6.6|       1.25|     2.5|
|aura-2.0           |             210.57|     5.3|       1.00|     2.0|
|symfony-2.7        |              95.72|     2.4|       3.50|     7.0|
|laravel-5.1        |              82.22|     2.1|       2.75|     5.5|
|zf-2.4             |              69.53|     1.8|       3.25|     6.5|
|typo3-flow-2.3     |              39.59|     1.0|       5.25|    10.5|

Note(1): All the results are run on php with `phalcon.so` and `ice.so`. If you don't load phalcon.so or ice.so, the rps except for Phalcon or Ice probably increase a bit.

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
* [Ice](http://www.iceframework.org/)
  * See https://github.com/kenjis/php-framework-benchmark/pull/17#issuecomment-98244668
* [Slim](http://www.slimframework.com/)
* [CodeIgniter](http://www.codeigniter.com/)
* [Lumen](http://lumen.laravel.com/)
* [Yii](http://www.yiiframework.com/)
* [Silex](http://silex.sensiolabs.org/)
* [Cygnite](http://www.cygniteframework.com/)
* [BEAR.Sunday](https://bearsunday.github.io/)
* [FuelPHP](http://fuelphp.com/)
* [CakePHP](http://cakephp.org/)
* [Aura](http://auraphp.com/)
* [Symfony](http://symfony.com/)
  * [How to Deploy a Symfony Application](http://symfony.com/doc/current/cookbook/deployment/tools.html)
* [Laravel](http://laravel.com/)
* [Zend Framework](http://framework.zend.com/)
* [TYPO3 Flow](http://flow.typo3.org/)

## Related

* [PHP ORM Benchmark](https://github.com/kenjis/php-orm-benchmark)
* [PHP User Agent Parser Benchmarks](https://github.com/kenjis/user-agent-parser-benchmarks)
