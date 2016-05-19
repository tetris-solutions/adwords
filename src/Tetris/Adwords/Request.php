<?php

namespace Tetris\Adwords;

use Money;
use Tetris\Adwords\Request\Read\TransientRequest as ReadRequest;
use Tetris\Adwords\Request\Write\InsertRequest;
use Tetris\Adwords\Request\Write\UpdateRequest;

abstract class Request
{
    /**
     * @var Client $client
     */
    protected $client;

    /**
     * @var string|null $className
     */
    protected $className;

    /**
     * @var array $fieldMap
     */
    protected $fieldMap;

    static function select(Client $client, array $fieldMap): ReadRequest
    {
        return new ReadRequest($client, $fieldMap);
    }

    /**
     * insert([Name => 'Something terribly misguided'])->into(Campaign)->returning(['Id'])
     * @param Client $client
     * @param array $values
     * @return InsertRequest
     */
    static function insert(Client $client, array $values): InsertRequest
    {
        return new InsertRequest($client, $values);
    }

    /**
     * update(Campaign)->set([Id => 1234, Name => 'The previous name was clearly wrong'])->returning(['Name'])
     * @param Client $client
     * @param string $className
     * @param string|null $serviceName
     * @return UpdateRequest
     */
    static function update(Client $client, string $className, $serviceName = null): UpdateRequest
    {
        return new UpdateRequest($client, $className, $serviceName);
    }

    protected static function normalizeFieldMaps(array $fieldMap): array
    {
        return array_keys($fieldMap) !== range(0, count($fieldMap) - 1)
            ? $fieldMap
            : array_combine($fieldMap, $fieldMap);
    }
}