<?php

namespace Tetris\Adwords;
use Exception;
use Google\AdsApi\AdWords\v201806\mcm\AccountLabelService;
use Google\AdsApi\AdWords\v201806\cm\AdCustomizerFeedService;
use Google\AdsApi\AdWords\v201806\cm\AdGroupAdService;
use Google\AdsApi\AdWords\v201806\cm\AdGroupBidModifierService;
use Google\AdsApi\AdWords\v201806\cm\AdGroupCriterionService;
use Google\AdsApi\AdWords\v201806\cm\AdGroupExtensionSettingService;
use Google\AdsApi\AdWords\v201806\cm\AdGroupFeedService;
use Google\AdsApi\AdWords\v201806\cm\AdGroupService;
use Google\AdsApi\AdWords\v201806\cm\AdParamService;
use Google\AdsApi\AdWords\v201806\rm\AdwordsUserListService;
use Google\AdsApi\AdWords\v201806\cm\BatchJobOpsService;
use Google\AdsApi\AdWords\v201806\cm\BatchJobService;
use Google\AdsApi\AdWords\v201806\cm\BiddingStrategyService;
use Google\AdsApi\AdWords\v201806\billing\BudgetOrderService;
use Google\AdsApi\AdWords\v201806\cm\BudgetService;
use Google\AdsApi\AdWords\v201806\cm\CampaignBidModifierService;
use Google\AdsApi\AdWords\v201806\cm\CampaignCriterionService;
use Google\AdsApi\AdWords\v201806\cm\CampaignExtensionSettingService;
use Google\AdsApi\AdWords\v201806\cm\CampaignFeedService;
use Google\AdsApi\AdWords\v201806\cm\CampaignGroupPerformanceTargetService;
use Google\AdsApi\AdWords\v201806\cm\CampaignGroupService;
use Google\AdsApi\AdWords\v201806\cm\CampaignService;
use Google\AdsApi\AdWords\v201806\cm\CampaignSharedSetService;
use Google\AdsApi\AdWords\v201806\cm\ConversionTrackerService;
use Google\AdsApi\AdWords\v201806\cm\ConstantDataService;
use Google\AdsApi\AdWords\v201806\cm\CustomerExtensionSettingService;
use Google\AdsApi\AdWords\v201806\mcm\CustomerService;
use Google\AdsApi\AdWords\v201806\ch\CustomerSyncService;
use Google\AdsApi\AdWords\v201806\cm\DataService;
use Google\AdsApi\AdWords\v201806\cm\DraftAsyncErrorService;
use Google\AdsApi\AdWords\v201806\cm\DraftService;
use Google\AdsApi\AdWords\v201806\cm\FeedItemService;
use Google\AdsApi\AdWords\v201806\cm\FeedMappingService;
use Google\AdsApi\AdWords\v201806\cm\FeedService;
use Google\AdsApi\AdWords\v201806\cm\LabelService;
use Google\AdsApi\AdWords\v201806\cm\LocationCriterionService;
use Google\AdsApi\AdWords\v201806\mcm\ManagedCustomerService;
use Google\AdsApi\AdWords\v201806\cm\MediaService;
use Google\AdsApi\AdWords\v201806\cm\OfflineCallConversionFeedService;
use Google\AdsApi\AdWords\v201806\cm\OfflineConversionFeedService;
use Google\AdsApi\AdWords\v201806\cm\ReportDefinitionService;
use Google\AdsApi\AdWords\v201806\cm\SharedCriterionService;
use Google\AdsApi\AdWords\v201806\cm\SharedSetService;
use Google\AdsApi\AdWords\v201806\o\TargetingIdeaService;
use Google\AdsApi\AdWords\v201806\o\TrafficEstimatorService;
use Google\AdsApi\AdWords\v201806\cm\TrialAsyncErrorService;
use Google\AdsApi\AdWords\v201806\cm\TrialService;

class AdwordsServicesList
{
    private static $serviceList = [
        "AccountLabelService" => AccountLabelService::class,
    	"AdCustomizerFeedService" => AdCustomizerFeedService::class,
    	"AdGroupAdService" => AdGroupAdService::class,
    	"AdGroupBidModifierService" => AdGroupBidModifierService::class,
    	"AdGroupCriterionService" => AdGroupCriterionService::class,
    	"AdGroupExtensionSettingService" => AdGroupExtensionSettingService::class,
    	"AdGroupFeedService" => AdGroupFeedService::class,
    	"AdGroupService" => AdGroupService::class,
    	"AdParamService" => AdParamService::class,
    	"AdwordsUserListService" => AdwordsUserListService::class,
    	"BatchJobOpsService" => BatchJobOpsService::class,
    	"BatchJobService" => BatchJobService::class,
    	"BiddingStrategyService" => BiddingStrategyService::class,
    	"BudgetOrderService" => BudgetOrderService::class,
    	"BudgetService" => BudgetService::class,
    	"CampaignBidModifierService" => CampaignBidModifierService::class,
    	"CampaignCriterionService" => CampaignCriterionService::class,
    	"CampaignExtensionSettingService" => CampaignExtensionSettingService::class,
    	"CampaignFeedService" => CampaignFeedService::class,
    	"CampaignGroupPerformanceTargetService" => CampaignGroupPerformanceTargetService::class,
    	"CampaignGroupService" => CampaignGroupService::class,
    	"CampaignService" => CampaignService::class,
    	"CampaignSharedSetService" => CampaignSharedSetService::class,
        "ConstantDataService" => ConstantDataService::class,
    	"ConversionTrackerService" => ConversionTrackerService::class,
    	"CustomerExtensionSettingService" => CustomerExtensionSettingService::class,
    	"CustomerFeedService" => CustomerFeedService::class,
    	"CustomerService" => CustomerService::class,
    	"CustomerSyncService" => CustomerSyncService::class,
    	"DataService" => DataService::class,
    	"DraftAsyncErrorService" => DraftAsyncErrorService::class,
    	"DraftService" => DraftService::class,
    	"FeedItemService" => FeedItemService::class,
    	"FeedMappingService" => FeedMappingService::class,
    	"FeedService" => FeedService::class,
    	"LabelService" => LabelService::class,
    	"LocationCriterionService" => LocationCriterionService::class,
    	"ManagedCustomerService" => ManagedCustomerService::class,
    	"MediaService" => MediaService::class,
    	"OfflineCallConversionFeedService" => OfflineCallConversionFeedService::class,
    	"OfflineConversionFeedService" => OfflineConversionFeedService::class,
    	"ReportDefinitionService" => ReportDefinitionService::class,
    	"SharedCriterionService" => SharedCriterionService::class,
    	"SharedSetService" => SharedSetService::class,
    	"TargetingIdeaService" => TargetingIdeaService::class,
    	"TrafficEstimatorService" => TrafficEstimatorService::class,
    	"TrialAsyncErrorService" => TrialAsyncErrorService::class,
    	"TrialService" => TrialService::class
    ];

    static function get(string $name)
    {
        if (!array_key_exists($name, self::$serviceList)) {
            throw new Exception("This AdWords Service does not exist. :(");
        }

        return self::$serviceList[$name];
    }
}