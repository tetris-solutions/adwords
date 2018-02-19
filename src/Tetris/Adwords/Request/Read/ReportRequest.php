<?php

namespace Tetris\Adwords\Request\Read;

use Tetris\Adwords\Exceptions\NullReportException;
use Tetris\Adwords\AdwordsObjectParser;
use Tetris\Adwords\Request\ReadRequest;
use Google\AdsApi\AdWords\Reporting\v201705\ReportDownloader;
use Google\AdsApi\AdWords\Reporting\v201705\ReportDefinition;
use DateTime;
use Google\AdsApi\AdWords\v201705\cm\DateRange;
use stdClass;
use Google\AdsApi\AdWords\v201705\cm\Predicate;

class ReportRequest extends ReadRequest
{
    protected function init($serviceName = null)
    {
        if (!class_exists('ReportDefinition', false)) {

            $googleAdsUtilsDir = $this->client->getConfig('GOOGLEADS_LIB_UTILS_DIR');

            require_once $googleAdsUtilsDir . '/ReportDefinition.php';
            require_once $googleAdsUtilsDir . '/ReportDownloader.php';
        }
    }

    function during(DateTime $start, DateTime $end): ReadInterface
    {
        $this->selector->setDateRange(new DateRange($start->format('Ymd'), $end->format('Ymd')));
        return $this;
    }

    function limit(int $count, $offset = 0): ReadInterface
    {
        throw new \Exception('Paging is not supported for reports', 500);
    }

    private function fetch(): array
    {
        $report = new ReportDefinition();
        $report->setSelector($this->selector);
        $report->setReportName("{$this->className} #" . uniqid());
        $report->setReportType($this->className);
        $report->setDateRangeType('CUSTOM_DATE');
        $report->setDownloadFormat('XML');

        $this->track([
            'field_count' => count($this->selector->getFields()),
            'predicate_count' => count($this->selector->getPredicates())
        ]);

        $downloader = new ReportDownloader($this->client->getSession());

        $reportDownloadResult = $downloader->downloadReport($report, null);

        $xml = simplexml_load_string($reportDownloadResult->getAsString());
        $json = json_encode($xml);

        $result = json_decode($json);

        if (empty($result->table->row)) {
            throw new NullReportException($report, $result);
        }

        $rows = is_array($result->table->row)
            ? $result->table->row
            : [$result->table->row];

        return array_map(function (stdClass $row) {
            return AdwordsObjectParser::normalizeReportObject($this->className, $this->fieldMap, $row->{'@attributes'});
        }, $rows);
    }

    function fetchOne($keepSourceObject = FALSE)
    {
        return $this->fetch()[0];
    }

    function fetchAll($keepSourceObject = FALSE): array
    {
        return $this->fetch();
    }
}