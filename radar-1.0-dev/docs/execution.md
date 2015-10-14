# Execution Process

The full execution process in Radar looks like this:

    Boot -> Setup -> Run -> Middleware

Point by point:

- A bootstrap phase to build a DI container with custom configuration;

- The setup phase (this is where you define the URL routes with their action
elements, add middleware callables, define custom Action/Routing/Exception/
Sending handlers), etc.

- The run phase, which executes all middleware callables in turn.

The initial `web/index.php` installation queues four middleware decorators:

- _Relay\Middleware\SendingHandler_ to pass along the inbound request without
  modification, and send back the outbound response after all other middlware
  have processed it;

- _Relay\Middleware\ExceptionHandler_ as a final fallback to catch exceptions;

- _Radar\Adr\Handler\RoutingHandler_ to determine the _Route_ based on the _ServerRequest_;

- _Radar\Adr\Handler\ActionHandler_ to use the _Route_ for the action-domain-responder activity:

    - An _Input_ callable is invoked to examine the incoming HTTP
    _ServerRequest_ message and extract values to pass along to the core
    _Domain_ callable.

    - A _Domain_ callable is invoked using those values, and a _Payload_
    from the _Domain_ is received in return.

    - A _Responder_ callable is invoked with the _Domain_ output; the
    _Responder_ then builds the outgoing HTTP _Response_ message.

You can prepend, append, or replace these handlers in `web/index.php` with your own middleware.

### Navigation

* Back to [Container Configuration](/docs/container.md)
* Up to [Index](/docs/index.md)
