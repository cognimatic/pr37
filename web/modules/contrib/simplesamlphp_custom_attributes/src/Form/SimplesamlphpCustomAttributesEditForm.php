<?php

namespace Drupal\simplesamlphp_custom_attributes\Form;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * {@inheritdoc}
 */
class SimplesamlphpCustomAttributesEditForm extends FormBase {

  /**
   * Mapping settings.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $mappingConfig;

  /**
   * Used to get field names for user entity.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityFieldManagerInterface $entity_field_manager) {
    $configFactory = $this->configFactory();
    $this->mappingConfig = $configFactory->getEditable('simplesamlphp_custom_attributes.mappings');
    $this->entityFieldManager = $entity_field_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_field.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'simplesamlphp_custom_attributes_edit_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $mapping = NULL) {
    $saml_attribute = '';
    $field_name = '';
    if ($mapping !== NULL) {
      // Get the mappings from the settings.
      $mappings = $this->mappingConfig->get('mappings');
      // Get this specific mapping attribute name and field name.
      $saml_attribute = $mappings[$mapping]['attribute_name'];
      $field_name = $mappings[$mapping]['field_name'];
    }

    // Get any fields that are not provided by core's user module. They could be
    // added via Field API, or as base fields on the user entity.
    $options = ['custom' => $this->t('Custom')];
    $fields = $this->entityFieldManager->getFieldDefinitions('user', 'user');
    foreach ($fields as $name => $field) {
      if ($field->getFieldStorageDefinition()->getProvider() != 'user') {
        $options[$name] = $field->getLabel();
      }
    }

    $form['attribute_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('SAML Attribute'),
      '#description' => $this->t('The name of the SAML attribute you want to sync to the user profile.'),
      '#required' => TRUE,
      '#default_value' => $saml_attribute,
    ];

    $form['field_name'] = [
      '#type' => 'select',
      '#title' => $this->t('User Field'),
      '#description' => $this->t('The user field you want to sync this attribute to.'),
      '#required' => TRUE,
      '#options' => $options,
      '#default_value' => $field_name,
    ];

    // Add this value so we know if it's an add or an edit.
    $form['mapping_id'] = [
      '#type' => 'hidden',
      '#value' => $mapping,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $mappings = $this->mappingConfig->get('mappings');

    // If this is a new mapping, check to make sure the same one isn't already
    // defined.
    if ($mappings && !$form_state->getValue('mapping_id')) {
      foreach ($mappings as $mapping) {
        if ($mapping['attribute_name'] === $form_state->getValue('attribute_name') && $mapping['field_name'] === $form_state->getValue('field_name')) {
          $form_state->setErrorByName('field_name', $this->t('This SAML attribute has already been mapped to this field.'));
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $mappings = $this->mappingConfig->get('mappings');

    // Set up the new mapping to add to the array.
    $mapping = [
      'attribute_name' => $form_state->getValue('attribute_name'),
      'field_name' => $form_state->getValue('field_name'),
    ];

    // If we're editing, update the value, if we're adding, add it.
    $mapping_id = $form_state->getValue('mapping_id');
    if (is_numeric($mapping_id)) {
      $mappings[$mapping_id] = $mapping;
    }
    else {
      $mappings[] = $mapping;
    }

    // Save the config with the new mappings.
    $this->mappingConfig->set('mappings', $mappings)->save();

    // Go back to the listing page.
    $form_state->setRedirect('simplesamlphp_custom_attributes.list');
  }

}
