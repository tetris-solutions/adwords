<?php

namespace Tetris\Adwords\Request;

use Tetris\Adwords\Request;

use Google\AdsApi\AdWords\v201809\cm\CampaignService;
use Google\AdsApi\AdWords\v201809\cm\BudgetService;
use Google\AdsApi\AdWords\v201809\mcm\ManagedCustomerService;

abstract class WriteRequest extends Request
{
    protected $values;
    protected $operator;
}