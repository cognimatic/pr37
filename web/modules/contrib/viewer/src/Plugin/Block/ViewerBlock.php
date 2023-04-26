<?php

namespace Drupal\viewer\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Provides a 'ViewerBlock' block.
 *
 * @Block(
 *  id = "viewer",
 *  admin_label = @Translation("Viewer"),
 * )
 */
class ViewerBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $viewer = '';
    if (!empty($this->configuration['viewer_reference'])) {
      if ($exists = $this->entityTypeManager->getStorage('viewer')->load($this->configuration['viewer_reference'])) {
        $viewer = $exists;
      }
    }
    $form['viewer_reference'] = [
      '#title' => $this->t('Viewer'),
      '#type' => 'entity_autocomplete',
      '#target_type' => 'viewer',
      '#default_value' => $viewer,
      '#selection_handler' => 'default',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['viewer_reference'] = $form_state->getValue('viewer_reference');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    if (empty($this->configuration['viewer_reference'])) {
      return $build;
    }
    if ($viewer = $this->entityTypeManager->getStorage('viewer')->load($this->configuration['viewer_reference'])) {
      if ($plugin = $viewer->getViewerPlugin()) {
        $plugin->setViewer($viewer);
        if ($viewer->isPublished()) {
          if ($plugin->requirementsAreMet()) {
            $build['viewer'] = $plugin->getRenderable();
          }
          else {
            $build['viewer'] = [
              '#markup' => $this->t(
                'Required conditions are not met for the %name',
                ['%name' => $viewer->label()]
              ),
            ];
          }
        }
        else {
          $build['viewer'] = ['#markup' => $this->t('%name is inactive', ['%name' => $viewer->label()])];
        }
        if (!empty($build['viewer'])) {
          if ($viewer_source = $viewer->getViewerSource()) {
            $build['viewer']['#cache']['tags'] = [
              'viewer_source:' . $viewer_source->id(),
              'viewer:' . $viewer->id(),
            ];
          }
          else {
            $build['viewer']['#cache']['tags'] = ['viewer:' . $viewer->id()];
          }
        }
      }
    }
    return $build;
  }

}
