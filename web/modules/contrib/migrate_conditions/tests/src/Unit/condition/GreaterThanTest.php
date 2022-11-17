<?php

namespace Drupal\Tests\migrate_conditions\Unit\condition;

/**
 * @coversDefaultClass \Drupal\migrate_conditions\Plugin\migrate_conditions\condition\GreaterThan
 * @group migrate_conditions
 */
class GreaterThanTest extends ConditionTestBase {

  use ConditionTestValidationTrait;

  /**
   * {@inheritdoc}
   */
  protected $conditionClass = 'Drupal\migrate_conditions\Plugin\migrate_conditions\condition\GreaterThan';

  /**
   * {@inheritdoc}
   */
  protected $conditionId = 'greater_than';

  /**
   * Data provider for ::testConfigurationValidation().
   */
  public function providerTestConfigurationValidation() {
    return [
      [
        'configuration' => [
          'value' => 5,
          'property' => 'my_property',
        ],
        'message' => 'Exactly one of value and property must be set when using the greater_than condition.',
      ],
      [
        'configuration' => [],
        'message' => 'Exactly one of value and property must be set when using the greater_than condition.',
      ],
      [
        'configuration' => [
          'property' => 123,
        ],
        'message' => 'The property configuration must be a string when using the greater_than condition.',
      ],
    ];
  }

  /**
   * Data provider for ::testEvaluate().
   */
  public function providerTestEvaluate() {
    return [
      [
        'source' => 'aaa',
        'configuration' => [
          'value' => 'aaa',
        ],
        'property_value' => NULL,
        'expected' => FALSE,
      ],
      [
        'source' => 'bbb',
        'configuration' => [
          'value' => 'aaa',
        ],
        'property_value' => NULL,
        'expected' => TRUE,
      ],
      [
        'source' => 'aaa',
        'configuration' => [
          'value' => 'bbb',
        ],
        'property_value' => NULL,
        'expected' => FALSE,
      ],
      [
        'source' => 123,
        'configuration' => [
          'value' => 123,
        ],
        'property_value' => NULL,
        'expected' => FALSE,
      ],
      [
        'source' => 1230,
        'configuration' => [
          'value' => 123,
        ],
        'property_value' => NULL,
        'expected' => TRUE,
      ],
      [
        'source' => [1, 2, 3],
        'configuration' => [
          'value' => [1, 2, 3],
        ],
        'property_value' => NULL,
        'expected' => FALSE,
      ],
      [
        'source' => [1, 2, 3],
        'configuration' => [
          'value' => [1, 2, 3, 4],
        ],
        'property_value' => NULL,
        'expected' => FALSE,
      ],
      [
        'source' => [1, 2, 3, 4],
        'configuration' => [
          'value' => [1, 2, 3],
        ],
        'property_value' => NULL,
        'expected' => TRUE,
      ],
      [
        'source' => ['key' => 6],
        'configuration' => [
          'value' => ['key' => 5],
        ],
        'property_value' => NULL,
        'expected' => TRUE,
      ],
      [
        'source' => ['key' => 5],
        'configuration' => [
          'value' => ['key' => 6],
        ],
        'property_value' => NULL,
        'expected' => FALSE,
      ],
      [
        'source' => ['another_key' => 5],
        'configuration' => [
          'value' => ['key' => 6],
        ],
        'property_value' => NULL,
        'expected' => FALSE,
      ],
      [
        'source' => ['another_key' => 6],
        'configuration' => [
          'value' => ['key' => 5],
        ],
        'property_value' => NULL,
        'expected' => FALSE,
      ],
      [
        'source' => FALSE,
        'configuration' => [
          'value' => NULL,
        ],
        'property_value' => NULL,
        'expected' => FALSE,
      ],
      [
        'source' => 45,
        'configuration' => [
          'property' => 'my_property',
        ],
        'property_value' => 46,
        'expected' => FALSE,
      ],
      [
        'source' => 46,
        'configuration' => [
          'property' => 'my_property',
        ],
        'property_value' => 45,
        'expected' => TRUE,
      ],
    ];
  }

}
