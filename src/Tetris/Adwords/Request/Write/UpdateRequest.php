<?php
namespace Tetris\Adwords\Request\Write;

use Tetris\Adwords\Request\WriteRequest;
use Tetris\Adwords\Client;
use Google\AdsApi\AdWords\v201809\cm\Operator;

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
        return new ExecutableWriteRequest($this->client, Operator::SET, $this->className, $values, $this->serviceName);
    }
}