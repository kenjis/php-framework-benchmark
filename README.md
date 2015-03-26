# PHP Framework Benchmark

## Results

### Hello World Benchmark

|framework          |requests per second|peak memory|
|-------------------|------------------:|----------:|
|phalcon-1.3        |            1445.99|       0.50|
|codeigniter-3.0    |             698.69|       0.50|
|yii-2.0            |             376.68|       1.50|
|fuel-1.8-dev       |             322.90|       0.75|
|silex-1.2          |             311.63|       0.75|
|bear-1.0           |             296.89|       1.00|
|cake-3.0           |             259.01|       1.00|
|symfony-2.6        |             122.58|       2.00|
|laravel-5.0        |              70.63|       3.00|

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
* [Silex](http://silex.sensiolabs.org/)
* [BEAR.Sunday](https://bearsunday.github.io/)
* [CakePHP](http://cakephp.org/)
* [Symfony](http://symfony.com/)
* [Laravel](http://laravel.com/)

## Related

* [PHP ORM Benchmark](https://github.com/kenjis/php-orm-benchmark)
* [PHP User Agent Parser Benchmarks](https://github.com/kenjis/user-agent-parser-benchmarks)
