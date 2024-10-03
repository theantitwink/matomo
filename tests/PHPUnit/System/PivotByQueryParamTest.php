<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\System;

use Piwik\Config;
use Piwik\Date;
use Piwik\Tests\Fixtures\ManyVisitsWithMockLocationProvider;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * @group Core
 * @group PivotByQueryParamTest
 */
class PivotByQueryParamTest extends SystemTestCase
{
    /**
     * @var ManyVisitsWithMockLocationProvider
     */
    public static $fixture = null;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        Config::getInstance()->General['pivot_by_filter_enable_fetch_by_segment'] = 1;
    }

    public function testPivotBySubtableDimensionCreatesCorrectPivotTable()
    {
        $this->assertApiResponseEqualsExpected("Referrers.getKeywords", array(
            'idSite' => self::$fixture->idSite,
            'date' => Date::factory(self::$fixture->dateTime)->toString(),
            'period' => 'week',
            'pivotBy' => 'Referrers.SearchEngine',
            'pivotByColumn' => 'nb_visits',
            'pivotByColumnLimit' => -1,
            'disable_queued_filters' => 1 // test that prepending doesn't happen w/ this
        ));
    }

    public function testPivotBySubtableDimensionWhenEntireHirearchyIsNotLoaded()
    {
        $this->assertApiResponseEqualsExpected("Referrers.getKeywords", array(
            'idSite' => self::$fixture->idSite,
            'date' => Date::factory(self::$fixture->dateTime)->toString(),
            'period' => 'week',
            'pivotBy' => 'Referrers.SearchEngine',
            'pivotByColumn' => '', // also test default pivot column
            'pivotByColumnLimit' => -1,
            'expanded' => 0
        ));
    }

    public function testPivotBySubtableDimensionCreatesCorrectPivotTableWhenPeriodIsDateRange()
    {
        $this->assertApiResponseEqualsExpected("Referrers.getKeywords", array(
            'idSite' => self::$fixture->idSite,
            'date' => '2009-12-29,2010-01-10',
            'period' => 'range',
            'pivotBy' => 'Referrers.SearchEngine'
        ));
    }

    public function testPivotBySegmentCreatesCorrectPivotTable()
    {
        $this->assertApiResponseEqualsExpected("Referrers.getKeywords", array(
            'idSite' => self::$fixture->idSite,
            'date' => Date::factory(self::$fixture->dateTime)->toString(),
            'period' => 'week',
            'pivotBy' => 'UserCountry.City',
            'pivotByColumn' => 'nb_visits',
            'pivotByColumnLimit' => -1
        ));
    }

    public function testPivotBySegmentCreatesCorrectPivotTableWhenSegmentUsedInRequest()
    {
        $this->assertApiResponseEqualsExpected("Referrers.getKeywords", array(
            'idSite' => self::$fixture->idSite,
            'date' => Date::factory(self::$fixture->dateTime)->toString(),
            'period' => 'week',
            'segment' => 'browserCode==FF',
            'pivotBy' => 'UserCountry.City',
            'pivotByColumn' => 'nb_visits',
            'pivotByColumnLimit' => -1
        ));
    }

    public function testPivotBySegmentCreatesCorrectPivotTableWhenPeriodIsRange()
    {
        $this->assertApiResponseEqualsExpected("Referrers.getKeywords", array(
            'idSite' => self::$fixture->idSite,
            'date' => '2009-12-29,2010-01-10',
            'period' => 'range',
            'pivotBy' => 'UserCountry.City'
        ));
    }

    public function testPivotByParamPlaysNiceWithOtherQueryParams()
    {
        $this->assertApiResponseEqualsExpected("Referrers.getKeywords", array(
            'idSite' => self::$fixture->idSite,
            'date' => Date::factory(self::$fixture->dateTime)->toString(),
            'period' => 'week',
            'pivotBy' => 'Referrers.SearchEngine',
            'pivotByColumn' => 'nb_visits',
            'pivotByColumnLimit' => -1,
            'flat' => 1,
            'totals' => 1,
            'disable_queued_filters' => 1,
            'disable_generic_filters' => 1,
            'showColumns' => 'Google,Bing'
        ));
    }

    // TODO: known issue: some segment/report relationships are more complicated; for example, UserCountry.GetCity labels are combinations
    // of city, region & country dimensions, so the segment to get an intersected table needs all 3 of those.
    public function testPivotByParamPlaysNiceWithQueuedFilters()
    {
        $this->markTestSkipped();
        $this->assertApiResponseEqualsExpected("DevicesDetection.getBrowsers", array( // should have logo metadata in output
            'idSite' => self::$fixture->idSite,
            'date' => Date::factory(self::$fixture->dateTime)->toString(),
            'period' => 'week',
            'pivotBy' => 'UserCountry.City', // testing w/ report that has no subtable report
            'pivotByColumn' => 'nb_visits',
            'pivotByColumnLimit' => -1
        ));
    }

    public function testPivotByParamWorksWithReportWhoseSubtableIsSelf()
    {
        $this->assertApiResponseEqualsExpected("Actions.getPageUrls", array(
            'idSite' => self::$fixture->idSite,
            'date' => Date::factory(self::$fixture->dateTime)->toString(),
            'period' => 'week',
            'pivotBy' => 'Actions.PageUrl',
            'pivotByColumn' => 'nb_hits',
            'pivotByColumnLimit' => -1
        ));
    }

    public function testPivotByParamWorksWithColumnLimiting()
    {
        $this->assertApiResponseEqualsExpected("Referrers.getKeywords", array(
            'idSite' => self::$fixture->idSite,
            'date' => Date::factory(self::$fixture->dateTime)->toString(),
            'period' => 'week',
            'pivotBy' => 'UserCountry.City',
            'pivotByColumn' => 'nb_visits',
            'pivotByColumnLimit' => 2
        ));
    }

    public function testPivotByParamWorksWithJsonOutput()
    {
        $this->assertApiResponseEqualsExpected("Referrers.getKeywords", array(
            'idSite' => self::$fixture->idSite,
            'date' => Date::factory(self::$fixture->dateTime)->toString(),
            'period' => 'week',
            'format' => 'json',
            'pivotBy' => 'UserCountry.City',
            'pivotByColumn' => 'nb_visits',
            'pivotByColumnLimit' => -1
        ));
    }

    public function testPivotByParamWorksWithCsvOutput()
    {
        $this->assertApiResponseEqualsExpected("Referrers.getKeywords", array(
            'idSite' => self::$fixture->idSite,
            'date' => Date::factory(self::$fixture->dateTime)->toString(),
            'period' => 'week',
            'format' => 'csv',
            'pivotBy' => 'UserCountry.City',
            'pivotByColumn' => 'nb_visits',
            'pivotByColumnLimit' => -1
        ));
    }

    public function testPivotByParamPlaysNiceWithDataTableMaps()
    {
        $this->assertApiResponseEqualsExpected("Referrers.getKeywords", array(
            'idSite' => 'all',
            'date' => '2010-01-01,2010-01-07',
            'period' => 'day',
            'pivotBy' => 'UserCountry.City',
            'pivotByColumn' => 'nb_visits',
            'pivotByColumnLimit' => -1
        ));
    }

    public function testPivotByParamWorksWithCustomDimension()
    {
        $this->assertApiResponseEqualsExpected("UserCountry.getCountry", [
            'idSite' => self::$fixture->idSite,
            'date' => Date::factory(self::$fixture->dateTime)->toString(),
            'period' => 'week',
            'pivotBy' => 'CustomDimension.CustomDimension' . self::$fixture->customDimensionId,
        ]);
    }

    public function testPivotByParamWorksWithCustomDimensionReport()
    {
        $this->assertApiResponseEqualsExpected("CustomDimensions.getCustomDimension", [
            'idSite' => self::$fixture->idSite,
            'date' => Date::factory(self::$fixture->dateTime)->toString(),
            'period' => 'week',
            'idDimension' => self::$fixture->customDimensionId,
            'pivotBy' => 'UserCountry.City',
        ]);
    }

    public function testPivotByParamFailsWithCustomDimensionAndMultipleSites()
    {
        $this->assertApiResponseEqualsExpected("UserCountry.getCountry", [
            'idSite' => 'all',
            'date' => Date::factory(self::$fixture->dateTime)->toString(),
            'period' => 'week',
            'pivotBy' => 'CustomDimension.CustomDimension' . self::$fixture->customDimensionId,
        ]);
    }

    public function assertApiResponseEqualsExpected($apiMethod, $queryParams)
    {
        parent::assertApiResponseEqualsExpected($apiMethod, $queryParams);
    }
}

PivotByQueryParamTest::$fixture = new ManyVisitsWithMockLocationProvider();
