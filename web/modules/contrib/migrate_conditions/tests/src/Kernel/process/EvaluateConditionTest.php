<?php

namespace Drupal\Tests\migrate_conditions\Kernel\process;

use Drupal\KernelTests\KernelTestBase;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\Plugin\MigrationInterface;

/**
 * Tests evaluate_condition process plugin.
 *
 * @group migrate_conditions
 */
class EvaluateConditionTest extends KernelTestBase {

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
        'testing' => [
          [
            'plugin' => 'evaluate_condition',
            'source' => 'array',
            'condition' => [
              'plugin' => 'callback',
              'callable' => 'is_array',
            ],
          ],
          [
            'plugin' => 'callback',
            'callable' => 'is_bool',
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
   * Tests multiples handling.
   *
   * @param array $source_data
   *   The source data.
   * @param array $expected_data
   *   The expected results.
   *
   * @dataProvider providerTestMultiples
   */
  public function testMultiples(array $source_data, array $expected_data) {
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
   * Provides source date for testing mutiples.
   */
  public function providerTestMultiples() {
    $tests = [
      [
        'source_data' => [
          'id' => '1',
          'array' => [3, 4, 'ham', 'bone'],
        ],
        'expected_data' => [
          'testing' => TRUE,
        ],
      ],
    ];

    return $tests;
  }

}
