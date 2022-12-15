<?php

namespace Drupal\Tests\migrate_conditions\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\Plugin\MigrationInterface;

/**
 * Tests configuring the source of condition plugins.
 *
 * @group migrate_conditions
 */
class ConfiguredSourceTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'migrate',
    'migrate_conditions',
    'migrate_conditions_test_migrations',
  ];

  /**
   * Tests a crazy migration with wild configuration.
   */
  public function testConfigredSource() {
    $manager = \Drupal::service('plugin.manager.migration');
    $migration = $manager->createInstance('configured_source_test');
    $executable = new MigrateExecutable($migration);
    $result = $executable->import();
    $this->assertSame(MigrationInterface::RESULT_COMPLETED, $result);
    $expected = [
      'id' => 17,
      'array_is_array' => TRUE,
      'number_gate_open' => 17,
      'number_gate_closed' => 100,
      'tricky_inheritance' => TRUE,
      'switch_test' => 'second',
      'iterate' => 'this is a string',
      'iterate_again' => 'this is a string',
      'do_not_iterate' => 100,
      'nully' => TRUE,
      'double' => TRUE,
      'double_trouble' => FALSE,
    ];
    $this->assertEquals($expected, \Drupal::config('configured_source_test.settings')->get());
  }

}
