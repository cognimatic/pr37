<?php

namespace Drupal\Tests\migrate_conditions\Kernel\process;

use Drupal\KernelTests\KernelTestBase;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\Plugin\MigrationInterface;

/**
 * Tests first_meeting_condition process plugin.
 *
 * @group migrate_conditions
 */
class FirstMeetingConditionTest extends KernelTestBase {

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
      'destination' => [
        'plugin' => 'config',
        'config_name' => 'migrate_test.settings',
      ],
    ];
  }

  /**
   * Tests multiples handling.
   *
   * The first_meeting_condition plugin should set the pipeline as multiple
   * in much the same way as the get plugin does. If the return is an array,
   * we handle the array as multiple values. If the return is not an array,
   * we treat as a single value.
   *
   * @param array $source_data
   *   The source data.
   * @param array $process
   *   The process pipeline.
   * @param array $expected_data
   *   The expected results.
   *
   * @dataProvider providerTestMultiples
   */
  public function testMultiples(array $source_data, array $process, array $expected_data) {
    $definition = $this->getDefinition();
    $definition['source']['data_rows'] = [$source_data];
    $definition['process'] = $process;

    $migration = \Drupal::service('plugin.manager.migration')->createStubMigration($definition);

    $executable = new MigrateExecutable($migration);
    $result = $executable->import();

    // Migration needs to succeed before further assertions are made.
    $this->assertSame(MigrationInterface::RESULT_COMPLETED, $result);

    // Compare with expected data.
    $this->assertEquals($expected_data, \Drupal::config('migrate_test.settings')->get());
  }

  /**
   * Provides source date for testing mutiples.
   */
  public function providerTestMultiples() {
    $tests = [
      'simple_source_scalar_out' => [
        'source_data' => [
          'id' => '1',
          'array' => [1, 4, 9, 16],
        ],
        'process' => [
          'testing' => [
            [
              'plugin' => 'first_meeting_condition',
              'source' => 'array',
              'condition' => [
                'plugin' => 'greater_than',
                'value' => 5,
              ],
            ],
            [
              'plugin' => 'callback',
              'callable' => 'abs',
            ],
          ],
        ],
        'expected_data' => [
          'testing' => 9,
        ],
      ],
      'complex_source_scalar_out' => [
        'source_data' => [
          'id' => '1',
          'val1' => 4,
          'val2' => 9,
        ],
        'process' => [
          'testing' => [
            [
              'plugin' => 'first_meeting_condition',
              'source' => [
                'val1',
                'val2',
              ],
              'condition' => [
                'plugin' => 'greater_than',
                'value' => 5,
              ],
            ],
            [
              'plugin' => 'callback',
              'callable' => 'abs',
            ],
          ],
        ],
        'expected_data' => [
          'testing' => 9,
        ],
      ],
      'simple_source_array_out' => [
        'source_data' => [
          'id' => '1',
          'nested_array' => [
            [],
            [2, 4, 6, 8],
          ],
        ],
        'process' => [
          'testing' => [
            [
              'plugin' => 'first_meeting_condition',
              'source' => 'nested_array',
              'condition' => 'not:empty',
            ],
            [
              'plugin' => 'callback',
              'callable' => 'abs',
            ],
          ],
        ],
        'expected_data' => [
          'testing' => [2, 4, 6, 8],
        ],
      ],
      'complex_source_array_out' => [
        'source_data' => [
          'id' => '1',
          'array' => [1, 4, 9, 16],
          'nested_array' => [
            [],
            [2, 4, 6, 8],
          ],
        ],
        'process' => [
          'testing' => [
            [
              'plugin' => 'first_meeting_condition',
              'source' => [
                'some_property_that_doesnt_exist',
                'array',
                'nested_array',
              ],
              'condition' => 'not:empty',
            ],
            [
              'plugin' => 'callback',
              'callable' => 'abs',
            ],
          ],
        ],
        'expected_data' => [
          'testing' => [1, 4, 9, 16],
        ],
      ],
      'default_value_scalar' => [
        'source_data' => [
          'id' => '1',
        ],
        'process' => [
          'testing' => [
            [
              'plugin' => 'first_meeting_condition',
              'source' => [
                'some_property_that_doesnt_exist',
              ],
              'condition' => 'not:empty',
              'default_value' => 123,
            ],
            [
              'plugin' => 'callback',
              'callable' => 'abs',
            ],
          ],
        ],
        'expected_data' => [
          'testing' => 123,
        ],
      ],
      'default_value_array' => [
        'source_data' => [
          'id' => '1',
        ],
        'process' => [
          'testing' => [
            [
              'plugin' => 'first_meeting_condition',
              'source' => [
                'some_property_that_doesnt_exist',
              ],
              'condition' => 'not:empty',
              'default_value' => [123, 321],
            ],
            [
              'plugin' => 'callback',
              'callable' => 'abs',
            ],
          ],
        ],
        'expected_data' => [
          'testing' => [123, 321],
        ],
      ],
    ];

    return $tests;
  }

}
