<?php

namespace Drupal\Tests\migrate_conditions\Kernel\process;

use Drupal\KernelTests\KernelTestBase;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\Plugin\MigrationInterface;

/**
 * Tests skip_on_condition process plugin.
 *
 * @group migrate_conditions
 */
class SkipOnConditionTest extends KernelTestBase {

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
   * The skip_on_condition plugin should not affect how multiples would be
   * handled. The next plugin should work the same whether or not
   * skip_on_condition comes before it.
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
      'scalar_source' => [
        'source_data' => [
          'id' => '1',
          'scalar' => 5,
        ],
        'process' => [
          'skip' => [
            [
              'plugin' => 'skip_on_condition',
              'source' => 'scalar',
              'condition' => 'empty',
              'method' => 'row',
            ],
            [
              'plugin' => 'callback',
              'callable' => 'abs',
            ],
          ],
          'no_skip' => [
            'plugin' => 'callback',
            'callable' => 'abs',
            'source' => 'scalar',
          ],
        ],
        'expected_data' => [
          'skip' => 5,
          'no_skip' => 5,
        ],
      ],
      'array_source' => [
        'source_data' => [
          'id' => '1',
          'array' => [1, 4, 9, 16],
        ],
        'process' => [
          'skip' => [
            [
              'plugin' => 'skip_on_condition',
              'source' => 'array',
              'condition' => 'empty',
              'method' => 'row',
            ],
            [
              'plugin' => 'callback',
              'callable' => 'abs',
            ],
          ],
          'no_skip' => [
            'plugin' => 'callback',
            'callable' => 'abs',
            'source' => 'array',
          ],
        ],
        'expected_data' => [
          'skip' => [1, 4, 9, 16],
          'no_skip' => [1, 4, 9, 16],
        ],
      ],
    ];

    return $tests;
  }

}
