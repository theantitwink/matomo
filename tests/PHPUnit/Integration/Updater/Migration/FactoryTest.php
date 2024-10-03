<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Updater\Migration;

use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Updater\Migration;
use Piwik\Updater\Migration\Db\Factory as DbFactory;
use Piwik\Updater\Migration\Plugin\Factory as PluginFactory;
use Piwik\Updater\Migration\Config\Factory as ConfigFactory;

/**
 * @group Core
 * @group Updater
 * @group Migration
 */
class FactoryTest extends IntegrationTestCase
{
    /**
     * @var Migration\Factory
     */
    private $factory;

    public function setUp(): void
    {
        parent::setUp();

        $this->factory = new Migration\Factory(new DbFactory(), new PluginFactory(), new ConfigFactory());
    }

    public function testDbHoldsDatabaseFactory()
    {
        $this->assertTrue($this->factory->db instanceof DbFactory);
    }

    public function testPluginHoldsPluginFactory()
    {
        $this->assertTrue($this->factory->plugin instanceof PluginFactory);
    }

    public function testPluginHoldsConfigFactory()
    {
        $this->assertTrue($this->factory->config instanceof ConfigFactory);
    }
}
