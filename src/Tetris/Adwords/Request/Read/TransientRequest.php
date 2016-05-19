<?php
namespace Tetris\Adwords\Request\Read;

use Tetris\Adwords\Client;

class TransientRequest
{
    private $fields;
    private $client;

    function __construct(Client $client, array $fields)
    {
        $this->client = $client;
        $this->fields = $fields;
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
            return new ReportRequest($this->client, $className, $this->fields);
        } else {
            return new GetRequest($this->client, $className, $this->fields, $serviceName);
        }
    }
}