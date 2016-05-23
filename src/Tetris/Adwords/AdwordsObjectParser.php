<?php

namespace Tetris\Adwords;

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

    private static function writeField(string $inputField, string $outputField, $inputObject, array &$outputArray)
    {
        $input = $inputObject;
        $output = &$outputArray;

        if ($input instanceof Campaign) {
            switch ($inputField) {
                // budget fields
                case 'Amount':
                case 'BudgetId':
                case 'BudgetName':
                case 'BudgetReferenceCount':
                case 'BudgetStatus':
                case 'IsBudgetExplicitlyShared':
                case 'DeliveryMethod':
                    if (!isset($outputArray['budget'])) {
                        $outputArray['budget'] = [];
                    }

                    if ($inputField !== 'BudgetId') {
                        $inputField = str_replace('Budget', '', $inputField);
                    }

                    $output = &$outputArray['budget'];
                    $input = $input->budget;

                    break;
            }
        }

        $output[$outputField] = self::getNormalizedField($inputField, $input);
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
            self::writeField($adwordsKey, $userKey, $adwordsObject, $array);
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
                    $micro = round($value, 2) * 10 ** 6;
                    $entity->amount = new Money((int)$micro);
                    break;
                default:
                    $entity->{$field} = $value;
                    break;
            }
        }

        return $entity;
    }
}