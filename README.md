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
1. Tipsy 0.10.3
1. FatFree 3.5.0
1. Slim 2.6.2
1. CodeIgniter 3.0.2
1. NoFussFramework 1.2.3
1. Slim 3.0.0-RC2
1. BEAR.Sunday 1.0.1
1. Lumen 5.1.6
1. zend-expressive 1.0.0rc2 (FastRoute + zend-servicemanager)
1. Radar 1.0.0-dev
1. Yii 2.0.6
1. Silex 1.3.4
1. Cygnite 1.3.1
1. FuelPHP 1.8-dev
1. PHPixie 3.2
1. Aura 2.0.2
1. CakePHP 3.1.4
1. Symfony 2.7.7
1. Laravel 5.1.24
1. Zend Framework 2.5.2
1. TYPO3 Flow 3.0.0

## Results

### Benchmarking Environment

* CentOS 6.6 64bit (VM; VirtualBox)
  * PHP 5.5.30 (Remi RPM)
    * Zend OPcache v7.0.4-dev
  * Apache 2.2

### Hello World Benchmark

These are my benchmarks, not yours. **I encourage you to run on your (production equivalent) environments.**

(2015/11/29)

![Benchmark Results Graph](https://pbs.twimg.com/media/CU9dNeqUwAEbcod.png:large)

|framework          |requests per second|relative|peak memory|relative|
|-------------------|------------------:|-------:|----------:|-------:|
|phalcon-2.0        |           1,746.90|    64.7|       0.27|     1.0|
|ice-1.0            |           1,454.79|    53.9|       0.26|     1.0|
|tipsy-0.10         |           1,425.66|    52.8|       0.32|     1.2|
|fatfree-3.5        |           1,106.20|    41.0|       0.42|     1.6|
|slim-2.6           |             880.24|    32.6|       0.47|     1.8|
|ci-3.0             |             810.99|    30.1|       0.43|     1.7|
|nofuss-1.2         |             672.16|    24.9|       0.40|     1.5|
|slim-3.0           |             534.16|    19.8|       0.61|     2.4|
|bear-1.0           |             442.63|    16.4|       0.76|     2.9|
|lumen-5.1          |             412.36|    15.3|       0.95|     3.7|
|ze-1.0             |             391.97|    14.5|       0.80|     3.1|
|radar-1.0-dev      |             369.79|    13.7|       0.70|     2.7|
|yii-2.0            |             379.77|    14.1|       1.37|     5.3|
|silex-1.3          |             383.66|    14.2|       0.86|     3.3|
|cygnite-1.3        |             385.16|    14.3|       0.76|     2.9|
|fuel-1.8-dev       |             346.33|    12.8|       0.65|     2.5|
|phpixie-3.2        |             236.58|     8.8|       1.31|     5.1|
|aura-2.0           |             233.80|     8.7|       0.89|     3.5|
|cake-3.1           |             207.27|     7.7|       1.37|     5.3|
|symfony-2.7        |             101.99|     3.8|       3.21|    12.4|
|laravel-5.1        |              91.59|     3.4|       2.76|    10.7|
|zf-2.5             |              81.13|     3.0|       3.02|    11.7|
|typo3f-3.0         |              26.98|     1.0|       6.50|    25.2|

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
  SetEnv php_framework_benchmark_path /home/vagrant/public/php-framework-benchmark
</VirtualHost>
~~~

## Benchmarking using the supplied Docker Stack

Use the supplied Docker Stack in order to automatically set up the following benchmarking environments:

* Ubuntu 15.04 64bit (Docker)
  * Nginx 1.7.12
  * PHP-FPM 5.6.4
    * Zend OPcache 7.0.4-dev
    * PhalconPHP 2.0.9
  * PHP-FPM 7.0.0
    * Zend OPcache 7.0.6-dev
    * PhalconPHP 2.0.9
  * HHVM 3.10.1

By sharing underlying software stacks, the benchmark results vary only according to the host machine's hardware specs and differing code implementations.

### Getting Started

If running locally, install [Docker Toolbox](https://www.docker.com/docker-toolbox).

Clone the source code:
~~~
git clone https://github.com/kenjis/php-framework-benchmark.git
cd php-framework-benchmark
~~~

Cd into the docker directory of this repo and make sure that docker toolbox is available:
~~~
cd docker
eval "$(docker-machine env default)"
~~~

Start the Nginx/PHP server stacks:
~~~
docker-compose up -d
~~~

Start the supplied docker shell from within this repository's `docker` folder:
~~~
docker-compose run shell
~~~

Run the set-up script:
~~~
sh setup.sh
~~~

Run benchmarks against each stack:
~~~
stack=docker_nginx_php_5_6_4 sh benchmark.sh
stack=docker_nginx_hhvm_3_10_1 sh benchmark.sh
stack=docker_nginx_php_7_0_0 sh benchmark.sh
~~~

### Check the results

To see the results graph, run the following script from outside the docker shell, from the repository root:

~~~
bin/docker-urls.sh
~~~

It echoes URLs, which you should open up in your browser.

## References

* [Aura](http://auraphp.com/) ([@auraphp](https://twitter.com/auraphp))
* [BEAR.Sunday](https://bearsunday.github.io/) ([@BEARSunday](https://twitter.com/BEARSunday))
* [CakePHP](http://cakephp.org/) ([@cakephp](https://twitter.com/cakephp))
* [CodeIgniter](http://www.codeigniter.com/) ([@CodeIgniter](https://twitter.com/CodeIgniter))
* [Cygnite](http://www.cygniteframework.com/) ([@cygnitephp](https://twitter.com/cygnitephp))
* [FatFree](http://fatfreeframework.com/) ([@phpfatfree](https://twitter.com/phpfatfree))
* [FuelPHP](http://fuelphp.com/) ([@fuelphp](https://twitter.com/fuelphp))
* [Ice](http://www.iceframework.org/) ([@iceframework](https://twitter.com/iceframework))
  * See https://github.com/kenjis/php-framework-benchmark/pull/17#issuecomment-98244668
* [Laravel](http://laravel.com/) ([@laravelphp](https://twitter.com/laravelphp))
* [Lumen](http://lumen.laravel.com/)
* [NoFussFramework](http://www.nofussframework.com/)
* [Phalcon](http://phalconphp.com/) ([@phalconphp](https://twitter.com/phalconphp))
* [PHPixie](http://phpixie.com/) ([@phpixie](https://twitter.com/phpixie))
* [Radar](https://github.com/radarphp/Radar.Project)
* [Silex](http://silex.sensiolabs.org/)
* [Slim](http://www.slimframework.com/) ([@slimphp](https://twitter.com/slimphp))
* [Symfony](http://symfony.com/) ([@symfony](https://twitter.com/symfony))
  * [How to Deploy a Symfony Application](http://symfony.com/doc/current/cookbook/deployment/tools.html)
* [Tipsy](http://tipsy.la)
* [TYPO3 Flow](http://flow.typo3.org/) ([@neoscms](https://twitter.com/neoscms))
* [Yii](http://www.yiiframework.com/) ([@yiiframework](https://twitter.com/yiiframework))
* [zend-expressive](https://github.com/zendframework/zend-expressive) ([@zfdevteam](https://twitter.com/zfdevteam))
* [Zend Framework](http://framework.zend.com/) ([@zfdevteam](https://twitter.com/zfdevteam))

## Other Benchmarks

* [PHP ORM Benchmark](https://github.com/kenjis/php-orm-benchmark)
* [PHP User Agent Parser Benchmarks](https://github.com/kenjis/user-agent-parser-benchmarks)
