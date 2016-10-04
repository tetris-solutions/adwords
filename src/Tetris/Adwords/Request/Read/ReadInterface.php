<?php

namespace Tetris\Adwords\Request\Read;

use DateTime;

interface ReadInterface
{
    function fetchOne($keepSourceObject = FALSE);

    function fetchAll($keepSourceObject = FALSE): array;

    function where(string $field, $value, $operator = 'EQUALS'): self;

    function limit(int $count, $offset = 0): self;

    function during(DateTime $start, DateTime $end): self;
}