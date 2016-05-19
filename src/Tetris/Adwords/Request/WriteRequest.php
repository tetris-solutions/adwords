<?php

namespace Tetris\Adwords\Request;

use Tetris\Adwords\Request;

use CampaignService;
use BudgetService;
use ManagedCustomerService;

abstract class WriteRequest extends Request
{
    protected $values;
    protected $operator;
}