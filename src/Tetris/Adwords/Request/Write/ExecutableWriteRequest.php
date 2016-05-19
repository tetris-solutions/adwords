<?php

namespace Tetris\Adwords\Request\Write;

use CampaignService;
use BudgetService;
use ManagedCustomerService;

use Tetris\Adwords\AdwordsObjectParser;
use Tetris\Adwords\Client;
use Tetris\Adwords\Request;

class ExecutableWriteRequest extends Request
{
    /**
     * @var array $values
     */
    protected $values;
    /**
     * @var string $operator
     */
    protected $operator;
    /**
     * @var CampaignService|BudgetService|ManagedCustomerService $service
     */
    protected $service;

    function __construct(Client $client, string $operator, string $className, array $values, $serviceName = null)
    {
        $this->client = $client;
        $this->className = $className;
        $this->values = $values;
        $this->operator = $operator;
        $this->service = $this->client->GetService(
            isset($serviceName) ? $serviceName : $this->className . 'Service'
        );
    }

    function returning(array $fieldMap)
    {
        $entityOperationClass = $this->className . 'Operation';

        /**
         * @var \CampaignOperation|\BudgetOperation
         */
        $operation = new $entityOperationClass();
        $operation->operand = AdwordsObjectParser::readFieldsFromArrayIntoAdwordsObject($this->className, $this->values);
        $operation->operator = $this->operator;

        $result = $this->service->mutate([$operation]);

        return AdwordsObjectParser::readFieldsFromAdwordsObject(self::normalizeFieldMaps($fieldMap), $result->value);
    }
}