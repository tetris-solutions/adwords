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
use Tetris\Adwords\AdwordsObjectParser;

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
            $ls[] = AdwordsObjectParser::readFieldsFromAdwordsObject($this->fields, $adwordsObject);
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