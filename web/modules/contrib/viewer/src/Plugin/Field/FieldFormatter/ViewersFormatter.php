<?php

namespace Drupal\viewer\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'viewer' formatter.
 *
 * @FieldFormatter(
 *   id = "viewer",
 *   label = @Translation("Viewer"),
 *   field_types = {
 *     "viewer"
 *   }
 * )
 */
class ViewersFormatter extends FormatterBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a ViewersFormatter instance.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);

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
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];
    foreach ($items as $delta => $item) {
      if ($viewer = $this->entityTypeManager->getStorage('viewer')->load($item->target_id)) {
        if ($plugin = $viewer->getViewerPlugin()) {
          $plugin->setViewer($viewer);
          if ($viewer->isPublished()) {
            if ($plugin->requirementsAreMet()) {
              $element[$delta] = $plugin->getRenderable();
            }
            else {
              $element[$delta] = [
                '#markup' => $this->t(
                  'Required conditions are not met for the %name',
                  ['%name' => $viewer->label()]
                ),
              ];
            }
          }
          else {
            $element[$delta] = ['#markup' => $this->t('%name is inactive', ['%name' => $viewer->label()])];
          }
          if (!empty($element[$delta])) {
            if ($viewer_source = $viewer->getViewerSource()) {
              $element[$delta]['#cache']['tags'] = [
                'viewer_source:' . $viewer_source->id(),
                'viewer:' . $viewer->id(),
              ];
            }
            else {
              $element[$delta]['#cache']['tags'] = ['viewer:' . $viewer->id()];
            }
          }
        }
      }
    }
    return $element;
  }

}
