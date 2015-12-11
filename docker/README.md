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
$ git clone https://github.com/kenjis/php-framework-benchmark.git
$ cd php-framework-benchmark
~~~

Cd into the docker directory of this repo and make sure that docker toolbox is available:
~~~
$ cd docker
$ eval "$(docker-machine env default)"
~~~

Start the Nginx/PHP server stacks:
~~~
$ docker-compose up -d
~~~

Start the supplied docker shell from within this repository's `docker` folder:
~~~
$ docker-compose run shell
~~~

Run the set-up script:
~~~
# sh setup.sh
~~~

Run benchmarks against each docker stack:
~~~
$ docker/bin/benchmark.sh
~~~

### Check the results

To see the results tables in markdown, run the following from within the docker shell:

~~~
$ docker/bin/results.sh
~~~

To see the results graph, run the following script from **outside** the docker shell, from the repository root:

~~~
$ docker/bin/urls.sh
~~~

It echoes URLs, which you should open up in your browser.

