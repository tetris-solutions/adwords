<?php

namespace Tetris\Adwords;

class ReportMap
{
    private static $cache = [];

    static function list()
    {
        $files = scandir(__DIR__ . "/../../reports");
        $extensionLength = strlen('.json');

        $ls = [];

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;

            $ls[] = substr($file, 0, -$extensionLength);
        }

        return $ls;
    }

    static function get(string $name)
    {
        if (!array_key_exists($name, self::$cache)) {
            self::$cache[$name] = json_decode(
                file_get_contents(__DIR__ . "/../../reports/{$name}.json"),
                true
            );
        }

        return self::$cache[$name];
    }
}
