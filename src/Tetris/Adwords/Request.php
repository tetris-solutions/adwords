<?php

namespace Tetris\Adwords;

use Money;
use Tetris\Adwords\Request\Read\TransientRequest as ReadRequest;

abstract class Request
{
    /**
     * @var Client $client
     */
    protected $client;

    /**
     * @var string $className
     */
    protected $className;

    function __construct(Client $client, string $className)
    {
        $this->client = $client;
        $this->className = $className;
    }

    static function select(Client $client, array $fields): ReadRequest
    {
        return new ReadRequest($client, $fields);
    }

    static function insert(Client $client, array $entity)
    {
        /**
         * insert([Name => 'Something terribly misguided'])->into(Campaign)->returning(['Id'])
         */
    }

    static function update(Client $client, string $className, string $serviceName)
    {
        /**
         * update(Campaign)->set([Name => 'The previous name was clearly wrong'])->where(id, 1234)->returning(['Name'])
         */
    }
}