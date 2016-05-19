<?php
namespace Tetris\Adwords\Request\Read;

use Tetris\Adwords\Client;

class TransientReadRequest
{
    private $fieldMap;
    private $client;

    function __construct(Client $client, array $fieldMap)
    {
        $this->client = $client;
        $this->fieldMap = $fieldMap;
    }

    /**
     * @param string $className
     * @param $serviceName
     * @return ReadInterface
     */
    function from(string $className, $serviceName = null): ReadInterface
    {
        $isAllUpperCase = strtoupper($className) === $className;

        if ($isAllUpperCase) {
            return new ReportRequest($this->client, $className, $this->fieldMap);
        } else {
            return new GetRequest($this->client, $className, $this->fieldMap, $serviceName);
        }
    }
}