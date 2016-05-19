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

    protected static function stripSingleValueFromArray($array)
    {
        if (!is_array($array) || count($array) > 1) {
            return $array;
        }

        $singleKey = array_keys($array)[0];

        return self::stripSingleValueFromArray($array[$singleKey]);
    }

    protected static function getNormalizedField($field, $input)
    {
        $camelCaseField = lcfirst($field);

        if (!property_exists($input, $camelCaseField)) {
            return NULL;
        }

        switch (ucfirst($field)) {
            case 'Bid':
            case 'BidCeiling':
            case 'Cost':
            case 'Amount':
                $microAmount = $input->{$camelCaseField} instanceof Money
                    ? $input->{$camelCaseField}->microAmount
                    : $input->{$camelCaseField};
                return (int)$microAmount / (10 ** 6);
            default:
                return $input->{$camelCaseField};

        }
    }

    protected static function isAssociativeArray($arr): bool
    {
        return array_keys($arr) !== range(0, count($arr) - 1);
    }
}