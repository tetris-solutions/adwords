<?php

namespace Tetris\Adwords;

use Campaign;
use ManagedCustomer;
use Budget;
use Money;
use Nayjest\StrCaseConverter\Str;

abstract class AdwordsObjectParser
{
    protected static $mappings = null;
    protected static $reportMappings = null;

    static function stripSingleValueFromArray($array)
    {
        if (!is_array($array) || count($array) > 1) {
            return $array;
        }

        $singleKey = array_keys($array)[0];

        return self::stripSingleValueFromArray($array[$singleKey]);
    }

    private static function getReportMappings(): array
    {
        if (empty(self::$reportMappings)) {
            self::$reportMappings = json_decode(file_get_contents(__DIR__ . '/report-mappings.json'), true);
        }
        return self::$reportMappings;
    }

    private static function getMappings(): array
    {
        if (empty(self::$mappings)) {
            self::$mappings = json_decode(file_get_contents(__DIR__ . '/mappings.json'), true);
        }
        return self::$mappings;
    }

    private static function convertField(string $field, $value)
    {
        if ($value instanceof Money) {
            return intval($value->microAmount) / (10 ** 6);
        }

        switch (ucfirst($field)) {
            case 'Bid':
            case 'BidCeiling':
            case 'Cost':
            case 'Amount':
                return (int)$value / (10 ** 6);
            default:
                return $value;
        }
    }

    private static function getPathFromObject(array $path, $object)
    {
        $pointer = $object;
        $field = '';

        foreach ($path as $part) {
            if (!isset($pointer->{$part})) {
                throw new \Exception('not found field');
            }

            $field = $part;
            $pointer = $pointer->{$part};
        }

        return self::convertField($field, $pointer);
    }

    private static function getField($object, string $field)
    {
        $mapping = self::getMappings();
        $className = get_class($object);
        $guessedServiceName = $className . 'Service';

        if (isset($mapping[$guessedServiceName][$field])) {
            try {
                return self::getPathFromObject($mapping[$guessedServiceName][$field], $object);
            } catch (\Throwable $e) {
            }
        }

        foreach ($mapping as $service) {
            foreach ($service as $name => $path) {
                if ($name === $field) {
                    try {
                        return self::getPathFromObject($path, $object);
                    } catch (\Throwable $e) {
                    }
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

    static function normalizeReportObject($reportName, $fields, $inputObject)
    {
        $map = [];
        $reportMappings = self::getReportMappings();

        foreach ($fields as $field => $userKey) {
            if (isset($reportMappings[$reportName][$field])) {
                $fieldRealName = $reportMappings[$reportName][$field]['XMLAttribute'];
            } else {
                $fieldRealName = lcfirst($field);
            }

            $map[$userKey] = property_exists($inputObject, $fieldRealName)
                ? self::convertField($field, $inputObject->{$fieldRealName})
                : NULL;
        }

        return AdwordsObjectParser::stripSingleValueFromArray($map);
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