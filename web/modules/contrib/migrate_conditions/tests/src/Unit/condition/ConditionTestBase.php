<?php

namespace Drupal\Tests\migrate_conditions\Unit\condition;

use Drupal\Tests\UnitTestCase;

/**
 * Base class useful for most condition plugins, but not all.
 */
abstract class ConditionTestBase extends UnitTestCase {

  /**
   * The condition class, fully namespaced.
   */
  protected $conditionClass;

  /**
   * The condition plugin id.
   */
  protected $conditionId;

  /**
   * @covers ::evaluate
   * @dataProvider providerTestEvaluate
   */
  public function testEvaluate($source, $configuration, $property_value, $expected) {
    $row = $this->createMock('Drupal\migrate\Row');
    if (isset($configuration['property'])) {
      $row->expects($this->any())
        ->method('get')
        ->with($configuration['property'])
        ->willReturn($property_value);
    }
    $class = $this->conditionClass;
    $condition = new $class($configuration, $this->conditionId, []);
    $this->assertSame($expected, $condition->evaluate($source, $row));
    // Negate and expect the opposite.
    $configuration['negate'] = empty($configuration['negate']);
    $negated_condition = new $class($configuration, $this->conditionId, []);
    $this->assertSame(!$expected, $negated_condition->evaluate($source, $row));
  }

  /**
   * Data provider for ::testEvaluate().
   */
  public function providerTestEvaluate() {
    return [];
  }

}
