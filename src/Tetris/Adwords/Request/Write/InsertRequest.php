<?php

namespace Tetris\Adwords\Request\Write;

use Tetris\Adwords\Request\WriteRequest;
use Tetris\Adwords\Client;

class InsertRequest extends WriteRequest
{
    function __construct(Client $client, array $values)
    {
        $this->values = $values;
        $this->client = $client;
    }

    function into(string $className, $serviceName = null): ExecutableWriteRequest
    {
        return new ExecutableWriteRequest($this->client, 'ADD', $className, $this->values, $serviceName);
    }
}