<?php

namespace Drupal\Tests\migrate_conditions\Unit\condition;

use Drupal\migrate\MigrateException;
use Drupal\Tests\UnitTestCase;

/**
 * Base class useful for most condition plugins, but not all.
 */
abstract class LogicalConditionTestBase extends UnitTestCase {

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
  public function testEvaluate($source, $evaluates, $configuration, $expected) {
    $row = $this->createMock('Drupal\migrate\Row');
    $row->expects($this->any())
      ->method('get')
      ->with('some_source_property')
      ->willReturn($source);

    $conditions = [];
    for ($i = 0; $i < count($configuration['conditions']); $i++) {
      $condition = $this->createMock('\Drupal\migrate_conditions\ConditionInterface');
      $condition->expects($this->any())
        ->method('evaluate')
        ->willReturn($evaluates[$i]);
      $conditions[] = $condition;
    }

    $condition_manager = $this->createMock('\Drupal\Component\Plugin\PluginManagerInterface');
    $condition_manager->expects($this->any())
      ->method('createInstance')
      ->willReturnOnConsecutiveCalls(...$conditions);

    $class = $this->conditionClass;
    $condition = new $class($configuration, $this->conditionId, [], $condition_manager);
    $this->assertSame($expected, $condition->evaluate($source, $row));
    // Negate and expect the opposite.
    $condition_manager = $this->createMock('\Drupal\Component\Plugin\PluginManagerInterface');
    $condition_manager->expects($this->any())
      ->method('createInstance')
      ->willReturnOnConsecutiveCalls(...$conditions);
    $configuration['negate'] = empty($configuration['negate']);
    $negated_condition = new $class($configuration, $this->conditionId, [], $condition_manager);
    $this->assertSame(!$expected, $negated_condition->evaluate($source, $row));

    // Use source configuration on condition.
    $configuration['source'] = 'some_source_property';
    // Reset negation.
    $configuration['negate'] = empty($configuration['negate']);
    $condition_manager = $this->createMock('\Drupal\Component\Plugin\PluginManagerInterface');
    $condition_manager->expects($this->any())
      ->method('createInstance')
      ->willReturnOnConsecutiveCalls(...$conditions);
    $condition_with_configured_source = new $class($configuration, $this->conditionId, [], $condition_manager);
    $this->assertSame($expected, $condition_with_configured_source->evaluate(NULL, $row));
  }

  /**
   * Data provider for ::testEvaluate().
   */
  public function providerTestEvaluate() {
    return [];
  }

  /**
   * @covers ::evaluate
   * @dataProvider providerTestEvaluateExceptions
   */
  public function testEvaluateExceptions($source, $configuration, $expected_message) {
    $row = $this->createMock('Drupal\migrate\Row');

    $condition = $this->createMock('\Drupal\migrate_conditions\ConditionInterface');
    $condition_manager = $this->createMock('\Drupal\Component\Plugin\PluginManagerInterface');
    $condition_manager->expects($this->any())
      ->method('createInstance')
      ->willReturn($condition);

    $class = $this->conditionClass;
    $condition = new $class($configuration, $this->conditionId, [], $condition_manager);
    $this->expectException(MigrateException::class);
    $this->expectExceptionMessage($expected_message);
    $condition->evaluate($source, $row);
  }

  /**
   * Data provider for ::testEvaluateExceptions().
   */
  public function providerTestEvaluateExceptions() {
    return [
      [
        'source' => 123,
        'configuration' => [
          'iterate' => TRUE,
          'conditions' => [
            [
              'plugin' => 'foo',
            ],
          ],
        ],
        'expected_message' => "If the 'iterate' property is true, the source must be an array.",
      ],
    ];
  }

}
