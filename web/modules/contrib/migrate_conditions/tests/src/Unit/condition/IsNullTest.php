<?php

namespace Drupal\Tests\migrate_conditions\Unit\condition;

/**
 * @coversDefaultClass \Drupal\migrate_conditions\Plugin\migrate_conditions\condition\IsNull
 * @group migrate_conditions
 */
class IsNullTest extends ConditionTestBase {

  /**
   * {@inheritdoc}
   */
  protected $conditionClass = 'Drupal\migrate_conditions\Plugin\migrate_conditions\condition\IsNull';

  /**
   * {@inheritdoc}
   */
  protected $conditionId = 'is_null';

  /**
   * Data provider for ::testEvaluate().
   */
  public function providerTestEvaluate() {
    return [
      [
        'source' => 'string',
        'configuration' => [],
        'property_value' => NULL,
        'expected' => FALSE,
      ],
      [
        'source' => 123,
        'configuration' => [],
        'property_value' => NULL,
        'expected' => FALSE,
      ],
      [
        'source' => [1, 2, 3],
        'configuration' => [],
        'property_value' => NULL,
        'expected' => FALSE,
      ],
      [
        'source' => TRUE,
        'configuration' => [],
        'property_value' => NULL,
        'expected' => FALSE,
      ],
      [
        'source' => 0,
        'configuration' => [],
        'property_value' => NULL,
        'expected' => FALSE,
      ],
      [
        'source' => '',
        'configuration' => [],
        'property_value' => NULL,
        'expected' => FALSE,
      ],
      [
        'source' => [],
        'configuration' => [],
        'property_value' => NULL,
        'expected' => FALSE,
      ],
      [
        'source' => NULL,
        'configuration' => [],
        'property_value' => NULL,
        'expected' => TRUE,
      ],
      [
        'source' => FALSE,
        'configuration' => [],
        'property_value' => NULL,
        'expected' => FALSE,
      ],
    ];
  }

}
