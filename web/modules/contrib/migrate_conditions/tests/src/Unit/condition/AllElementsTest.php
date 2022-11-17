<?php

namespace Drupal\Tests\migrate_conditions\Unit\condition;

/**
 * Tests the all_elements condition plugin.
 *
 * @group migrate_conditions
 * @coversDefaultClass \Drupal\migrate_conditions\Plugin\migrate_conditions\condition\AllElements
 */
class AllElementsTest extends ArrayConditionTestBase {

  /**
   * {@inheritdoc}
   */
  protected $conditionClass = 'Drupal\migrate_conditions\Plugin\migrate_conditions\condition\AllElements';

  /**
   * {@inheritdoc}
   */
  protected $conditionId = 'all_elements';

  /**
   * Data provider for ::testConfigurationValidation().
   */
  public function providerTestConfigurationValidation() {
    return [
      [
        'configuration' => [],
        'message' => "The 'condition' must be set.",
      ],
      [
        'configuration' => [
          'condition' => 123,
        ],
        'message' => "The 'condition' must be either a string or an array.",
      ],
      [
        'configuration' => [
          'condition' => [
            'value' => 123,
            'no_plugin' => 'uh oh',
          ],
        ],
        'message' => "The 'plugin' must be set for the condition.",
      ],
    ];
  }

  /**
   * Data provider for ::testEvaluate().
   */
  public function providerTestEvaluate() {
    return [
      [
        'source' => 1,
        'configuration' => [
          'condition' => 'foo',
        ],
        'sub_evaluate_map' => [
          [1, TRUE],
        ],
        'expected' => TRUE,
      ],
      [
        'source' => 1,
        'configuration' => [
          'condition' => 'foo',
        ],
        'sub_evaluate_map' => [
          [1, FALSE],
        ],
        'expected' => FALSE,
      ],
      [
        'source' => [1, 2, 3],
        'configuration' => [
          'condition' => 'foo',
        ],
        'sub_evaluate_map' => [
          [1, FALSE],
          [2, FALSE],
          [3, TRUE],
        ],
        'expected' => FALSE,
      ],
      [
        'source' => [1, 2, 3],
        'configuration' => [
          'condition' => 'foo',
        ],
        'sub_evaluate_map' => [
          [1, FALSE],
          [2, FALSE],
          [3, FALSE],
        ],
        'expected' => FALSE,
      ],
      [
        'source' => [1, 2, 3],
        'configuration' => [
          'condition' => 'foo',
        ],
        'sub_evaluate_map' => [
          [1, TRUE],
          [2, TRUE],
          [3, TRUE],
        ],
        'expected' => TRUE,
      ],
      [
        'source' => [],
        'configuration' => [
          'condition' => 'foo',
        ],
        'sub_evaluate_map' => [],
        'expected' => FALSE,
      ],
    ];
  }

}
