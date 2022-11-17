<?php

namespace Drupal\Tests\migrate_conditions\Kernel\condition;

use Drupal\KernelTests\KernelTestBase;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate_conditions\Plugin\migrate_conditions\condition\InMigrateMap;
use Drupal\node\Entity\Node;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;

/**
 * Tests the in_migrate_map condition plugin.
 *
 * @group migrate_conditions
 */
class InMigrateMapTest extends KernelTestBase {
  use ContentTypeCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'node',
    'field',
    'user',
    'text',
    'migrate',
    'migrate_conditions',
    'migrate_conditions_test_migrations',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('node');
    $this->installEntitySchema('user');
    $this->installConfig(['node', 'user']);
    $this->createContentType(['type' => 'node_lookup']);
  }

  /**
   * Tests validation in constructor.
   */
  public function testConstructor() {
    $configuration = [];
    $plugin_definition = \Drupal::service('plugin.manager.migrate_conditions.condition')->getDefinition('in_migrate_map');
    $lookup = \Drupal::service('migrate.lookup');
    $manager = \Drupal::service('plugin.manager.migration');
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('The migration configuration is required when using the in_migrate_map condition.');
    $condition = new InMigrateMap($configuration, 'in_migrate_map', $plugin_definition, $lookup, $manager);
  }

  /**
   * Tests in_migrate_map condition plugin.
   */
  public function testInMigrateMap() {
    $manager = \Drupal::service('plugin.manager.migration');
    $migration = $manager->createInstance('in_migrate_map_test');
    $executable = new MigrateExecutable($migration);
    $result = $executable->import();
    $this->assertSame(MigrationInterface::RESULT_COMPLETED, $result);
    $expected = '17--1-';
    $this->assertEquals($expected, Node::load(1)->label());
    $expected = '25-1--1';
    $this->assertEquals($expected, Node::load(2)->label());
    $expected = '33--1-';
    $this->assertEquals($expected, Node::load(3)->label());
    $expected = '44--1-1';
    $this->assertEquals($expected, Node::load(4)->label());
  }

}
