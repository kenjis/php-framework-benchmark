# PHP Framework Benchmark

This project attempts to mesure minimum overhead (minimum bootstrap cost) of PHP frameworks in real world.

So I think the minimun applications to benchmark should not include:

* cost of template engine (HTML output)
* cost of database manipulation
* cost of debugging information

Components like Template engine or ORM/Database library are out of scope in this project.

If you find something wrong with my code, please feel free to send Pull Requests. But please note optimizing only for "Hello World" is not acceptable. Building fastest "Hello World" application is not the goal in this project.

## Results

### Hello World Benchmark

(2015/03/29) *I've got some feedback after I published the results below. So I will update the results after tweaking this benchmarks.*

|framework          |requests per second|relative|peak memory|relative|
|-------------------|------------------:|-------:|----------:|-------:|
|phalcon-1.3        |           1,445.99|    20.5|       0.50|     1.0|
|codeigniter-3.0    |             698.69|     9.9|       0.50|     1.0|
|slim-2.6           |             685.63|     9.7|       0.50|     1.0|
|yii-2.0            |             376.68|     5.3|       1.50|     3.0|
|fuel-1.8-dev       |             322.90|     4.6|       0.75|     1.5|
|silex-1.2          |             311.63|     4.4|       0.75|     1.5|
|bear-1.0           |             296.89|     4.2|       1.00|     2.0|
|cake-3.0           |             259.01|     3.7|       1.00|     2.0|
|symfony-2.6        |             122.58|     1.7|       2.00|     4.0|
|laravel-5.0        |              70.63|     1.0|       3.00|     6.0|

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
