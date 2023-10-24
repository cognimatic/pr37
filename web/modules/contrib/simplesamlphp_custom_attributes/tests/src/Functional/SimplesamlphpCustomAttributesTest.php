<?php

namespace Drupal\Tests\simplesamlphp_custom_attributes\Functional;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests attribute mapping via SimpleSAMLphp.
 *
 * @group simplesamlphp_custom_attributes
 */
class SimplesamlphpCustomAttributesTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'classy';

  /**
   * Modules to enable for this test.
   *
   * @var string[]
   */
  protected static $modules = [
    'block',
    'field',
    'simplesamlphp_auth',
    'simplesamlphp_custom_attributes',
  ];

  /**
   * An administrator user.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $adminUser;

  /**
   * An authenticated user.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $authenticatedUser;

  /**
   * Full name label.
   *
   * @var string
   */
  protected string $fieldFullNameLabel = 'Full Name';

  /**
   * Full name machine name.
   *
   * @var string
   */
  protected string $fieldFullName = 'field_full_name';

  /**
   * SAML attribute.
   *
   * @var string
   */
  protected string $samlAttribute = 'urn:oid:2.16.840.1.113730.3.1.241';

  /**
   * Alt SAML attribute.
   *
   * @var string
   */
  protected string $altSamlAttribute = 'urn:oid:2.5.4.42';

  /**
   * Add URL.
   *
   * @var string
   */
  protected string $addURL = '/admin/config/people/simplesamlphp-custom-attributes/add';

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();
    $this->adminUser = $this->drupalCreateUser([
      'access administration pages',
      'administer users',
      'administer blocks',
      'administer simplesamlphp authentication',
    ]);
    $this->authenticatedUser = $this->drupalCreateUser();
    $this->drupalPlaceBlock('page_title_block');

    // Configure SimpleSAMLphp for testing purposes.
    $this->config('simplesamlphp_auth.settings')
      ->set('activate', 1)
      ->set('mail_attr', 'mail')
      ->set('unique_id', 'uid')
      ->set('user_name', 'displayName')
      ->set('login_link_display_name', "Federated test login")
      ->set('allow.default_login_users', $this->adminUser->id() . ',' . $this->authenticatedUser->id())
      ->save();

    // Create user 'Full Name' field.
    FieldStorageConfig::create([
      'field_name' => $this->fieldFullName,
      'entity_type' => 'user',
      'type' => 'string',
    ])->save();
    FieldConfig::create([
      'label' => $this->fieldFullNameLabel,
      'field_name' => $this->fieldFullName,
      'entity_type' => 'user',
      'bundle' => 'user',
      'required' => 0,
      'description' => '',
    ])->save();
  }

  /**
   * Test adding SimplesamlphpCustomAttributes mapping.
   */
  public function testAddingCustomAttributesMapping() {
    $this->drupalLogin($this->adminUser);
    $this->drupalGet($this->addURL);

    $this->assertSession()->elementTextContains('css', '#edit-field-name option:first-child', 'Custom');

    $add = [
      'attribute_name' => $this->samlAttribute,
      'field_name' => $this->fieldFullName,
    ];
    $this->submitForm($add, 'Submit');

    $this->assertSession()->pageTextContains('SimpleSAMLphp Auth Attribute Mapping');
    $this->assertSession()->elementTextContains('css', 'tbody td:first-child', $this->samlAttribute);
    $this->assertSession()->elementTextContains('css', 'tbody td:first-child + td', $this->fieldFullNameLabel);
  }

  /**
   * Test editing SimplesamlphpCustomAttributes mapping.
   */
  public function testEditingCustomAttributesMapping() {
    $this->drupalLogin($this->adminUser);
    $this->drupalGet($this->addURL);

    $add = [
      'attribute_name' => $this->samlAttribute,
      'field_name' => $this->fieldFullName,
    ];
    $this->submitForm($add, 'Submit');

    $this->click('tbody td:last-child li.edit a');

    $edit = [
      'attribute_name' => $this->altSamlAttribute,
    ];
    $this->submitForm($edit, 'Submit');

    $this->assertSession()->elementTextContains('css', 'tbody td:first-child', $this->altSamlAttribute);
  }

  /**
   * Test deleting SimplesamlphpCustomAttributes mapping.
   */
  public function testDeletingCustomAttributesMapping() {
    $this->drupalLogin($this->adminUser);
    $this->drupalGet($this->addURL);

    $add = [
      'attribute_name' => $this->samlAttribute,
      'field_name' => $this->fieldFullName,
    ];
    $this->submitForm($add, 'Submit');

    $this->click('tbody td:last-child li.delete a');
    $this->submitForm([], 'Confirm');

    $this->assertSession()->elementTextContains('css', 'tbody td:first-child', 'There are no mappings. You can add one using the link above.');
  }

  /**
   * Test adding and deleting custom SimplesamlphpCustomAttributes mapping.
   */
  public function testAddingAndDeletingCustomAttributesMapping() {
    $test_saml_attribute = 'test';

    $this->drupalLogin($this->adminUser);
    $this->drupalGet($this->addURL);

    $add = [
      'attribute_name' => $test_saml_attribute,
    ];
    $this->submitForm($add, 'Submit');

    $this->assertSession()->elementTextContains('css', 'tbody td:first-child', $test_saml_attribute);
    $this->assertSession()->elementTextContains('css', 'tbody td:first-child + td', 'Custom');

    $this->click('tbody td:last-child li.delete a');
    $this->submitForm([], 'Confirm');

    $this->assertSession()->elementTextContains('css', 'tbody td:first-child', 'There are no mappings. You can add one using the link above.');
  }

}
