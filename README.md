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
* Use controller or action class if a framework has the functionality.

Some people may think using default configuration is not fair. But I think a framework's default configuration is an assertion of what it is. Default configuration is a good starting point to know a framework. And I can't optimize all the frameworks. Some frameworks are optimized, some are not, it is not fair. So I don't remove any components/configurations.

But if you are interested in benchmarking with optimization (removing components/configurations which are not used), See [optimize](https://github.com/kenjis/php-framework-benchmark/tree/optimize) branch.

If you find something wrong with my code, please feel free to send Pull Requests. But please note optimizing only for "Hello World" is not acceptable. Building fastest "Hello World" application is not the goal in this project.

## Frameworks to Benchmark

1. Phalcon 2.0.8
1. Ice 1.0.34
1. FatFree 3.5.0
1. Slim 2.6.2
1. CodeIgniter 3.0.2
1. NoFussFramework 1.2.3
1. Slim 3.0.0-RC1
1. BEAR.Sunday 1.0.1
1. Lumen 5.1.5
1. zend-expressive 1.0.0rc2 (FastRoute + zend-servicemanager)
1. Radar 1.0.0-dev
1. Yii 2.0.6
1. Silex 1.3.4
1. Cygnite 1.3.1
1. FuelPHP 1.8-dev
1. PHPixie 3.2
1. Aura 2.0.2
1. CakePHP 3.1.2
1. Symfony 2.7.5
1. Laravel 5.1.19
1. Zend Framework 2.5.2
1. TYPO3 Flow 3.0.0

## Results

### Benchmarking Environment

* CentOS 6.6 64bit (VM; VirtualBox)
  * PHP 5.5.30 (Remi RPM)
    * Zend OPcache v7.0.4-dev
  * Apache 2.2

### Hello World Benchmark

These are my benchmarks, not yours. **I encourage you to run on your environments.**

(2015/10/28)

![Benchmark Results Graph](https://pbs.twimg.com/media/CSXXvggUYAA74_j.png)

|framework          |requests per second|relative|peak memory|relative|
|-------------------|------------------:|-------:|----------:|-------:|
|phalcon-2.0        |           1,470.10|    56.0|       0.50|     1.0|
|ice-1.0            |           1,230.86|    46.9|       0.50|     1.0|
|fatfree-3.5        |             948.76|    36.2|       0.50|     1.0|
|slim-2.6           |             749.78|    28.6|       0.50|     1.0|
|ci-3.0             |             687.84|    26.2|       0.50|     1.0|
|nofuss-1.2         |             596.83|    22.7|       0.50|     1.0|
|slim-3.0           |             534.92|    20.4|       0.75|     1.5|
|bear-1.0           |             418.27|    15.9|       1.00|     2.0|
|lumen-5.1          |             387.59|    14.8|       1.00|     2.0|
|ze-1.0             |             353.47|    13.5|       1.00|     2.0|
|radar-1.0-dev      |             355.74|    13.6|       0.75|     1.5|
|yii-2.0            |             351.31|    13.4|       1.75|     3.5|
|silex-1.3          |             316.28|    12.1|       1.00|     2.0|
|cygnite-1.3        |             324.87|    12.4|       1.00|     2.0|
|fuel-1.8-dev       |             301.30|    11.5|       0.75|     1.5|
|phpixie-3.2        |             253.21|     9.6|       1.50|     3.0|
|aura-2.0           |             198.16|     7.6|       1.00|     2.0|
|cake-3.1           |             202.34|     7.7|       1.50|     3.0|
|symfony-2.7        |              88.22|     3.4|       3.25|     6.5|
|laravel-5.1        |              80.09|     3.1|       2.75|     5.5|
|zf-2.5             |              69.57|     2.7|       3.25|     6.5|
|typo3f-3.0         |              26.24|     1.0|       6.75|    13.5|

Note(1): All the results are run on php with `phalcon.so` and `ice.so`. If you don't load phalcon.so or ice.so, the rps except for Phalcon or Ice probably increase a bit.

## How to Benchmark

Install source code as <http://localhost/php-framework-benchmark/>:

~~~
$ git clone https://github.com/kenjis/php-framework-benchmark.git
$ cd php-framework-benchmark
$ sh setup.sh
~~~

Run benchmarks:

~~~
$ sh benchmark.sh
~~~

See <http://localhost/php-framework-benchmark/>.

If you want to benchmark some frameworks:

~~~
$ sh setup.sh fatfree-3.5/ slim-3.0/ lumen-5.1/ silex-1.3/
$ sh benchmark.sh fatfree-3.5/ slim-3.0/ lumen-5.1/ silex-1.3/
~~~

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

## Apache Virtual Host Configuration

~~~
<VirtualHost *:80>
  DocumentRoot /home/vagrant/public
</VirtualHost>
~~~

## Reference

* [Phalcon](http://phalconphp.com/) ([@phalconphp](https://twitter.com/phalconphp))
* [Ice](http://www.iceframework.org/) ([@iceframework](https://twitter.com/iceframework))
  * See https://github.com/kenjis/php-framework-benchmark/pull/17#issuecomment-98244668
* [FatFree](http://fatfreeframework.com/) ([@phpfatfree](https://twitter.com/phpfatfree))
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
* [PHPixie](http://phpixie.com/) ([@phpixie](https://twitter.com/phpixie))
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
