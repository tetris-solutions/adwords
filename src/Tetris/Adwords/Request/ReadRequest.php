<?php

namespace Tetris\Adwords\Request;

use Tetris\Adwords\Request;
use Tetris\Adwords\Request\Read\ReadInterface;
use Tetris\Adwords\Client;
use Selector;
use Predicate;

abstract class ReadRequest extends Request implements ReadInterface
{
    /**
     * @var Selector $selector
     */
    protected $selector;

    /**
     * @var array $fields
     */
    protected $fields;

    function __construct(Client $client, string $className, array $fields, $serviceName = null)
    {
        parent::__construct($client, $className);
        $this->init($serviceName);

        $this->selector = new Selector();
        $this->setFields($fields);
        $this->selector->fields = array_keys($this->fields);
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

    private function setFields(array $fields)
    {
        $this->fields = self::isAssociativeArray($fields)
            ? $fields
            : array_combine($fields, $fields);
    }
}