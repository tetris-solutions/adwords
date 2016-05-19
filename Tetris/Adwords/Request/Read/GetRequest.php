<?php

namespace Tetris\Adwords\Request\Read;

use Campaign;
use Budget;
use ManagedCustomer;

use CampaignPage;
use BudgetPage;
use ManagedCustomerPage;

use CampaignService;
use BudgetService;
use ManagedCustomerService;

use Tetris\Adwords\Request\ReadRequest;

class GetRequest extends ReadRequest
{
    /**
     * @var CampaignService|BudgetService|ManagedCustomerService $service
     */
    private $service;

    protected function init($serviceName = null)
    {
        $this->service = $this->client->GetService(
            isset($serviceName) ? $serviceName : $this->className . 'Service'
        );
    }

    private static function readFieldFromAdpeekEntityIntoArray(string $inputField, string $outputField, $object, array &$array)
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
     * @param ManagedCustomer|Campaign|Budget $adwordsObject
     * @return array|mixed
     */
    private function readFieldsFromAdwordsObject($adwordsObject)
    {
        $array = [];

        foreach ($this->fields as $adwordsKey => $userKey) {
            self::readFieldFromAdpeekEntityIntoArray($adwordsKey, $userKey, $adwordsObject, $array);
        }

        return self::stripSingleValueFromArray($array);
    }

    private function fetch(): array
    {
        /**
         * @var CampaignPage|BudgetPage|ManagedCustomerPage $result
         */
        $result = $this->service->get($this->selector);

        $ls = [];

        /**
         * @var ManagedCustomer|Campaign|Budget $adwordsObject
         */
        foreach ($result->entries as $adwordsObject) {
            $ls[] = $this->readFieldsFromAdwordsObject($adwordsObject);
        }

        return $ls;
    }

    function fetchOne()
    {
        $ls = $this->fetch();

        if (empty($ls)) {
            throw new \Exception('Not found', 404);
        }

        return $ls[0];
    }

    function fetchAll(): array
    {
        return $this->fetch();
    }
}