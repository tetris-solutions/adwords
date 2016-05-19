<?php
namespace Tetris\Adwords\Request\Write;

use Tetris\Adwords\Request\WriteRequest;
use Tetris\Adwords\Client;

class UpdateRequest extends WriteRequest
{
    /**
     * @var string|null $serviceName
     */
    private $serviceName;

    function __construct(Client $client, string $className, $serviceName = null)
    {
        $this->client = $client;
        $this->className = $className;
        $this->serviceName = $serviceName;
    }

    function set(array $values): ExecutableWriteRequest
    {
        return new ExecutableWriteRequest($this->client, 'SET', $this->className, $values, $this->serviceName);
    }
}