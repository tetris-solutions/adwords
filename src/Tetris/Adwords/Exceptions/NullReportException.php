<?php

namespace Tetris\Adwords\Exceptions;

use Exception;

class NullReportException extends Exception
{
    public $report;
    public function __construct($report)
    {
        parent::__construct("Report result is NULL", 502);
        $this->report = $report;
    }
}
