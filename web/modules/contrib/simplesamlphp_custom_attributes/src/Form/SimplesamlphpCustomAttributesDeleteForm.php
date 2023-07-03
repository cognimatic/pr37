<?php

namespace Drupal\simplesamlphp_custom_attributes\Form;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * {@inheritdoc}
 */
class SimplesamlphpCustomAttributesDeleteForm extends ConfirmFormBase {

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
   * The name of the attribute we're deleting (needed for the confirm message).
   *
   * @var string
   */
  protected $attributeName;

  /**
   * The name of the field we're deleting (needed for the confirm message).
   *
   * @var string
   */
  protected $fieldName;

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
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'simplesamlphp_custom_attributes_delete_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $mapping = FALSE) {
    if (is_numeric($mapping)) {
      $mappings = $this->mappingConfig->get('mappings');

      // Set these values for the confirm message to pick up on them.
      $this->attributeName = $mappings[$mapping]['attribute_name'];
      $this->fieldName = $mappings[$mapping]['field_name'];

      // Set the mapping id so the submit handler can delete it.
      $form_state->set('simplesamlphp_custom_attributes_mapping', $mapping);

      return parent::buildForm($form, $form_state);
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    // Get the pretty label for the message.
    $fields = $this->entityFieldManager->getFieldDefinitions('user', 'user');
    $field_name = $fields[$this->fieldName]->getLabel();

    return $this->t('Are you sure you want to delete the ":attribute > :field" mapping?', [
      ':attribute' => $this->attributeName,
      ':field' => $field_name,
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('simplesamlphp_custom_attributes.list');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $mappings = $this->mappingConfig->get('mappings');

    // Remove the mapping from the array.
    unset($mappings[$form_state->get('simplesamlphp_custom_attributes_mapping')]);

    // Save the new config.
    $this->mappingConfig->set('mappings', $mappings)->save();

    // Go back to the list page.
    $form_state->setRedirect('simplesamlphp_custom_attributes.list');
  }

}
