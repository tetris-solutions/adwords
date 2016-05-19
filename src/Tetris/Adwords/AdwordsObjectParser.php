<?php

namespace Tetris\Adwords;

use Campaign;
use ManagedCustomer;
use Budget;
use Money;

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

    private static function readFieldFromAdpeekObjectIntoArray(string $inputField, string $outputField, $object, array &$array)
    {
        $input = $object;
        $output = &$array;

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
                    if (!isset($array['budget'])) {
                        $array['budget'] = [];
                    }

                    if ($inputField !== 'BudgetId') {
                        $inputField = str_replace('Budget', '', $inputField);
                    }

                    $output = &$array['budget'];
                    $input = $input->budget;

                    break;
            }
        }

        $output[$outputField] = self::getNormalizedField($inputField, $input);
    }

    /**
     * @param array $fields
     * @param ManagedCustomer|Campaign|Budget $adwordsObject
     * @return array|mixed
     */
    static function readFieldsFromAdwordsObject($fields, $adwordsObject)
    {
        $array = [];

        foreach ($fields as $adwordsKey => $userKey) {
            self::readFieldFromAdpeekObjectIntoArray($adwordsKey, $userKey, $adwordsObject, $array);
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
}