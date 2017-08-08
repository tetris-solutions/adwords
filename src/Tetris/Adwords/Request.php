<?php

namespace Tetris\Adwords;

use Exception;
use stdClass;

abstract class Request
{
    /**
     * @var Client $client
     */
    protected $client;

    /**
     * @var string|null $className
     */
    protected $className;

    /**
     * @var array $fieldMap
     */
    protected $fieldMap;

    /**
     * @var Logger
     */
    private $logger;

    protected static function normalizeFieldMaps(array $fieldMap): array
    {
        return array_keys($fieldMap) !== range(0, count($fieldMap) - 1)
            ? $fieldMap
            : array_combine($fieldMap, $fieldMap);
    }

    /**
     * @return stdClass|null
     */
    private function getParentProject()
    {

        $vendorPos = strpos(__DIR__, '/vendor/');

        if ($vendorPos === false) return null;

        $composer = substr(__DIR__, 0, $vendorPos) . '/composer.json';

        if (!file_exists($composer)) return null;

        return json_decode(file_get_contents($composer));
    }

    protected function track(array $metaData)
    {
        $metaData['service_name'] = $this->className;

        $parentProject = $this->getParentProject();

        $metaData['project'] = $parentProject->name ?? null;

        if (!$this->logger) {
            $this->logger = new Logger();
        }

        $this->logger->debug(
            "Adwords Request ~ " . (new \ReflectionClass($this))->getShortName(),
            $metaData
        );
    }
}