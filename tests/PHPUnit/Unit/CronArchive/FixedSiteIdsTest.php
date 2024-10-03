<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\CronArchive;

use Piwik\CronArchive\FixedSiteIds;

/**
 * @group Core
 */
class FixedSiteIdsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var FixedSiteIds
     */
    private $fixedSiteIds;

    public function setUp(): void
    {
        $this->fixedSiteIds = new FixedSiteIds(array(1,2,5,9));
    }

    public function testConstructWithEmptyValue()
    {
        $siteIds = new FixedSiteIds(null);
        $this->assertEquals(0, $siteIds->getNumSites());
        $this->assertNull($siteIds->getNextSiteId());
    }

    public function testGetNumSites()
    {
        $this->assertEquals(4, $this->fixedSiteIds->getNumSites());
    }

    public function testGetNumProcessedWebsitesGetNextSiteId()
    {
        $this->assertEquals(0, $this->fixedSiteIds->getNumProcessedWebsites());
        $this->assertEquals(1, $this->fixedSiteIds->getNextSiteId());
        $this->assertEquals(1, $this->fixedSiteIds->getNumProcessedWebsites());
        $this->assertEquals(2, $this->fixedSiteIds->getNextSiteId());
        $this->assertEquals(2, $this->fixedSiteIds->getNumProcessedWebsites());
        $this->assertEquals(5, $this->fixedSiteIds->getNextSiteId());
        $this->assertEquals(3, $this->fixedSiteIds->getNumProcessedWebsites());
        $this->assertEquals(9, $this->fixedSiteIds->getNextSiteId());
        $this->assertEquals(4, $this->fixedSiteIds->getNumProcessedWebsites());

        $this->assertNull($this->fixedSiteIds->getNextSiteId());
        $this->assertEquals(4, $this->fixedSiteIds->getNumProcessedWebsites());
    }
}
