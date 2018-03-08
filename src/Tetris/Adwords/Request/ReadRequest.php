<?php

namespace Tetris\Adwords\Request;

use Tetris\Adwords\Request;
use Tetris\Adwords\Request\Read\ReadInterface;
use Tetris\Adwords\Client;
use Google\AdsApi\AdWords\v201710\cm\Selector;
use Google\AdsApi\AdWords\v201710\cm\Predicate;
use Google\AdsApi\AdWords\v201710\cm\Paging;

abstract class ReadRequest extends Request implements ReadInterface
{
    /**
     * @var Selector $selector
     */
    protected $selector;

    /**
     * @var array $fieldMap
     */
    protected $fieldMap;

    /**
     * @var array $fieldMap
     */
    protected $predicates;

    function __construct(Client $client, string $className, array $fieldMap, $serviceName = null)
    {
        $this->client = $client;
        $this->className = $className;
        $this->init($serviceName);
        $this->selector = new Selector();
        $this->fieldMap = self::normalizeFieldMaps($fieldMap);
        $this->selector->setFields(array_keys($this->fieldMap));
        $this->predicates = [];
        $this->selector->setPredicates($this->predicates);
    }

    abstract protected function init($serviceName);

    function where(string $field, $value, $operator = 'EQUALS'): ReadInterface
    {
        $this->predicates[] = new Predicate(
            $field,
            $operator,
            is_array($value) ? $value : [$value]
        );
        $this->selector->setPredicates($this->predicates);
        return $this;
    }
}