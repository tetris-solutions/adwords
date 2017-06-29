<?php

namespace Tetris\Adwords\Request\Read;

use DateTime;
use Page;
use Paging;
use Predicate;

use Campaign;
use Budget;
use ManagedCustomer;

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

        $this->track([
            'field_count' => count($this->selector->fields),
            'predicate_count' => count($this->selector->predicates),
//            'fields' => $this->selector->fields,
//            'predicates' => array_map(function (Predicate $predicate) {
//                return [
//                    'field' => $predicate->field,
//                    'operator' => $predicate->operator,
//                    'value' => is_array($predicate->values) && count_chars($predicate->values) > 10
//                        ? '> 10 values'
//                        : $predicate->values
//                ];
//            }, $this->selector->predicates)
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