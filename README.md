# PHP Framework Benchmark

This project attempts to measure minimum overhead (minimum bootstrap cost) of PHP frameworks in real world.

So I think the minimum applications to benchmark should not include:

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

1. Phalcon 2.0.8
1. Ice 1.0.34
1. Slim 2.6.2
1. CodeIgniter 3.0.0
1. NoFussFramework 1.2.3
1. Slim 3.0.0-RC1
1. BEAR.Sunday 1.0.1
1. zend-expressive 1.0.0rc2 (FastRoute + zend-servicemanager)
1. Radar 1.0.0-dev
1. Yii 2.0.6
1. Lumen 5.0.8
1. Silex 1.3.4
1. Cygnite 1.3.1
1. FuelPHP 1.8-dev
1. Aura 2.0.2
1. CakePHP 3.1.1
1. Symfony 2.7.5
1. Laravel 5.1.19
1. Zend Framework 2.4.0
1. TYPO3 Flow 2.3.6

## Results

### Benchmarking Environment

* CentOS 6.6 64bit (VM; VirtualBox)
  * PHP 5.5.30 (Remi RPM)
    * Zend OPcache v7.0.4-dev
  * Apache 2.2

### Hello World Benchmark

These are my benchmarks, not yours. **I encourage you to run on your environments.**

(2015/10/22)

![Benchmark Results Graph](https://pbs.twimg.com/media/CR6F0mKUYAAzOJE.png)

|framework          |requests per second|relative|peak memory|relative|
|-------------------|------------------:|-------:|----------:|-------:|
|phalcon-2.0        |           1,523.55|    38.2|       0.50|     1.0|
|ice-1.0            |           1,275.87|    32.0|       0.50|     1.0|
|slim-2.6           |             812.56|    20.4|       0.50|     1.0|
|codeigniter-3.0    |             732.62|    18.4|       0.50|     1.0|
|nofuss-1.2         |             632.46|    15.9|       0.50|     1.0|
|slim-3.0           |             567.68|    14.2|       0.75|     1.5|
|bear-1.0           |             472.28|    11.9|       1.00|     2.0|
|ze-1.0             |             395.13|     9.9|       1.00|     2.0|
|radar-1.0-dev      |             376.21|     9.4|       0.75|     1.5|
|yii-2.0            |             393.89|     9.9|       1.75|     3.5|
|lumen-5.0          |             363.65|     9.1|       1.25|     2.5|
|silex-1.3          |             356.55|     8.9|       1.00|     2.0|
|cygnite-1.3        |             353.72|     8.9|       1.00|     2.0|
|fuel-1.8-dev       |             321.57|     8.1|       0.75|     1.5|
|aura-2.0           |             210.10|     5.3|       1.00|     2.0|
|cake-3.1           |             215.51|     5.4|       1.50|     3.0|
|symfony-2.7        |             100.22|     2.5|       3.25|     6.5|
|laravel-5.1        |              95.87|     2.4|       2.75|     5.5|
|zf-2.4             |              72.16|     1.8|       3.25|     6.5|
|typo3-flow-2.3     |              39.85|     1.0|       5.50|    11.0|

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

* [Phalcon](http://phalconphp.com/) ([@phalconphp](https://twitter.com/phalconphp))
* [Ice](http://www.iceframework.org/) ([@iceframework](https://twitter.com/iceframework))
  * See https://github.com/kenjis/php-framework-benchmark/pull/17#issuecomment-98244668
* [Slim](http://www.slimframework.com/) ([@slimphp](https://twitter.com/slimphp))
* [CodeIgniter](http://www.codeigniter.com/) ([@CodeIgniter](https://twitter.com/CodeIgniter))
* [NoFussFramework](http://www.nofussframework.com/)
* [BEAR.Sunday](https://bearsunday.github.io/) ([@BEARSunday](https://twitter.com/BEARSunday))
* [zend-expressive](https://github.com/zendframework/zend-expressive) ([@zfdevteam](https://twitter.com/zfdevteam))
* [Radar](https://github.com/radarphp/Radar.Project)
* [Yii](http://www.yiiframework.com/) ([@yiiframework](https://twitter.com/yiiframework))
* [Lumen](http://lumen.laravel.com/)
* [Silex](http://silex.sensiolabs.org/)
* [Cygnite](http://www.cygniteframework.com/) ([@cygnitephp](https://twitter.com/cygnitephp))
* [FuelPHP](http://fuelphp.com/) ([@fuelphp](https://twitter.com/fuelphp))
* [Aura](http://auraphp.com/) ([@auraphp](https://twitter.com/auraphp))
* [CakePHP](http://cakephp.org/) ([@cakephp](https://twitter.com/cakephp))
* [Symfony](http://symfony.com/) ([@symfony](https://twitter.com/symfony))
  * [How to Deploy a Symfony Application](http://symfony.com/doc/current/cookbook/deployment/tools.html)
* [Laravel](http://laravel.com/) ([@laravelphp](https://twitter.com/laravelphp))
* [Zend Framework](http://framework.zend.com/) ([@zfdevteam](https://twitter.com/zfdevteam))
* [TYPO3 Flow](http://flow.typo3.org/) ([@neoscms](https://twitter.com/neoscms))

## Related

* [PHP ORM Benchmark](https://github.com/kenjis/php-orm-benchmark)
* [PHP User Agent Parser Benchmarks](https://github.com/kenjis/user-agent-parser-benchmarks)
