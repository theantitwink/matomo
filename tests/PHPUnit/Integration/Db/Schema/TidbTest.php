<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Db\Schema;

use Piwik\Config\DatabaseConfig;
use Piwik\Db;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class TidbTest extends IntegrationTestCase
{
    public function testIsOptimizeInnoDBSupportedReturnsCorrectResult()
    {
        $schema = new Db\Schema\Tidb();
        $this->assertFalse($schema->isOptimizeInnoDBSupported());
    }

    public function testOptimize()
    {
        if (!DatabaseConfig::isTiDb()) {
            self::markTestSkipped('Tidb is not available');
        }

        // create two myisam tables
        Db::exec("CREATE TABLE table1 (a INT) ENGINE=MYISAM");
        Db::exec("CREATE TABLE table2 (b INT) ENGINE=MYISAM");

        // create two innodb tables
        Db::exec("CREATE TABLE table3 (c INT) ENGINE=InnoDB");
        Db::exec("CREATE TABLE table4 (d INT) ENGINE=InnoDB");

        $schema = Db\Schema::getInstance();

        // optimizing not available for TiDb
        $this->assertFalse($schema->optimizeTables(['table1', 'table2']));
        $this->assertFalse($schema->optimizeTables(['table1', 'table2', 'table3', 'table4']));
        $this->assertFalse($schema->optimizeTables(['table3', 'table4']));
        $this->assertFalse($schema->optimizeTables(['table3', 'table4'], true));
    }

    public function testGetDefaultCollationForCharsetReplacesUtf8mb4Binary(): void
    {
        if (DatabaseConfig::getConfigValue('schema') !== 'Tidb') {
            self::markTestSkipped('Tidb is not available');
        }

        $schema = Db\Schema::getInstance();

        self::assertSame(
            'utf8mb4_0900_ai_ci',
            $schema->getDefaultCollationForCharset('utf8mb4')
        );
    }

    /**
     * @dataProvider getTableCreateOptionsTestData
     */
    public function testTableCreateOptions(array $optionOverrides, string $expected): void
    {
        if (DatabaseConfig::getConfigValue('schema') !== 'Tidb') {
            self::markTestSkipped('Tidb is not available');
        }

        foreach ($optionOverrides as $name => $value) {
            DatabaseConfig::setConfigValue($name, $value);
        }

        $schema = Db\Schema::getInstance();

        self::assertSame($expected, $schema->getTableCreateOptions());
    }

    public function getTableCreateOptionsTestData(): iterable
    {
        yield 'default charset, empty collation' => [
            ['collation' => ''],
            'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ROW_FORMAT=DYNAMIC'
        ];

        yield 'override charset, empty collation' => [
            ['charset' => 'utf8mb3', 'collation' => ''],
            'ENGINE=InnoDB DEFAULT CHARSET=utf8mb3'
        ];

        yield 'default charset, override collation' => [
            ['collation' => 'utf8mb4_swedish_ci'],
            'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci ROW_FORMAT=DYNAMIC'
        ];

        yield 'override charset and collation' => [
            ['charset' => 'utf8mb3', 'collation' => 'utf8mb3_general_ci'],
            'ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci'
        ];
    }
}
