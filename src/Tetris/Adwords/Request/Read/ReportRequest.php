<?php

namespace Tetris\Adwords\Request\Read;

use Tetris\Adwords\Exceptions\NullReportException;
use Tetris\Adwords\AdwordsObjectParser;
use Tetris\Adwords\Request\ReadRequest;
use ReportUtils;
use ReportDefinition;
use DateTime;
use DateRange;
use stdClass;
use Predicate;

class ReportRequest extends ReadRequest
{
    protected function init($serviceName = null)
    {
        if (!class_exists('ReportDefinition', false)) {

            $googleAdsUtilsDir = $this->client->getConfig('GOOGLEADS_LIB_UTILS_DIR');

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

    function limit(int $count, $offset = 0): ReadInterface
    {
        throw new \Exception('Paging is not supported for reports', 500);
    }

    private function fetch(): array
    {
        $report = new ReportDefinition();
        $report->selector = $this->selector;
        $report->reportName = "{$this->className} #" . uniqid();
        $report->reportType = $this->className;
        $report->dateRangeType = 'CUSTOM_DATE';
        $report->downloadFormat = 'XML';

        $this->track([
            'field_count' => count($this->selector->fields),
            'predicate_count' => count($this->selector->predicates),
//            'fields' => $this->selector->fields,
//            'date_range' => [$this->selector->dateRange->min, $this->selector->dateRange->max],
//            'predicates' => array_map(function (Predicate $predicate) {
//                return [
//                    'field' => $predicate->field,
//                    'operator' => $predicate->operator,
//                    'value' => is_array($predicate->values) && count_chars($predicate->values) > 10
//                        ? '> 10 values'
//                        : $predicate->values
//                ];
//            }, $this->selector->predicates)
        ]);

        $downloader = new ReportUtils();

        $xml = simplexml_load_string($downloader->DownloadReport($report, null, $this->client));
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