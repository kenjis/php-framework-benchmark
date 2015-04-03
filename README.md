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
* Zend Framework 2.4.0
* TYPO3 Flow 2.3.3

## Results

These are my benchmarks, not yours. I encourage you to run on your environments.

### Hello World Benchmark

(2015/04/03)

|framework          |requests per second|relative|peak memory|relative|
|-------------------|------------------:|-------:|----------:|-------:|
|phalcon-1.3        |           1,546.58|    37.3|       0.50|     1.0|
|codeigniter-3.0    |             693.24|    16.7|       0.50|     1.0|
|slim-2.6           |             725.55|    17.5|       0.50|     1.0|
|yii-2.0            |             385.49|     9.3|       1.50|     3.0|
|fuel-1.8-dev       |             294.12|     7.1|       0.75|     1.5|
|silex-1.2          |             349.25|     8.4|       1.00|     2.0|
|bear-1.0           |             328.41|     7.9|       1.00|     2.0|
|cake-3.0           |             249.54|     6.0|       1.00|     2.0|
|symfony-2.6        |             246.28|     5.9|       1.00|     2.0|
|laravel-5.0        |              80.08|     1.9|       2.75|     5.5|
|zf-2.4             |              69.69|     1.7|       2.25|     4.5|
|typo3-flow-2.3     |              41.46|     1.0|       5.25|    10.5|

Note(1): All the results are run on php with phalcon.so. If you don't load phalcon.so, the rps except for Phalcon probably increase.

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

## Related

* [PHP ORM Benchmark](https://github.com/kenjis/php-orm-benchmark)
* [PHP User Agent Parser Benchmarks](https://github.com/kenjis/user-agent-parser-benchmarks)
