<?php

namespace Drupal\Tests\migrate_conditions\Kernel\process;

use Drupal\KernelTests\KernelTestBase;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\Plugin\MigrationInterface;

/**
 * Tests the switch_on_condition process plugin.
 *
 * @group migrate_conditions
 */
class SwitchOnConditionTest extends KernelTestBase {

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
        'dest_value_1' => [
          'plugin' => 'switch_on_condition',
          'source' => 'source_1',
          'cases' => [
            [
              'condition' => 'empty',
              'default_value' => 123,
            ],
            [
              'condition' => [
                'plugin' => 'equals',
                'value' => '789',
              ],
              'process' => [
                'plugin' => 'callback',
                'callable' => 'strrev',
              ],
            ],
            [
              'condition' => 'default',
              'get' => 'foo',
            ],
          ],
        ],
        'dest_value_2' => [
          'plugin' => 'switch_on_condition',
          'source' => 'source_2',
          'cases' => [
            [
              'condition' => 'empty',
              'default_value' => 123,
            ],
            [
              'condition' => 'default',
              'process' => [
                [
                  'source' => 'foo',
                  'plugin' => 'callback',
                  'callable' => 'strrev',
                ],
                [
                  'plugin' => 'callback',
                  'callable' => 'strtoupper',
                ],
              ],
            ],
          ],
        ],
        'dest_value_3' => [
          [
            'plugin' => 'get',
            'source' => 'source_3',
          ],
          [
            'plugin' => 'switch_on_condition',
            'cases' => [
              [
                'condition' => 'default',
                'process' => [
                  'plugin' => 'default_value',
                  'default_value' => 'my default',
                ],
              ],
            ],
          ],
          [
            'plugin' => 'callback',
            'callable' => 'strtoupper',
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
  public function testSwitchOnCondition(array $source_data, array $expected_data) {
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
   * Provides source data for "switch_on_condition" process plugin test.
   */
  public function migrateConditionsProviderSource() {
    $tests = [
      [
        'source_data' => [
          'id' => '1',
          'foo' => 'bar',
          'source_1' => 'this is a test',
          'source_2' => 'second one',
          'source_3' => [
            'some',
            'array',
          ],
        ],
        'expected_data' => [
          'dest_value_1' => 'bar',
          'dest_value_2' => 'RAB',
          'dest_value_3' => [
            'SOME',
            'ARRAY',
          ],
        ],
      ],
      [
        'source_data' => [
          'id' => '1',
          'foo' => 'bar',
          'source_1' => NULL,
          'source_2' => NULL,
          'source_3' => NULL,
        ],
        'expected_data' => [
          'dest_value_1' => 123,
          'dest_value_2' => 123,
          'dest_value_3' => 'MY DEFAULT',
        ],
      ],
      [
        'source_data' => [
          'id' => '1',
          'foo' => 'bar',
          'source_1' => '789',
          'source_2' => [
            'something',
            NULL,
            'another',
          ],
          'source_3' => 'some string',
        ],
        'expected_data' => [
          'dest_value_1' => '987',
          'dest_value_2' => 'RAB',
          'dest_value_3' => 'SOME STRING',
        ],
      ],
    ];

    return $tests;
  }

}
