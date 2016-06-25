<?php

namespace Tetris\Adwords;

use AdGroupCriterion;
use AdGroupAd;
use TextAd;
use Campaign;
use ManagedCustomer;
use Budget;
use Money;
use Nayjest\StrCaseConverter\Str;

abstract class AdwordsObjectParser
{
    static function stripSingleValueFromArray($array)
    {
        if (!is_array($array) || count($array) > 1) {
            return $array;
        }

        $singleKey = array_keys($array)[0];

        return self::stripSingleValueFromArray($array[$singleKey]);
    }

    private static function getMappings(): array
    {
        return json_decode(file_get_contents(__DIR__ . '/mappings.json'), true);
    }

    private static function getField($object, string $field)
    {
        $mapping = self::getMappings();
        $className = get_class($object);
        $guessedServiceName = $className . 'Service';

        $diveIntoPath = function (array $path) use ($object) {
            $pointer = $object;
            foreach ($path as $part) {
                if (!isset($pointer->{$part})) throw new \Exception('not found field');

                $pointer = $pointer->{$part};
            }

            return $pointer;
        };

        if (isset($mapping[$guessedServiceName][$field])) {
            try {
                return $diveIntoPath($mapping[$guessedServiceName][$field]);
            } catch (\Throwable $e) {
            }
        }

        foreach ($mapping as $service) {
            foreach ($service as $name => $path) {
                if ($name !== $field) continue;

                try {
                    return $diveIntoPath($path);
                } catch (\Throwable $e) {

                }
            }
        }

        throw new \Exception("Could not find field '{$field}' in a instance of {$className}");
    }

    /**
     * @param array $fieldMap
     * @param ManagedCustomer|Campaign|Budget $adwordsObject
     * @return array|mixed
     */
    static function readFieldsFromAdwordsObject(array $fieldMap, $adwordsObject)
    {
        $array = [];

        foreach ($fieldMap as $adwordsKey => $userKey) {
            try {
                $array[$userKey] = self::getField($adwordsObject, $adwordsKey);
            } catch (\Throwable $e) {
                $array[$userKey] = NULL;
            }
        }

        return self::stripSingleValueFromArray($array);
    }

    static function getNormalizedField($field, $input)
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

    static function readFieldsFromArrayIntoAdwordsObject(string $className, array $fields)
    {
        $entity = new $className();

        foreach ($fields as $field => $value) {
            $field = lcfirst(Str::toCamelCase($field));

            if (!property_exists($entity, $field)) continue;

            switch ($field) {
                case 'budget':
                    $entity->budget = new Budget($value);
                    break;
                case 'amount':
                    $micro = intval(floor($value * 100) * 10 ** 4);
                    $entity->amount = new Money($micro);
                    break;
                default:
                    $entity->{$field} = $value;
                    break;
            }
        }

        return $entity;
    }
}