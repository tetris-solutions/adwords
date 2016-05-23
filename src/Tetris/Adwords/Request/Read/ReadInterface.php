<?php

namespace Tetris\Adwords\Request\Read;

use DateTime;

interface ReadInterface
{
    function fetchOne();

    function fetchAll(): array;

    function where(string $field, $value, $operator = 'EQUALS'): self;

    function during(DateTime $start, DateTime $end): self;
}