# PHP Framework Benchmark

## Results

### Hello World Benchmark

|framework          |requests per second|peak memory|
|-------------------|------------------:|----------:|
|phalcon-1.3        |            1209.39|        0.5|
|codeigniter-3.0-dev|             475.88|        0.5|
|yii-2.0            |             271.12|        1.5|
|fuel-1.8-dev       |             186.39|       0.75|
|cake-3.0-dev       |             135.34|       1.25|
|symfony-2.5        |               84.9|          2|
|laravel-4.2        |              78.09|          2|

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
* [Yii](http://www.yiiframework.com/)
* [FuelPHP](http://fuelphp.com/)
* [CakePHP](http://cakephp.org/)
* [Symfony](http://symfony.com/)
* [Laravel](http://laravel.com/)
