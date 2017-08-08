<?php

namespace Tetris\Adwords;


use Tetris\MonoStash\MonoStash;

class Logger extends MonoStash
{
    function __construct()
    {
        parent::__construct('adwords');
    }
}
