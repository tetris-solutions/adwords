<?php

namespace Tetris\Adwords\Request\Read;

use DateTime;
use Google\AdsApi\AdWords\v201705\cm\Page;
use Google\AdsApi\AdWords\v201705\cm\Predicate;
use Google\AdsApi\AdWords\v201705\cm\Paging;

use Google\AdsApi\AdWords\v201705\cm\Campaign;
use Google\AdsApi\AdWords\v201705\cm\Budget;
use Google\AdsApi\AdWords\v201705\mcm\ManagedCustomer;

use Google\AdsApi\AdWords\v201705\cm\CampaignService;
use Google\AdsApi\AdWords\v201705\cm\BudgetService;
use Google\AdsApi\AdWords\v201705\mcm\ManagedCustomerService;

use Tetris\Adwords\Request\ReadRequest;
use Tetris\Adwords\AdwordsObjectParser;

class GetRequest extends ReadRequest
{
    /**
     * @var CampaignService|BudgetService|ManagedCustomerService $service
     */
    private $service;

    /**
     * @var string|null
     */
    private $subClassName;

    function subClass(string $className)
    {
        $this->subClassName = $className;

        return $this;
    }

    protected function init($serviceName = null)
    {
        $this->service = $this->client->getAdWordsServices(
            isset($serviceName) ? $serviceName : $this->className . 'Service'
        );
    }

    function limit(int $count, $offset = 0): ReadInterface
    {
        $this->selector->setPaging(new Paging($offset, $count));
        return $this;
    }

    private function fetch(bool $keepSourceObject): array
    {
        if (empty($this->selector->getPaging())) {
            $this->limit(500);
        }

        $this->track([
            'field_count' => count($this->selector->getFields()),
            'predicate_count' => count($this->selector->getPredicates())
        ]);

        $getMethod = 'get' . $this->subClassName;

        /**
         * @var Page $result
         */
        $result = $this->service->$getMethod($this->selector);

        if (empty($result->entries)) {
            return [];
        }

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