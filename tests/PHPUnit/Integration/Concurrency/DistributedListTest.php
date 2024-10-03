<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Concurrency;

use Piwik\Common;
use Piwik\Concurrency\DistributedList;
use Piwik\Date;
use Piwik\Db;
use Piwik\Option;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Core
 */
class DistributedListTest extends IntegrationTestCase
{
    public const TEST_OPTION_NAME = 'test.distributed.list';

    public static $defaultOptionValues = array(
        'val1',
        'val2',
        'val3',
        'val4'
    );

    /**
     * @var DistributedList
     */
    private $distributedList;

    public function setUp(): void
    {
        parent::setUp();

        $this->distributedList = new DistributedList(self::TEST_OPTION_NAME);

        $this->initOptionValue();
    }

    public function testGetAllCorrectlyReturnsItemsInOption()
    {
        $list = $this->distributedList->getAll();
        $this->assertEquals(self::$defaultOptionValues, $list);
    }

    public function testGetAllReturnsValueInOptionIfOptionCacheHasSeparateValue()
    {
        // get option so cache is loaded
        Option::get(self::TEST_OPTION_NAME);

        // set option value to something else
        $newList = array('1', '2', '3');
        $this->initOptionValue($newList);

        // test option is now different
        $list = $this->distributedList->getAll();
        $this->assertEquals($newList, $list);
    }

    public function testSetAllCorrectlySetsNormalListInOption()
    {
        $newList = array('1', '2', '3');
        $this->distributedList->setAll($newList);

        $optionValue = $this->getOptionValueForList();
        $this->assertEquals(serialize($newList), $optionValue);

        $list = $this->distributedList->getAll();
        $this->assertEquals($newList, $list);
    }

    public function testSetAllCorrectlyConvertsItemsToStringBeforePersistingToOption()
    {
        $newList = array('1', Date::factory('2015-02-03'), 4.5);
        $this->distributedList->setAll($newList);

        $optionValue = $this->getOptionValueForList();
        $expectedOptionList = array('1', '2015-02-03', '4.5');
        $this->assertEquals(serialize($expectedOptionList), $optionValue);

        $list = $this->distributedList->getAll();
        $this->assertEquals($expectedOptionList, $list);
    }

    public function testAddAddsOneItemToListInOptionTableIfItemIsNotArray()
    {
        $this->distributedList->add('val5');

        $expectedOptionList = array('val1', 'val2', 'val3', 'val4', 'val5');
        $this->assertEquals(serialize($expectedOptionList), $this->getOptionValueForList());
    }

    public function testAddAddsMultipleItemsToListInOptionTableIfItemsIsArray()
    {
        $this->distributedList->add(array('val5', Date::factory('2015-03-04')));

        $expectedOptionList = array('val1', 'val2', 'val3', 'val4', 'val5', '2015-03-04');
        $this->assertEquals(serialize($expectedOptionList), $this->getOptionValueForList());
    }

    public function testRemoveRemovesSingleItemByValueInOptionTableIfItemIsNotArray()
    {
        $this->distributedList->remove('val2');

        $expectedOptionList = array('val1', 'val3', 'val4');
        $this->assertEquals(serialize($expectedOptionList), $this->getOptionValueForList());
    }

    public function testRemoveRemovesMultipleItemsByValueInOptionTableIfItemIsArray()
    {
        $this->distributedList->remove(array('val2', 'val4'));

        $expectedOptionList = array('val1', 'val3');
        $this->assertEquals(serialize($expectedOptionList), $this->getOptionValueForList());
    }

    public function testRemoveByIndexRemovesSingleItemByIndexInOptionTableIfArgIsIndex()
    {
        $this->distributedList->removeByIndex(2);

        $expectedOptionList = array('val1', 'val2', 'val4');
        $this->assertEquals(serialize($expectedOptionList), $this->getOptionValueForList());
    }

    public function testRemoveByIndexRemovesMultipleItemsByIndexInOptionTableIfArgIsArray()
    {
        $this->distributedList->removeByIndex(array(1, 3));

        $expectedOptionList = array('val1', 'val3');
        $this->assertEquals(serialize($expectedOptionList), $this->getOptionValueForList());
    }

    private function initOptionValue($data = false)
    {
        $data = $data ?: self::$defaultOptionValues;

        $optionTable = Common::prefixTable('option');
        Db::query(
            "INSERT INTO `$optionTable` (option_name, option_value, autoload) VALUES (?, ?, ?)
                   ON DUPLICATE KEY UPDATE option_value = ?",
            array(self::TEST_OPTION_NAME, serialize($data), 0, serialize($data))
        );
    }

    private function getOptionValueForList()
    {
        $optionTable = Common::prefixTable('option');
        return Db::fetchOne("SELECT option_value FROM `$optionTable` WHERE option_name = ?", array(self::TEST_OPTION_NAME));
    }
}
