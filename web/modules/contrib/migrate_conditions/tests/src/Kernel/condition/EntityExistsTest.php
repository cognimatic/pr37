<?php

namespace Drupal\Tests\migrate_conditions\Kernel\Plugin\condition;

use Drupal\KernelTests\KernelTestBase;
use Drupal\user\Entity\User;

/**
 * Tests the entity_exists condition plugin.
 *
 * @group migrate_conditions
 */
class EntityExistsTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'migrate',
    'migrate_conditions',
    'system',
    'user',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installSchema('system', ['sequences']);
    $this->installEntitySchema('user');
  }

  /**
   * Tests validation in constructor.
   */
  public function testConstructor() {
    $configuration = [];
    $this->expectExceptionMessage('The entity_type configuration is required when using the entity_exists condition.');
    $condition = \Drupal::service('plugin.manager.migrate_conditions.condition')->createInstance('entity_exists', $configuration);
  }

  /**
   * Tests with a real entity.
   */
  public function testEvaluate() {
    $row = $this->createMock('Drupal\migrate\Row');

    $user = User::create([
      'name' => $this->randomString(),
    ]);
    $user->save();
    $uid = $user->id();

    $configuration = [
      'entity_type' => 'user',
    ];
    $condition = \Drupal::service('plugin.manager.migrate_conditions.condition')->createInstance('entity_exists', $configuration);
    $this->assertTrue($condition->evaluate($uid, $row));
    $this->assertFalse($condition->evaluate('not an id', $row));
  }

}
