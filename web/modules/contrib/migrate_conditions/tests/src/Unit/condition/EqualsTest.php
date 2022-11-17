<?php

namespace Drupal\Tests\migrate_conditions\Unit\condition;

/**
 * @coversDefaultClass \Drupal\migrate_conditions\Plugin\migrate_conditions\condition\Equals
 * @group migrate_conditions
 */
class EqualsTest extends ConditionTestBase {

  use ConditionTestValidationTrait;

  /**
   * {@inheritdoc}
   */
  protected $conditionClass = 'Drupal\migrate_conditions\Plugin\migrate_conditions\condition\Equals';

  /**
   * {@inheritdoc}
   */
  protected $conditionId = 'equals';

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
        'message' => 'Exactly one of value and property must be set when using the equals condition.',
      ],
      [
        'configuration' => [],
        'message' => 'Exactly one of value and property must be set when using the equals condition.',
      ],
      [
        'configuration' => [
          'property' => 123,
        ],
        'message' => 'The property configuration must be a string when using the equals condition.',
      ],
    ];
  }

  /**
   * Data provider for ::testEvaluate().
   */
  public function providerTestEvaluate() {
    return [
      [
        'source' => 'string',
        'configuration' => [
          'value' => 'string',
        ],
        'property_value' => NULL,
        'expected' => TRUE,
      ],
      [
        'source' => 'something else',
        'configuration' => [
          'value' => 'string',
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
        'expected' => TRUE,
      ],
      [
        'source' => 321,
        'configuration' => [
          'value' => 123,
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
        'expected' => TRUE,
      ],
      [
        'source' => FALSE,
        'configuration' => [
          'value' => NULL,
          'strict' => TRUE,
        ],
        'property_value' => NULL,
        'expected' => FALSE,
      ],
      [
        'source' => [1, 2, 3],
        'configuration' => [
          'value' => [1, 2, 3],
        ],
        'property_value' => NULL,
        'expected' => TRUE,
      ],
      [
        'source' => [1, 3, 2],
        'configuration' => [
          'value' => [1, 2, 3],
        ],
        'property_value' => NULL,
        'expected' => FALSE,
      ],
      [
        'source' => 45,
        'configuration' => [
          'property' => 'my_property',
        ],
        'property_value' => '45',
        'expected' => TRUE,
      ],
      [
        'source' => 45,
        'configuration' => [
          'property' => 'my_property',
          'strict' => TRUE,
        ],
        'property_value' => '45',
        'expected' => FALSE,
      ],
    ];
  }

}
