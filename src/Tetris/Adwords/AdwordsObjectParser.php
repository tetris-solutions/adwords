<?php

namespace Tetris\Adwords;

use Exception;
use stdClass;
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

    private static function convertField($value)
    {
        if ($value instanceof Money) {
            return intval($value->microAmount) / (10 ** 6);
        }

        return $value;
    }

    private static function insertValue(array $path, $object, &$values)
    {
        $pointer = $object;

        foreach ($path as $index => $part) {
            if (!isset($pointer->{$part})) {
                return;
            }

            $pointer = $pointer->{$part};
            $isLastPart = $index === count($path) - 1;

            if (is_array($pointer) && !$isLastPart) {
                $remainingPath = array_slice($path, $index + 1);

                foreach ($pointer as $item) {
                    self::insertValue($remainingPath, $item, $values);
                }
            }
        }

        $values[] = $pointer;
    }

    private static function getValueFromPath(array $path, $object)
    {
        $values = [];

        self::insertValue($path, $object, $values);

        if (empty($values)) {
            throw new Exception('Could not find field');
        }

        return count($values) > 1 ? $values : $values[0];
    }

    private static function getField($object, string $field)
    {
        $mapping = self::getMappings();
        $className = get_class($object);
        $guessedServiceName = $className . 'Service';

        if (isset($mapping[$guessedServiceName][$field])) {
            foreach ($mapping[$guessedServiceName][$field] as $path) {
                try {
                    return self::getValueFromPath($mapping[$guessedServiceName][$field], $object);
                } catch (\Throwable $e) {
                }
            }
        }

        foreach ($mapping as $service) {
            foreach ($service as $name => $paths) {
                foreach ($paths as $path) {
                    if ($name === $field) {
                        try {
                            return self::getValueFromPath($path, $object);
                        } catch (\Throwable $e) {
                        }
                    }
                }
            }
        }

        throw new \Exception("Could not find field '{$field}' in a instance of {$className}");
    }

    private static function normalizeAdwordsObject($input)
    {
        if (is_scalar($input)) {
            return $input;
        }

        if (is_array($input)) {
            $parsedArray = [];

            foreach ($input as $index => $value) {
                $parsedArray[$index] = self::normalizeAdwordsObject(
                    $value instanceof Money ? intval($value->microAmount) / (10 ** 6) : $value
                );
            }

            return $parsedArray;
        }

        if (is_object($input)) {
            $parsedObject = new stdClass;
            $ls = get_object_vars($input);

            foreach ($ls as $key => $value) {
                $parsedObject->{$key} = self::normalizeAdwordsObject(
                    $value instanceof Money ? intval($value->microAmount) / (10 ** 6) : $value
                );
            }

            return $parsedObject;
        }

        return NULL;
    }

    /**
     * @param array $fieldMap
     * @param ManagedCustomer|Campaign|Budget $adwordsObject
     * @return array|mixed
     */
    static function readFieldsFromAdwordsObject(array $fieldMap, $adwordsObject, $keepSourceObject = FALSE)
    {
        $array = [];
        $input = self::normalizeAdwordsObject($adwordsObject);

        foreach ($fieldMap as $adwordsKey => $userKey) {
            try {
                $array[$userKey] = self::getField($input, $adwordsKey);
            } catch (\Throwable $e) {
                $array[$userKey] = NULL;
            }
        }

        $result = self::stripSingleValueFromArray($array);

        if ($keepSourceObject && is_array($result)) {
            $result['__source__'] = $input;
        }

        return $result;
    }

    static function normalizeReportObject($reportName, $fields, $inputObject)
    {
        $map = [];
        $reportMappings = self::getReportMappings();

        foreach ($fields as $field => $userKey) {
            $fieldRealName = isset($reportMappings[$reportName][$field]['XMLAttribute'])
                ? $reportMappings[$reportName][$field]['XMLAttribute']
                : NULL;

            if ($fieldRealName === NULL || !property_exists($inputObject, $fieldRealName)) {
                $map[$userKey] = NULL;
                continue;
            }

            $type = $reportMappings[$reportName][$field]['Type'];

            $map[$userKey] = $type === 'Money'
                ? intval($inputObject->{$fieldRealName}) / (10 ** 6)
                : $inputObject->{$fieldRealName};
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