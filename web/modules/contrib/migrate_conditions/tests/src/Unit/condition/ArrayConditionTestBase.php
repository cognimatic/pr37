<?php

namespace Drupal\Tests\migrate_conditions\Unit\condition;

use Drupal\migrate_conditions\Plugin\migrate_conditions\condition\AllElements;
use Drupal\Tests\UnitTestCase;

/**
 * Base class useful for array condition plugins.
 */
abstract class ArrayConditionTestBase extends UnitTestCase {

  /**
   * The condition class, fully namespaced.
   */
  protected $conditionClass;

  /**
   * The condition plugin id.
   */
  protected $conditionId;

  /**
   * @covers ::__construct()
   * @dataProvider providerTestConfigurationValidation
   */
  public function testConfigurationValidation($configuration, $message) {
    $condition_manager = $this->createMock('\Drupal\Component\Plugin\PluginManagerInterface');
    $condition_manager->expects($this->any())
      ->method('createInstance')
      ->willReturn(NULL);
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage($message);
    $class = $this->conditionClass;
    $condition = new $class($configuration, $this->conditionId, [], $condition_manager);
  }

  /**
   * Data provider for ::testConfigurationValidation().
   */
  public function providerTestConfigurationValidation() {
    return [];
  }

  /**
   * @covers ::row
   * @covers ::process
   * @dataProvider providerTestEvaluate
   */
  public function testEvaluate($source, $configuration, $sub_evaluate_map, $expected) {
    $row = $this->createMock('Drupal\migrate\Row');

    $map = [];
    foreach ($sub_evaluate_map as $source_then_return) {
      $map[] = [$source_then_return[0], $row, $source_then_return[1]];
    }
    $condition = $this->createMock('\Drupal\migrate_conditions\ConditionInterface');
    $condition->expects($this->any())
      ->method('evaluate')
      ->willReturnMap($map);
    $condition_manager = $this->createMock('\Drupal\Component\Plugin\PluginManagerInterface');
    $condition_manager->expects($this->any())
      ->method('createInstance')
      ->will($this->returnValue($condition));

    $class = $this->conditionClass;
    $condition = new $class($configuration, $this->conditionId, [], $condition_manager);
    $this->assertSame($expected, $condition->evaluate($source, $row));
    // Negate and expect the opposite.
    $configuration['negate'] = empty($configuration['negate']);
    $negated_condition = new $class($configuration, $this->conditionId, [], $condition_manager);
    $this->assertSame(!$expected, $negated_condition->evaluate($source, $row));
  }

  /**
   * Data provider for ::testEvaluate().
   */
  public function providerTestEvaluate() {
    return [];
  }

}
