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

        if ($input instanceof AdGroupAd) {
            $directAttributes = [
                'AdGroupAdDisapprovalReasons',
                'AdGroupAdTrademarkDisapproved',
                'AdGroupCreativeApprovalStatus',
                'AdGroupId',
                'AdType',
                'BaseAdGroupId',
                'BaseCampaignId',
                'Labels',
                'Status'
            ];
            $experimentAttributes = [
                'ExperimentDataStatus',
                'ExperimentDeltaStatus',
                'ExperimentId'
            ];

            $isAdAttribute = !in_array($inputField, $directAttributes);
            $isExperimentAttribute = in_array($inputField, $experimentAttributes);

            if ($inputField === 'TemplateOriginAdId') {
                $inputField = 'OriginAdId';
            } else {
                $removablePrefixes = [
                    'AdGroupAd',
                    'AdGroupCreative',
                    'Ad',
                    'Creative',
                    'CallOnlyAd',
                    'RichMediaAd',
                    'TemplateAd'
                ];

                foreach ($removablePrefixes as $prefix) {
                    if (strpos($inputField, $prefix) === 0) {
                        $inputField = str_replace($prefix, '', $inputField);
                        break;
                    }
                }
            }

            if ($isExperimentAttribute) {
                if (!isset($outputArray['experiment'])) {
                    $outputArray['experiment'] = [];
                }

                $input = $input->experimentData;
                $output = &$outputArray['experiment'];
            } else if ($isAdAttribute) {
                $input = $input->ad;
            }
        }

        if ($input instanceof AdGroupCriterion) {
            $directAttributes = [
                "AdGroupId",
                "CriterionUse",
                "Labels",
                "BaseCampaignId",
                "BaseAdGroupId",
                "Status",
                "SystemServingStatus", "ApprovalStatus", "DisapprovalReasons", "DestinationUrl", "FirstPageCpc", "TopOfPageCpc", "FirstPositionCpc", "BidModifier", "FinalUrls", "FinalMobileUrls", "FinalAppUrls", "TrackingUrlTemplate", "UrlCustomParameters"];
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