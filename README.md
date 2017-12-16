# PHP Framework Benchmark

This project attempts to measure minimum overhead (minimum bootstrap cost) of PHP frameworks in the real world.

So I think the minimum applications to benchmark should not include:

* cost of template engine (HTML output)
* cost of database manipulation
* cost of debugging information

Components like Template engine or ORM/Database libraries are out of scope in this project.

## Benchmarking Policy

This is `master` branch.

* Install a framework according to the official documentation.
* Use the default configuration.
  * Don't remove any components/configurations even if they are not used.
  * With minimum changes to run this benchmark.
* Set environment production/Turn off debug mode.
* Run optimization which you normally do in your production environment, like Composer's `--optimize-autoloader`.
* Use controller or action class if a framework has the functionality.

Some people may think using default configuration is not fair. But I think a framework's default configuration is an assertion of what it is. Default configuration is a good starting point to know a framework. And I can't optimize all the frameworks. Some frameworks are optimized, some are not, it is not fair. So I don't remove any components/configurations.

But if you are interested in benchmarking with optimization (removing components/configurations which are not used), See [optimize](https://github.com/kenjis/php-framework-benchmark/tree/optimize) branch.

If you find something wrong with my code, please feel free to send Pull Requests. But please note optimizing only for "Hello World" is not acceptable. Building fastest "Hello World" application is not the goal in this project.

## Results

### Benchmarking Environment

* CentOS 6.8 64bit (VM; VirtualBox)
  * PHP 5.6.30 (Remi RPM)
    * Zend OPcache v7.0.6-dev
  * Apache 2.2

### Hello World Benchmark

These are my benchmarks, not yours. **I encourage you to run on your (production equivalent) environments.**

(2017/02/14)

![Benchmark Results Graph](img/php-framework-benchmark-20170214.png)

|framework          |requests per second|relative|peak memory|relative|
|-------------------|------------------:|-------:|----------:|-------:|
|siler-0.6          |           2,069.69|    20.3|       0.25|     1.0|
|kumbia-1.0-dev     |           1,753.60|    17.2|       0.29|     1.2|
|staticphp-0.9      |           1,665.28|    16.3|       0.27|     1.1|
|phalcon-2.0        |           1,618.39|    15.9|       0.26|     1.1|
|tipsy-0.10         |           1,376.97|    13.5|       0.32|     1.3|
|fatfree-3.5        |             965.16|     9.5|       0.41|     1.7|
|ci-3.0             |             753.09|     7.4|       0.42|     1.7|
|nofuss-1.2         |             667.24|     6.5|       0.40|     1.6|
|slim-3.0           |             550.43|     5.4|       0.61|     2.5|
|bear-1.0           |             502.52|     4.9|       0.73|     3.0|
|lumen-5.1          |             415.57|     4.1|       0.85|     3.5|
|yii-2.0            |             410.08|     4.0|       1.32|     5.4|
|ze-1.0             |             403.34|     4.0|       0.75|     3.1|
|cygnite-1.3        |             369.12|     3.6|       0.71|     2.9|
|fuel-1.8           |             344.26|     3.4|       0.63|     2.6|
|silex-2.0          |             342.81|     3.4|       0.78|     3.2|
|phpixie-3.2        |             267.24|     2.6|       1.25|     5.1|
|aura-2.0           |             233.54|     2.3|       0.88|     3.6|
|cake-3.2           |             174.91|     1.7|       1.95|     7.9|
|zf-3.0             |             133.87|     1.3|       2.24|     9.1|
|symfony-3.0        |             131.50|     1.3|       2.18|     8.9|
|laravel-5.3        |             101.94|     1.0|       2.83|    11.5|

Note(1): All the results are run on php with `phalcon.so` and `ice.so`. If you don't load phalcon.so or ice.so, the rps except for Phalcon or Ice probably increase a bit.

Note(2): This benchmarks are limited by `ab` performance. See [#62](https://github.com/kenjis/php-framework-benchmark/issues/62).

## How to Benchmark

If you want to benchmark PHP extension frameworks like Phalcon, you need to install the extenstions.

Install source code as <http://localhost/php-framework-benchmark/>:

~~~
$ git clone https://github.com/kenjis/php-framework-benchmark.git
$ cd php-framework-benchmark
$ bash setup.sh
~~~

Run benchmarks:

~~~
$ bash benchmark.sh
~~~

See <http://localhost/php-framework-benchmark/>.

If you want to benchmark some frameworks:

~~~
$ bash setup.sh fatfree-3.5/ slim-3.0/ lumen-5.1/ silex-1.3/
$ bash benchmark.sh fatfree-3.5/ slim-3.0/ lumen-5.1/ silex-1.3/
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

## References

* [Aura](http://auraphp.com/) ([@auraphp](https://twitter.com/auraphp))
* [BEAR.Sunday](https://bearsunday.github.io/) ([@BEARSunday](https://twitter.com/BEARSunday))
* [CakePHP](http://cakephp.org/) ([@cakephp](https://twitter.com/cakephp))
* [CodeIgniter](http://www.codeigniter.com/) ([@CodeIgniter](https://twitter.com/CodeIgniter))
* [Cygnite](http://www.cygniteframework.com/) ([@cygnitephp](https://twitter.com/cygnitephp))
* [FatFree](http://fatfreeframework.com/) ([@phpfatfree](https://twitter.com/phpfatfree))
* [FuelPHP](http://fuelphp.com/) ([@fuelphp](https://twitter.com/fuelphp))
* [Ice](http://www.iceframework.org/) ([@iceframework](https://twitter.com/iceframework)) [PHP extension]
  * See https://github.com/kenjis/php-framework-benchmark/pull/17#issuecomment-98244668
* [KumbiaPHP](https://github.com/KumbiaPHP/KumbiaPHP) ([@KumbiaPHP](https://twitter.com/KumbiaPHP))
  * [Install KumbiaPHP](https://github.com/KumbiaPHP/Documentation/blob/master/en/to-install.md#instalar-kumbiaphp)
* [Laravel](http://laravel.com/) ([@laravelphp](https://twitter.com/laravelphp))
* [Lumen](http://lumen.laravel.com/)
* [NoFussFramework](http://www.nofussframework.com/)
* [Phalcon](http://phalconphp.com/) ([@phalconphp](https://twitter.com/phalconphp)) [PHP extension]
  * [Installation](https://docs.phalconphp.com/en/latest/reference/install.html)
* [PHPixie](http://phpixie.com/) ([@phpixie](https://twitter.com/phpixie))
* [Radar](https://github.com/radarphp/Radar.Project)
* [Siler](https://github.com/leocavalcante/siler)
* [Silex](http://silex.sensiolabs.org/)
* [Slim](http://www.slimframework.com/) ([@slimphp](https://twitter.com/slimphp))
* [StaticPHP](https://github.com/gintsmurans/staticphp)
* [Symfony](http://symfony.com/) ([@symfony](https://twitter.com/symfony))
  * [How to Deploy a Symfony Application](http://symfony.com/doc/current/cookbook/deployment/tools.html)
* [Tipsy](http://tipsy.la)
* [Flow-Framework](https://flow.neos.io) ([@neoscms](https://twitter.com/neoscms))
* [Yii](http://www.yiiframework.com/) ([@yiiframework](https://twitter.com/yiiframework))
* [zend-expressive](https://github.com/zendframework/zend-expressive) ([@zfdevteam](https://twitter.com/zfdevteam))
* [Zend Framework](http://framework.zend.com/) ([@zfdevteam](https://twitter.com/zfdevteam))

## Other Benchmarks

* [PHP ORM Benchmark](https://github.com/kenjis/php-orm-benchmark)
* [PHP User Agent Parser Benchmarks](https://github.com/kenjis/user-agent-parser-benchmarks)
