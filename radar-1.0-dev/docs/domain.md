# Domain Design

Radar concentrates exclusively the HTTP request/response cycle. This means that,
for Radar to be useful, you need to build your _Domain_ outside of, and probably
in parallel with, your Radar wrapper around that _Domain_.

With that in mind, this is a minimalist primer on building a _Domain_ service.
For more information, please consult Domain Driven Design and similar works.

## Application Service

All Radar cares about is the outermost (or topmost) entry point into the
_Domain_ layer. This entry point is likely to be something like an
_ApplicationService_.

Your ADR _Action_ will pass user input into the _ApplicationService_. The
_ApplicationService_ will initiate and coordinate all the underlying activity in
the _Domain_, and return a _Payload_ back to the _Action_ for the _Responder_ to
use.

The _ApplicationService_ should never access anything directly in the HTTP or
CLI layer. Everything it needs should be injected from the outside, either at
construction time or through a method call. For example, no superglobal should
ever appear in an _ApplicationService_ (or anywhere else in the _Domain_
either). This is to make sure the _ApplicationService_, and by extension the
_Domain_ as a whole, is independent from any particular user interface system.

Each _ApplicationService_ should be as narrowly-purposed as possible, handling
either a single activity, or a limited set of related activities.

## Class Structure

In a todo system, for example, there might be a single _TodoApplicationService_
with methods for browse, read, edit, add, and delete:

```php
namespace Domain\Todo;

class TodoApplicationService
{
    // fetch a list of todo items
    public function getList(array $input) { ... }

    // edit a todo item
    public function editItem(array $input) { ... }

    // mark a todo item as done or not
    public function markItem(array $input) { ... }

    // add a new todo item
    public function addItem(array $input) { ... }

    // delete a todo item
    public function deleteItem(array $input) { ... }
}
```

Alternatively, and perhaps preferably, there might be a series of single-purpose
_Todo_ application services:

```php
namespace Domain\Todo\ApplicationService;

class GetList
{
    public function __invoke(array $input) { ... }
}

class EditItem
{
    public function __invoke(array $input) { ... }
}

class AddItem
{
    public function __invoke(array $input) { ... }
}

class DeleteItem
{
    public function __invoke(array $input) { ... }
}
```

### Domain Logic

The logic inside the _ApplicationService_ is entirely up to you. You can use
anything from a plain-old database connection to a formal DDD approach. As long
as the _ApplicationService_ returns a _Payload_, the internals of the
_ApplicationService_ and its interactions do not matter to Radar.

Here is a naive bit of logic for a _Fetch_ service in our todo application. It
guards against several error conditions (anonymous user, invalid input, user
attempting to edit a todo item they do not own, and database update failures).
It returns a _Payload_ that describes exactly what happened inside the
_Domain_. Also notice how it is completely independent from HTTP or CLI
elements; this makes it easier to test in isolation, and to reuse in different
interfaces.

```php
namespace Domain\Todo\ApplicationService;

use Aura\Payload\Payload;
use Exception;
use Todo\User;
use Todo\Mapper;

class EditItem
{
    public function __construct(
        User $user,
        Mapper $mapper,
        Payload $payload
    ) {
        $this->user = $user;
        $this->mapper = $mapper;
        $this->payload = $payload;
    }

    public function __invoke(array $input)
    {
        if (! $this->user->isAuthenticated()) {
            return $this->payload
                ->setStatus(Payload::NOT_AUTHENTICATED);
        }

        if (empty($input['id'])) {
            return $this->payload
                ->setStatus(Payload::NOT_VALID)
                ->setInput($input)
                ->setMessages([
                    'id' => 'Todo ID not set.'
                ]);
        }

        $todo = $this->mapper->fetchById($input['id']);
        if (! $todo) {
            return $this->payload
                ->setStatus(Payload::NOT_FOUND)
                ->setInput($input);
        }

        if ($this->user->userId !== $todo->userId) {
            return $this->payload
                ->setStatus(Payload::NOT_AUTHORIZED)
                ->setInput($input);
        }

        try {
            $todo->description = $input['description'];
            $this->mapper->update($todo);
            return $this->payload
                ->setStatus(Payload::UPDATED)
                ->setOutput($todo);
        } catch (Exception $e) {
            return $this->payload
                ->setStatus(Payload::ERROR)
                ->setInput($input)
                ->setOutput($e);
        }
    }
}
```

### Domain Packaging

Although you can place the _Domain_ layer in the Radar `src/` directory, it may
be wiser to create it as a separate package, and import it via Composer. This
will help to enforce the separation between the core application and the Radar
user-interface wrapper around it, along with test suites independent from the
Radar project.

### Navigation

* Continue to [Routing](/docs/routing.md)
* Back to [Introduction](/docs/intro.md)
