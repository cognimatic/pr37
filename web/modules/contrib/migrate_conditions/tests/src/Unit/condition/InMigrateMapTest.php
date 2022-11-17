<?php

namespace Drupal\Tests\migrate_conditions\Unit\condition;

use Drupal\migrate_conditions\Plugin\migrate_conditions\condition\InMigrateMap;
use Drupal\Tests\UnitTestCase;

/**
 * Tests the in_migrate_map condition plugin.
 *
 * @group migrate_conditions
 * @coversDefaultClass \Drupal\migrate_conditions\Plugin\migrate_conditions\condition\InMigrateMap
 */
class InMigrateMapTest extends UnitTestCase {

  /**
   * @covers ::evaluate
   * @dataProvider providerTestEvaluate
   */
  public function testEvaluate($source, $lookup, $configuration, $expected) {
    $row = $this->createMock('Drupal\migrate\Row');
    $migrate_lookup = $this->prophesize('\Drupal\migrate\MigrateLookupInterface');
    $migrate_lookup->lookup((array) $configuration['migration'], (array) $source)->willReturn($lookup);
    $migration_plugin_manager = $this->createMock('\Drupal\migrate\Plugin\MigrationPluginManagerInterface');
    $migration_plugin_manager->expects($this->exactly(count((array) $configuration['migration'])))
      ->method('createInstance')
      ->willReturn('something');

    $condition = new InMigrateMap($configuration, 'in_migrate_map', [], $migrate_lookup->reveal(), $migration_plugin_manager);
    $this->assertSame($expected, $condition->evaluate($source, $row));
  }

  /**
   * Data provider for ::testEvaluate().
   */
  public function providerTestEvaluate() {
    return [
      [
        'source' => 123,
        'lookup' => [],
        'configuration' => [
          'migration' => 'foo',
        ],
        'expected' => FALSE,
      ],
      [
        'source' => 123,
        'lookup' => [],
        'configuration' => [
          'migration' => 'foo',
          'negate' => TRUE,
        ],
        'expected' => TRUE,
      ],
      [
        'source' => 123,
        'lookup' => [
          ['nid' => 44],
        ],
        'configuration' => [
          'migration' => [
            'foo',
          ],
        ],
        'expected' => TRUE,
      ],
      [
        'source' => 123,
        'lookup' => [],
        'configuration' => [
          'migration' => [
            'foo',
            'bar',
          ],
        ],
        'expected' => FALSE,
      ],
      [
        'source' => 123,
        'lookup' => [
          ['nid' => NULL],
        ],
        'configuration' => [
          'migration' => 'foo',
        ],
        'expected' => FALSE,
      ],
      [
        'source' => 123,
        'lookup' => [
          ['nid' => NULL],
        ],
        'configuration' => [
          'migration' => 'foo',
          'include_skipped' => TRUE,
        ],
        'expected' => TRUE,
      ],
    ];
  }

  /**
   * @covers ::__construct
   */
  public function testConstructorExceptions() {
    $migrate_lookup = $this->createMock('\Drupal\migrate\MigrateLookupInterface');
    $migration_plugin_manager = $this->createMock('\Drupal\migrate\Plugin\MigrationPluginManagerInterface');
    $migration_plugin_manager->expects($this->once())
      ->method('createInstance')
      ->willReturn(NULL);

    $configuration = [
      'migration' => 'foo',
    ];

    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('The migration configured for in_migrate_map could not be loaded: foo');
    $condition = new InMigrateMap($configuration, 'in_migrate_map', [], $migrate_lookup, $migration_plugin_manager);
  }

}
