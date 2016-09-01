<?php

namespace Tetris\Adwords\Exceptions;

use Exception;

class NullReportException extends Exception
{
    public $report;
    public $result;
    public function __construct($report, $result)
    {
        parent::__construct("Report result is NULL", 502);
        $this->report = $report;
        $this->result = $result;
    }
}
