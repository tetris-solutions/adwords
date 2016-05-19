<?php

namespace Tetris\Adwords;

use Money;

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

    protected static function normalizeFieldMaps(array $fieldMap): array
    {
        return array_keys($fieldMap) !== range(0, count($fieldMap) - 1)
            ? $fieldMap
            : array_combine($fieldMap, $fieldMap);
    }
}