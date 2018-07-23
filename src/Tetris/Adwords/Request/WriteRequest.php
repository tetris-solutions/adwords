<?php

namespace Tetris\Adwords\Request;

use Tetris\Adwords\Request;

use Google\AdsApi\AdWords\v201806\cm\CampaignService;
use Google\AdsApi\AdWords\v201806\cm\BudgetService;
use Google\AdsApi\AdWords\v201806\mcm\ManagedCustomerService;

abstract class WriteRequest extends Request
{
    protected $values;
    protected $operator;
}