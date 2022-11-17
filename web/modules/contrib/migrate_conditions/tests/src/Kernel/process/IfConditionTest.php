<?php

namespace Drupal\Tests\migrate_conditions\Kernel\process;

use Drupal\KernelTests\KernelTestBase;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\Plugin\MigrationInterface;

/**
 * Tests the if_condition process plugin.
 *
 * Specifically, we're interested in testing the do_process and else_process
 * functionality.
 *
 * @group migrate_conditions
 */
class IfConditionTest extends KernelTestBase {

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
          'plugin' => 'if_condition',
          'source' => 'my_source',
          'condition' => [
            'plugin' => 'contains',
            'value' => 'migrate',
          ],
          'else_process' => [
            'plugin' => 'callback',
            'callable' => 'strtoupper',
            'source' => 'my_other_source',
          ],
          'do_process' => [
            [
              'plugin' => 'concat',
              'source' => [
                'my_source',
                'my_other_source',
              ],
            ],
            [
              'plugin' => 'explode',
              'delimiter' => 'migrate',
            ],
          ],
        ],
        'dest_value_2' => [
          'plugin' => 'if_condition',
          'source' => 'my_source',
          'condition' => [
            'plugin' => 'contains',
            'value' => 'migrate',
          ],
          'else_process' => [
            'plugin' => 'default_value',
            'default_value' => 'hamburger',
            'source' => [],
          ],
          'do_process' => [
            'plugin' => 'if_condition',
            'condition' => 'not:empty',
            'source' => 'my_other_source',
            'do_process' => [
              'plugin' => 'callback',
              'callable' => 'strtoupper',
              'source' => 'my_other_source',
            ],
          ],
        ],
        'dest_value_3' => [
          'plugin' => 'if_condition',
          'source' => 'my_source',
          'condition' => [
            'plugin' => 'contains',
            'value' => 'migrate',
          ],
          'else_process' => [
            'plugin' => 'callback',
            'callable' => 'ucfirst',
          ],
          'do_process' => [
            'plugin' => 'callback',
            'callable' => 'strtoupper',
          ],
        ],
        'dest_value_4' => [
          'plugin' => 'if_condition',
          'source' => 'my_source',
          'condition' => [
            'plugin' => 'contains',
            'value' => 'migrate',
          ],
          'do_process' => [
            'plugin' => 'get',
            'source' => '@dest_value_3',
          ],
          'else_process' => [
            'plugin' => 'get',
            'source' => '@dest_value_2',
          ],
        ],
        // This one tests when if_condition is in the middle of a pipeline.
        // In this case, source is not set anywhere within if_condition.
        'dest_value_5' => [
          [
            'plugin' => 'get',
            'source' => 'my_source',
          ],
          [
            'plugin' => 'if_condition',
            'condition' => 'not:empty',
            'do_process' => [
              'plugin' => 'callback',
              'callable' => 'strtoupper'
            ],
          ],
          [
            'plugin' => 'callback',
            'callable' => 'strrev',
          ],
          [
            'plugin' => 'skip_on_condition',
            'condition' => [
              'plugin' => 'equals',
              'value' => strrev(strtoupper('this makes migrate fun')),
            ],
            'method' => 'process',
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
  public function testIfCondition(array $source_data, array $expected_data) {
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
          'my_source' => 'this is a test',
          'my_other_source' => 'Hi, friend.',
        ],
        'expected_data' => [
          'dest_value_1' => 'HI, FRIEND.',
          'dest_value_2' => 'hamburger',
          'dest_value_3' => 'This is a test',
          'dest_value_4' => 'hamburger',
          'dest_value_5' => strrev(strtoupper('this is a test')),
        ],
      ],
      [
        'source_data' => [
          'id' => '1',
          'my_source' => 'this makes migrate fun',
          'my_other_source' => 'Hi, friend.',
        ],
        'expected_data' => [
          'dest_value_1' => [
            'this makes ',
            ' funHi, friend.',
          ],
          'dest_value_2' => 'HI, FRIEND.',
          'dest_value_3' => 'THIS MAKES MIGRATE FUN',
          'dest_value_4' => 'THIS MAKES MIGRATE FUN',
          // 'dest_value_5' should be skipped.
        ],
      ],
    ];

    return $tests;
  }

  /**
   * Returns test migration definition.
   *
   * @return array
   */
  public function getDefinitionMultiples() {
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
    $definition = $this->getDefinitionMultiples();
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
      'scalar' => [
        'source_data' => [
          'id' => '1',
          'val' => 5,
        ],
        'process' => [
          'testing' => [
            [
              'plugin' => 'if_condition',
              'source' => 'val',
              'condition' => 'not:empty',
            ],
            [
              'plugin' => 'callback',
              'callable' => 'abs',
            ],
          ],
        ],
        'expected_data' => [
          'testing' => 5,
        ],
      ],
      'array' => [
        'source_data' => [
          'id' => '1',
          'array' => [1, 4, 9, 16],
        ],
        'process' => [
          'testing' => [
            [
              'plugin' => 'if_condition',
              'source' => 'array',
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
      'process_that_sets_multiple' => [
        'source_data' => [
          'id' => '1',
          'string' => 'drupal|is|fun',
        ],
        'process' => [
          'testing' => [
            [
              'plugin' => 'if_condition',
              'source' => 'string',
              'condition' => 'not:empty',
              'do_process' => [
                'plugin' => 'explode',
                'delimiter' => '|',
              ],
            ],
            [
              'plugin' => 'callback',
              'callable' => 'strtoupper',
            ],
          ],
        ],
        'expected_data' => [
          'testing' => [
            'DRUPAL', 'IS', 'FUN',
          ],
        ],
      ],
      'source_array_with_iterating_and' => [
        'source_data' => [
          'id' => '1',
          'array' => [
            'one' => 1,
            'three' => 3,
          ],
        ],
        'process' => [
          'testing' => [
            [
              'plugin' => 'if_condition',
              'source' => 'array',
              'condition' => [
                'plugin' => 'and',
                'iterate' => TRUE,
                'conditions' => [
                  'one' => [
                    'plugin' => 'equals',
                    'value' => 1,
                  ],
                  'three' => [
                    'plugin' => 'greater_than',
                    'value' => 2,
                  ],
                ],
              ],
            ],
            [
              'plugin' => 'callback',
              'callable' => 'abs',
            ],
          ],
        ],
        'expected_data' => [
          'testing' => [1, 3],
        ],
      ],
      // array_build is weird in that it returns an array but
      // it does not declare the array as multiple. Uh oh...
      // This test shows that if_condition does not honor the
      // somewhat unusual behavior of array_build. We should
      // ideally be setting multiple to false which would pass
      // an array to strlen which would error out. But instead
      // we naively assume any array output should be considered
      // multiple values and this pipeline is successful.
      'process_array_build' => [
        'source_data' => [
          'id' => '1',
          'array' => [
            [
              'id' => 'en',
              'name' => 'English',
            ],
            [
              'id' => 'fr',
              'name' => 'French',
            ],
          ],
        ],
        'process' => [
          'testing' => [
            [
              'plugin' => 'if_condition',
              'source' => 'array',
              'condition' => 'not:empty',
              'do_process' => [
                'plugin' => 'array_build',
                'key' => 'id',
                'value' => 'name',
              ],
            ],
            [
              'plugin' => 'callback',
              'callable' => 'strlen',
            ],
          ],
        ],
        'expected_data' => [
          'testing' => [7, 6],
        ],
      ],
    ];

    return $tests;
  }

}
