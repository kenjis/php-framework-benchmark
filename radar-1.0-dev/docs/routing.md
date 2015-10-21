# Routing

Before continuing, remove the default `Hello` route from the `web/index.php`
file. It looks like this:

```php
$adr->get('Hello', '/{name}?', function (array $input) {
        $payload = new \Aura\Payload\Payload();
        return $payload
            ->setStatus($payload::SUCCESS)
            ->setOutput([
                'phrase' => 'Hello ' . $input['name']
            ]);
    })
    ->defaults(['name' => 'world']);
```

That will prepare your project for real use.

## Adding A Route

The Radar routing system is based on
[Aura.Router](https://github.com/auraphp/Aura.Router), with some details
modified specifically for Radar.

In other frameworks, a route points an incoming request to a particular
controller class and action method. In Radar, every action is identical, in the
sense that it receives input, invokes a domain element, and passes the domain
output to a responder. This means that a Radar route does not point to an action
per se, but to a trio of action-related elements: an input handler, a domain
element, and a responder.

Let's add an HTTP PATCH route. In `web/index.php`, call `$adr->patch()` with a
route name and URL path, and a _Domain_ specification.

```php
$adr->patch('Todo\EditItem', '/todo/{id}', 'Domain\Todo\ApplicationService\EditItem');
```

The route name doubles as a class name prefix for optional _Input_ and
_Responder_ classes. We will talk more about that later.

The path is typical for routing systems, using placeholder tokens for route
attribute values.

The _Domain_ specification is a string or an array:

- If a string, Radar will instantiate this class using the internal dependency
injection container and call its `__invoke()` method with the user input from
the HTTP request.

- If an array in the format `['ClassName', 'method']`, the dependency injection
container will instantiate the specfied class name, and then call the specified
method with the user input from the HTTP request.

## Manually Specifying A Custom Input Class

By default, each Radar route uses the _Radar\Adr\Input_ class to collect user
input and use it for parameters to the _Domain_ call. Essentially, the input-collection
logic looks like this:

```php
    public function __invoke(ServerRequestInterface $request)
    {
        return [
            array_merge(
                (array) $request->getQueryParams(),
                (array) $request->getAttributes(),
                (array) $request->getParsedBody(),
                (array) $request->getUploadedFiles()
            )
        ];
    }
```

This means the _Domain_ element has to receive a single `$input` array as its
only parameter. The `$input` is merged from a list of sources, with later
sources overriding earlier ones. This is naive but useful as a default case.

For more serious and useful input collection,
you can write a callable class that mimics the above `__invoke()` signature, and have it
return the parameters to pass to the _Domain_ element. For example, if your
_Domain_ call takes an `$id` and a `$data` array as its parameters, you might do this:

```php
use Psr\Http\Message\ServerRequestInterface;

class GenericIdAndDataInput
{
    public function __invoke(ServerRequestInterface $request)
    {
        $id = $request->getAttribute('id');
        $data = $request->getParsedBody();
        return [$id, $data];
    }
}
```

You can then specify this class as the input callable to be used for your route
by calling `input()` on the route object:

```php
$adr->patch('Todo\EditItem', '/todo/{id}', 'Domain\Todo\ApplicationService\EditItem')
    ->input('GenericIdAndDataInput');
```

If you want to set your own default input class instead of the Radar one,
call `$adr->input()` directly instead of as part of a route specification:

```php
$adr->input('MyDefaultInputClass');
```

That class will be used as the default input callable for all route actions.

## Manually Specifying A Custom Responder Class

By default, each Radar route uses the _Radar\Adr\Responder\Responder_ class to
build the HTTP response. Because presentation work is so dependent on the
domain elements being presented, it is difficult to outline how Responders work
in detail; please examine the _Responder_ class to get a good handle on what's
going on there.

In the mean time, let it suffice to say that you are almost certain to want to
build your own _Responder_ classes, and then specify them as the responder callable
for one or more of your routes. To
do so, call `responder()` on the route, and pass the responder class name:

```php
$adr->patch('Todo\EditItem', '/todo/{id}', 'Domain\Todo\ApplicationService\EditItem')
    ->responder('MyTodoResponder');
```


If you want to set your own default responder class instead of the Radar one,
call `$adr->responder()` directly instead of as part of a route specification:

```php
$adr->responder('MyDefaultResponderClass');
```

> N.b.: As a side note, if your responder class implements
> _Radar\Adr\Responder\ResponderAcceptsInterface_, the route will automatically
> apply an `accepts()` pre-filter to help with content negotiation. This is not
> content-negotiation proper, only a filter to make sure the responder can handle
> at least one of the content types acceptable by the client.

## Automatic Input And Responder Discovery

Earlier, we noted that the route name doubles as a class name prefix for
optional _Input_ and _Responder_ classes. This means that if you name your
classes according to a Radar convention, the route will automatically pick up on
those classes and use them for `input()` and `responder()` on the route.

For example, given a route named `Todo\EditItem`, you could create a
`Todo\EditItem\Input` class and/or a `Todo\EditItem\Responder` class. If they
exist, the route will use them automatically. Of course, you can still manually
specify an `input()` and `responder()` value, and those will be used instead.

## Other Route Specifications

The `$adr` object acts as a proxy for the underlying _Aura\Router\Map_ instance,
so all the _Map_ methods are also available on the `$adr` object for defining
your routes. Please see the Aura.Router
[Map](https://github.com/auraphp/Aura.Router/blob/3.x/docs/index.md)
documentation for more information.

### Navigation

* Continue to [Middleware](/docs/middleware.md)
* Back to [Domain Design](/docs/domain.md)
