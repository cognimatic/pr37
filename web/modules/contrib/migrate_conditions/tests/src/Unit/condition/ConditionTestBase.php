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
    $map = [['some_source_property', $source]];
    if (isset($configuration['property'])) {
      $map[] = [$configuration['property'], $property_value];
    }
    $row->expects($this->any())
      ->method('get')
      ->willReturnMap($map);
    $class = $this->conditionClass;
    $condition = new $class($configuration, $this->conditionId, []);
    $this->assertSame($expected, $condition->evaluate($source, $row));
    // Negate and expect the opposite.
    $configuration['negate'] = empty($configuration['negate']);
    $negated_condition = new $class($configuration, $this->conditionId, []);
    $this->assertSame(!$expected, $negated_condition->evaluate($source, $row));
    // Use source configuration on condition.
    $configuration['source'] = 'some_source_property';
    // Reset negation.
    $configuration['negate'] = empty($configuration['negate']);
    $condition_with_configured_source = new $class($configuration, $this->conditionId, []);
    $this->assertSame($expected, $condition_with_configured_source->evaluate(NULL, $row));
  }

  /**
   * Data provider for ::testEvaluate().
   */
  public function providerTestEvaluate() {
    return [];
  }

}
