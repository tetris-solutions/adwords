<?php

namespace Tetris\Adwords;

use Exception;
use stdClass;
use Google\AdsApi\AdWords\v201710\cm\Campaign;
use Google\AdsApi\AdWords\v201710\mcm\ManagedCustomer;
use Google\AdsApi\AdWords\v201710\cm\Budget;
use Google\AdsApi\AdWords\v201710\cm\Money;
use Google\AdsApi\AdWords\v201710\cm\Bid;
use Nayjest\StrCaseConverter\Str;

abstract class AdwordsObjectParser
{
    protected static $mappings = null;
    protected static $reportMappings = null;
    protected static $overrideType = [
        'AverageCpv' => 'Money'
    ];
    protected static $overrideService = [
        'SharedBiddingStrategyService' => 'BiddingStrategyService'
    ];
    protected static $overrideClassName = [
        'BiddingStrategy' => 'SharedBiddingStrategy'
    ];

    private static function cast($value)
    {
        if ($value instanceof Money) {
            return intval($value->getMicroAmount()) / (10 ** 6);
        }

        if ($value instanceof Bid) {
            return self::cast($value->getAmount());
        }

        return $value;
    }

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
        if (empty(self::$mappings)) {
            self::$mappings = json_decode(file_get_contents(__DIR__ . '/mappings.json'), true);
        }
        return self::$mappings;
    }

    private static function insertValue(array $path, $object, &$values)
    {
        $pointer = $object;

        //check if there is array inside array
        $realPath = is_scalar($path[0]) ? $path : $path[0];

        foreach ($realPath as $index => $part) { 

            $isLastPart = $index === count($realPath) - 1;

            if (is_array($pointer)) {
                
                $remainingPath = array_slice($realPath, $index);

                foreach ($pointer as $item) {
                    self::insertValue($remainingPath, $item, $values);
                }
            }

            //if the propriety exists as getter, get it
            if(method_exists ( $pointer , "get".ucfirst($part) )){
                $pointerClass = get_class($pointer);
                $pointer = call_user_func_array(array($pointer, "get".ucfirst($part)), array());
                if($pointerClass == Money::class){
                    $pointer = $pointer / (10 ** 6);
                }
            }else{
                return;
            }

            if (!isset($pointer)) {
                return;
            }

            if($isLastPart)$pointer = self::parseAdwordsObjectsArray($pointer); 
        }

        $values[] = $pointer;
    }

    private static function parseAdwordsObjectsArray($pointer)
    {
        if (is_array($pointer)) {
            //if $pointer is an array of objects, parse it, infer all of the proprieties of each object and get then
            $newPointer = [];
            foreach ($pointer as $item) {

                if(is_object($item)){
                    $newPointer[] = self::getItemProps($item);
                }else{
                    $newPointer[] = $item;
                }
                
            }
            return $newPointer;
        
        }else if(is_object($pointer)){
            //this is an object returned from one of the new objects, so it is also "unknow". Lets grab all of its proprieties
            return self::getItemProps($pointer);

        }else{
            //nothing to do here, return the same value
            return $pointer;
        }
    }

    private static function getItemProps($item)
    {
        $itemProps = [];
        //infer all of the getter proprieties
        $method_names = preg_grep('/^get/', get_class_methods($item));
        //for each propriety get it as a substring of the getter name
        foreach ($method_names as $method) {
            $proprietyName = lcfirst(substr($method, 3));
            $itemProps[$proprietyName] = call_user_func_array(array($item, $method), array());
            $itemProps[$proprietyName] = self::parseAdwordsObjectsArray($itemProps[$proprietyName]);
        }
        return $itemProps;
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

        $pos = strrpos($className, '\\');
        $parsedClassName = $pos === false ? $className : substr($className, $pos + 1);
        $guessedServiceName = $parsedClassName . 'Service';

        if (isset(self::$overrideService[$guessedServiceName])) {
            $guessedServiceName = self::$overrideService[$guessedServiceName];
        }

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

    /**
     * @param array $fieldMap
     * @param ManagedCustomer|Campaign|Budget $adwordsObject
     * @return array|mixed
     */
    static function readFieldsFromAdwordsObject(array $fieldMap, $adwordsObject, $keepSourceObject = FALSE)
    {
        $array = [];
        $input = $adwordsObject;

        foreach ($fieldMap as $adwordsKey => $userKey) {
            try {
                $array[$userKey] = self::parseSpecialValues($userKey, self::getField($input, $adwordsKey));

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

    static function parseSpecialValues($key, $value)
    {
        if(strtolower(substr($key, -2)) == 'id'){
            return (string)$value;
        }else{
            return $value;
        }
    }


    static function normalizeReportObject($reportName, $fields, $inputObject)
    {
        $map = [];
        $report = ReportMap::get($reportName);

        foreach ($fields as $field => $userKey) {
            $fieldRealName = isset($report[$field]['XMLAttribute'])
                ? $report[$field]['XMLAttribute']
                : NULL;

            if ($fieldRealName === NULL || !property_exists($inputObject, $fieldRealName)) {
                $map[$userKey] = NULL;
                continue;
            }

            $type = $report[$field]['Type'];

            if (isset(self::$overrideType[$field])) {
                $type = self::$overrideType[$field];
            }

            $map[$userKey] = ($type === 'Money' || $type === 'Bid')
                ? intval($inputObject->{$fieldRealName}) / (10 ** 6)
                : $inputObject->{$fieldRealName};
        }

        return AdwordsObjectParser::stripSingleValueFromArray($map);
    }

    static function readFieldsFromArrayIntoAdwordsObject(string $className, array $fields)
    {
        $className = isset(self::$overrideClassName[$className])
            ? '\Google\AdsApi\AdWords\v201710\cm\\' . self::$overrideClassName[$className]
            : '\Google\AdsApi\AdWords\v201710\cm\\' . $className;

        try {
            $entity = new $className();
        } catch (\Throwable $e) {
            $entity = new stdClass();
        }

        foreach ($fields as $field => $value) {

            $field = ucfirst(Str::toCamelCase($field));

            if (!method_exists($entity, "set".$field)) continue;

            switch (strtolower($field)) {
                case 'budget':
                    $entity->setBudget(new Budget($value));
                    break;
                case 'amount':
                    $micro = intval(floor($value * 100) * 10 ** 4);
                    $entity->setAmount(new Money(null,$micro));
                    break;
                default:
                    call_user_func_array(array($entity, "set".$field), array($value));
                    break;
            }
        }

        return $entity;
    }
}