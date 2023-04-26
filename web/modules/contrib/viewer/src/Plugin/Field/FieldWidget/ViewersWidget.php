<?php

namespace Drupal\viewer\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'viewer_widget' widget.
 *
 * @FieldWidget(
 *   id = "viewer",
 *   label = @Translation("Viewer"),
 *   field_types = {
 *     "viewer"
 *   }
 * )
 */
class ViewersWidget extends WidgetBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $storage = $this->entityTypeManager->getStorage('viewer');
    $ids = $storage->getQuery()
      ->sort('created', 'DESC')
      ->accessCheck(TRUE)
      ->execute();
    $entities = $storage->loadMultiple($ids);
    $options = [];
    foreach ($entities as $entity) {
      $status = $entity->isPublished() ? $this->t('Active') : $this->t('Inactive');
      $options[$entity->id()] = $entity->label() . ' (' . $status . ')';
    }
    $element['target_id'] = [
      '#title' => $this->t('Viewer'),
      '#type' => 'select',
      '#empty_option' => $this->t('- Select Viewer -'),
      '#options' => $options,
      '#default_value' => $items[$delta]->target_id ?? '',
    ];
    return $element;
  }

}
