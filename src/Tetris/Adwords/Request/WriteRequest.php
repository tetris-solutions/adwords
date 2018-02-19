<?php

namespace Tetris\Adwords\Request;

use Tetris\Adwords\Request;

use Google\AdsApi\AdWords\v201705\cm\CampaignService;
use Google\AdsApi\AdWords\v201705\cm\BudgetService;
use Google\AdsApi\AdWords\v201705\mcm\ManagedCustomerService;

abstract class WriteRequest extends Request
{
    protected $values;
    protected $operator;
}