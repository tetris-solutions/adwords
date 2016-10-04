<?php

namespace Tetris\Adwords\Request;

use Tetris\Adwords\Request;
use Tetris\Adwords\Request\Read\ReadInterface;
use Tetris\Adwords\Client;
use Selector;
use Predicate;
use Paging;

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

    function __construct(Client $client, string $className, array $fieldMap, $serviceName = null)
    {
        $this->client = $client;
        $this->className = $className;
        $this->init($serviceName);
        $this->selector = new Selector();
        $this->fieldMap = self::normalizeFieldMaps($fieldMap);
        $this->selector->fields = array_keys($this->fieldMap);
        $this->selector->predicates = [];
    }

    abstract protected function init($serviceName);

    function where(string $field, $value, $operator = 'EQUALS'): ReadInterface
    {
        $this->selector->predicates[] = new Predicate(
            $field,
            $operator,
            is_array($value) ? $value : [$value]
        );
        return $this;
    }
}