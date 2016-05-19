<?php

namespace Tetris\Adwords\Request\Read;


interface ReadInterface
{
    function fetchOne();

    function fetchAll(): array;

    function where(string $field, $value, $operator = 'EQUALS'): self;
}