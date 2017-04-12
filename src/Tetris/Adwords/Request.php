<?php

namespace Tetris\Adwords;

require_once __DIR__ . '/logger.php';

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

    protected static function normalizeFieldMaps(array $fieldMap): array
    {
        return array_keys($fieldMap) !== range(0, count($fieldMap) - 1)
            ? $fieldMap
            : array_combine($fieldMap, $fieldMap);
    }

    protected function track(array $metaData)
    {
        global $logger;

        $logger->debug("Adwords Request ~ " . get_class($this), array_merge([
            'service_name' => $this->className,
            'stack' => (new \Exception('None'))->getTraceAsString()
        ], $metaData));
    }
}