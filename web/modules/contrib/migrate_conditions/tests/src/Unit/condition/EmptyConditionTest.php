<?php

namespace Drupal\Tests\migrate_conditions\Unit\condition;

/**
 * @coversDefaultClass \Drupal\migrate_conditions\Plugin\migrate_conditions\condition\EmptyCondition
 * @group migrate_conditions
 */
class EmptyConditionTest extends ConditionTestBase {

  /**
   * {@inheritdoc}
   */
  protected $conditionClass = 'Drupal\migrate_conditions\Plugin\migrate_conditions\condition\EmptyCondition';

  /**
   * {@inheritdoc}
   */
  protected $conditionId = 'empty';

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
        'expected' => TRUE,
      ],
      [
        'source' => '',
        'configuration' => [],
        'property_value' => NULL,
        'expected' => TRUE,
      ],
      [
        'source' => [],
        'configuration' => [],
        'property_value' => NULL,
        'expected' => TRUE,
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
        'expected' => TRUE,
      ],
    ];
  }

}
