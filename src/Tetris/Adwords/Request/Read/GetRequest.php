<?php

namespace Tetris\Adwords\Request\Read;

use DateTime;
use Paging;

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

    function limit(int $count, $offset = 0): ReadInterface
    {
        $this->selector->paging = new Paging($offset, $count);
        return $this;
    }

    private function fetch(bool $keepSourceObject): array
    {
        if (empty($this->selector->paging)) {
            $this->limit(500);
        }
        /**
         * @var CampaignPage|BudgetPage|ManagedCustomerPage $result
         */
        $result = $this->service->get($this->selector);

        $ls = [];

        /**
         * @var ManagedCustomer|Campaign|Budget $adwordsObject
         */
        foreach ($result->entries as $adwordsObject) {
            $ls[] = AdwordsObjectParser::readFieldsFromAdwordsObject($this->fieldMap, $adwordsObject, $keepSourceObject);
        }

        return $ls;
    }

    function fetchOne($keepSourceObject = FALSE)
    {
        $ls = $this->fetch($keepSourceObject);

        if (empty($ls)) {
            throw new \Exception('Not found', 404);
        }

        return $ls[0];
    }

    function fetchAll($keepSourceObject = FALSE): array
    {
        return $this->fetch($keepSourceObject);
    }

    function during(DateTime $start, DateTime $end): ReadInterface
    {
        throw new \Exception('Filtering by date range is not supported by regular Service.get() requests');
    }
}