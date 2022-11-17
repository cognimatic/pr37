<?php

namespace Drupal\Tests\migrate_conditions\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\Plugin\MigrationInterface;

/**
 * Tests running a migration using the plugins herein.
 *
 * None of these cases is especially important on its own. As a whole
 * though this offers some reasonable assurance that the plugins work
 * in "real" migrations. The unit tests cover far more code, but this
 * helps us know that the code works with the rest of Drupal.
 *
 * @group migrate_conditions
 */
class MigrateConditionsPipelineTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['migrate', 'migrate_conditions'];

  /**
   * Returns test migration definition.
   *
   * @return array
   */
  public function getDefinition() {
    return [
      'source' => [
        'plugin' => 'embedded_data',
        'data_rows' => [],
        'ids' => [
          'id' => ['type' => 'string'],
        ],
      ],
      'process' => [
        'first' => [
          [
            'plugin' => 'skip_on_condition',
            'source' => 'simple_array',
            'condition' => 'not:not:empty',
            'method' => 'process',
          ],
          [
            'plugin' => 'filter_on_condition',
            'condition' => [
              'plugin' => 'is_null',
              'negate' => TRUE,
            ],
          ],
        ],
        'second' => [
          [
            'plugin' => 'skip_on_condition',
            'source' => 'simple_array',
            'condition' => [
              'plugin' => 'has_element',
              'condition' => 'empty',
            ],
            'method' => 'process',
          ],
        ],
        'third' => [
          [
            'plugin' => 'first_meeting_condition',
            'source' => 'simple_array',
            'condition' => 'greater_than(b)',
            'default_value' => 'My default',
          ],
          [
            'plugin' => 'evaluate_condition',
            'condition' => [
              'plugin' => 'not:equals',
              'negate' => TRUE,
              'property' => 'simple_value',
            ],
          ],
        ],
        'fourth' => [
          [
            'plugin' => 'get',
            'source' => 'simple_value',
          ],
        ],
        'fifth' => [
          [
            'plugin' => 'if_condition',
            'source' => 'simple_array/1',
            'condition' => [
              'plugin' => 'in_array',
              'property' => '@fourth',
            ],
            'do_get' => 'simple_array/0',
            'else_get' => 'simple_array/1',
          ],
        ],
      ],
      'destination' => [
        'plugin' => 'config',
        'config_name' => 'migrate_test.settings',
      ],
    ];
  }

  /**
   * Tests a pipeline involving process plugins using conditions.
   *
   * @dataProvider migrateConditionsProviderSource
   *
   * @param array $source_data
   *   The source data.
   * @param array $expected_data
   *   The expected results.
   */
  public function testMigrateConditionsPipeline(array $source_data, array $expected_data) {
    $definition = $this->getDefinition();
    $definition['source']['data_rows'] = [$source_data];

    $migration = \Drupal::service('plugin.manager.migration')->createStubMigration($definition);

    $executable = new MigrateExecutable($migration);
    $result = $executable->import();

    // Migration needs to succeed before further assertions are made.
    $this->assertSame(MigrationInterface::RESULT_COMPLETED, $result);

    // Compare with expected data.
    $this->assertEquals($expected_data, \Drupal::config('migrate_test.settings')->get());
  }

  /**
   * Provides multiple source data for "extract" process plugin test.
   */
  public function migrateConditionsProviderSource() {
    $tests = [
      [
        'source_data' => [
          'id' => '1',
          'simple_value' => 'beta',
          'simple_array' => ['alpha', 'beta', NULL],
        ],
        'expected_data' => [
          'first' => ['alpha', 'beta'],
          'third' => TRUE,
          'fourth' => 'beta',
          'fifth' => 'alpha',
        ],
      ],
      [
        'source_data' => [
          'id' => '2',
          'simple_value' => 'gamma',
          'simple_array' => ['andy', 'alice', 'azul', 'bravo'],
        ],
        'expected_data' => [
          'first' => ['andy', 'alice', 'azul', 'bravo'],
          'second' => ['andy', 'alice', 'azul', 'bravo'],
          'third' => FALSE,
          'fourth' => 'gamma',
          'fifth' => 'alice',
        ],
      ],
      [
        'source_data' => [
          'id' => '3',
          'simple_value' => 'My default',
          'simple_array' => [],
        ],
        'expected_data' => [
          'second' => [],
          'third' => TRUE,
          'fourth' => 'My default',
        ],
      ],
    ];

    return $tests;
  }

}
