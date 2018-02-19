<?php

namespace Tetris\Adwords\Request\Write;

use Google\AdsApi\AdWords\v201705\cm\CampaignService;
use Google\AdsApi\AdWords\v201705\cm\BudgetService;
use Google\AdsApi\AdWords\v201705\mcm\ManagedCustomerService;

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

    private $result;

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
        if (empty($this->result)) {
            $this->execute();
        }

        return AdwordsObjectParser::readFieldsFromAdwordsObject(
            self::normalizeFieldMaps($fieldMap),
            $this->result->value[0]
        );
    }

    function execute()
    {
        $this->track([
            'operator' => $this->operator
        ]);

        $entityOperationClass = $this->className . 'Operation';

        /**
         * @var \CampaignOperation|\BudgetOperation
         */
        $operation = new $entityOperationClass();
        $operation->operand = AdwordsObjectParser::readFieldsFromArrayIntoAdwordsObject($this->className, $this->values);
        $operation->operator = $this->operator;

        $this->result = $this->service->mutate([$operation]);
    }
}