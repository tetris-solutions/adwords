<?php

namespace Tetris\Adwords\Request\Read;

use Tetris\Adwords\AdwordsObjectParser;
use Tetris\Adwords\Request\ReadRequest;
use ReportUtils;
use ReportDefinition;
use DateTime;
use DateRange;
use stdClass;

class ReportRequest extends ReadRequest
{
    protected function init($serviceName = null)
    {
        if (!class_exists('ReportDefinition', false)) {

            $googleAdsUtilsDir = $this->client->getConfig('GOOGLEADS_LIB_UTILS_DIR');

            // '/../../../../vendor/googleads/googleads-php-lib/src/Google/Api/Ads/AdWords/Util/v201603';

            require_once $googleAdsUtilsDir . '/ReportUtils.php';
            require_once $googleAdsUtilsDir . '/ReportClasses.php';
        }

        $this->client->LoadService('ReportDefinitionService');
    }

    function during(DateTime $start, DateTime $end): ReadInterface
    {
        $this->selector->dateRange = new DateRange($start->format('Ymd'), $end->format('Ymd'));
        return $this;
    }

    private function fetch(): stdClass
    {
        $report = new ReportDefinition();
        $report->selector = $this->selector;
        $report->reportName = "{$this->className} #" . uniqid();
        $report->reportType = $this->className;
        $report->dateRangeType = 'CUSTOM_DATE';
        $report->downloadFormat = 'XML';

        $xml = simplexml_load_string(ReportUtils::DownloadReport($report, null, $this->client));
        $json = json_encode($xml);

        $result = json_decode($json);

        return $result;
    }

    /**
     * @param stdClass $attributes
     * @return array|mixed
     */
    private function readFieldsFromXmlObject($attributes)
    {
        $map = [];

        foreach ($this->fieldMap as $adwordsKey => $userKey) {
            $map[$userKey] = AdwordsObjectParser::getNormalizedField($adwordsKey, $attributes);
        }

        return AdwordsObjectParser::stripSingleValueFromArray($map);
    }

    function fetchOne()
    {
        $attributes = $this->fetch()->table->row->{'@attributes'};

        return $this->readFieldsFromXmlObject($attributes);
    }

    function fetchAll(): array
    {
        throw new \Exception('Not implemented');
    }
}