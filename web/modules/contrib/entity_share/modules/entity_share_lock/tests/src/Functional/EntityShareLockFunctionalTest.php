<?php

declare(strict_types = 1);

namespace Drupal\Tests\entity_share_lock\Functional;

use Drupal\entity_share_lock\HookHandler\FormAlterHookHandler;
use Drupal\node\NodeInterface;
use Drupal\Tests\entity_share_client\Functional\EntityShareClientFunctionalTestBase;

/**
 * Test lock feature.
 *
 * @group entity_share
 * @group entity_share_lock
 */
class EntityShareLockFunctionalTest extends EntityShareClientFunctionalTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'entity_share_lock',
  ];

  /**
   * {@inheritdoc}
   */
  protected static $entityTypeId = 'node';

  /**
   * {@inheritdoc}
   */
  protected static $entityBundleId = 'es_test';

  /**
   * {@inheritdoc}
   */
  protected static $entityLangcode = 'en';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->postSetupFixture();
  }

  /**
   * {@inheritdoc}
   */
  protected function getAdministratorPermissions() {
    $permissions = parent::getAdministratorPermissions();
    $permissions[] = 'bypass node access';
    return $permissions;
  }

  /**
   * {@inheritdoc}
   */
  protected function getImportConfigProcessorSettings() {
    $processors = parent::getImportConfigProcessorSettings();
    $processors['default_data_processor']['policy'] = FormAlterHookHandler::LOCKED_POLICY;
    return $processors;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntitiesDataArray() {
    return [
      'node' => [
        'en' => [
          'es_test' => $this->getCompleteNodeInfos([
            'status' => [
              'value' => NodeInterface::PUBLISHED,
              'checker_callback' => 'getValue',
            ],
          ]),
        ],
      ],
    ];
  }

  /**
   * Test lock feature.
   */
  public function testLock() {
    $this->pullEveryChannels();
    $this->checkCreatedEntities();

    $node = $this->loadEntity('node', 'es_test');

    $this->drupalLogin($this->adminUser);
    $this->drupalGet($node->toUrl('edit-form'));

    // Test that the form is disabled.
    $this->assertSession()->fieldDisabled('title[0][value]');
    // Test that a message is displayed.
    $this->assertSession()->responseContains('The entity had been locked from edition because of an import policy.');
  }

}
