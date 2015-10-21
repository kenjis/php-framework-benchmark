<?php

namespace Domain\Hello;

use Aura\Payload\Payload;

class HelloApplicationService
{
    public function __construct()
    {
        $this->payload = new Payload();
    }

    public function __invoke(array $input)
    {
        return $this->payload
            ->setStatus(Payload::SUCCESS)
            ->setOutput([
                'value' => 'Hello World!'
            ]);
    }
}
