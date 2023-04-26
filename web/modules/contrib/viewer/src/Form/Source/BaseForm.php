<?php

namespace Drupal\viewer\Form\Source;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;
use Drupal\viewer\Plugin\ViewerSourceManager;
use Drupal\viewer\Plugin\ViewerCellManager;
use Drupal\viewer\Plugin\ViewerTypeManager;
use Drupal\viewer\Traits\TempKeyValTrait;

/**
 * BaseForm abstract class.
 *
 * @ingroup viewer
 */
abstract class BaseForm extends FormBase {

  use DependencySerializationTrait;
  use TempKeyValTrait;

  /**
   * Private temporary factory.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $tempStoreFactory;

  /**
   * Private temporary factory.
   *
   * @var \Drupal\user\PrivateTempStore
   */
  protected $store;

  /**
   * Viewer Source service.
   *
   * @var \Drupal\viewer\Plugin\ViewerSourceManager
   */
  protected $viewerSourceManager;

  /**
   * Viewer cell service.
   *
   * @var \Drupal\viewer\Plugin\ViewerCellManager
   */
  protected $viewerCellManager;

  /**
   * Viewer type service.
   *
   * @var \Drupal\viewer\Plugin\ViewerTypeManager
   */
  protected $viewerTypeManager;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a \Drupal\viewer\BaseForm.
   */
  public function __construct(PrivateTempStoreFactory $temp_store_factory, ViewerSourceManager $viewer_source, ViewerCellManager $viewer_cell, ViewerTypeManager $viewer_type, EntityTypeManagerInterface $entity_type_manager) {
    $this->tempStoreFactory = $temp_store_factory;
    $this->store = $this->tempStoreFactory->get('viewer_source_multistep_values');
    $this->viewerSourceManager = $viewer_source;
    $this->viewerCellManager = $viewer_cell;
    $this->viewerTypeManager = $viewer_type;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('tempstore.private'),
      $container->get('plugin.manager.viewer_source'),
      $container->get('plugin.manager.viewer_cell'),
      $container->get('plugin.manager.viewer_type'),
      $container->get('entity_type.manager'),
      $container->get('logger.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = [];
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
      '#weight' => 10,
    ];
    $form['actions']['cancel'] = [
      '#type' => 'link',
      '#title' => $this->t('Cancel'),
      '#url' => Url::fromRoute('entity.viewer_source.collection'),
      '#weight' => 10,
      '#attributes' => ['class' => 'button'],
    ];
    $form_state->disableCache();
    return $form;
  }

  /**
   * Redirect to listing page.
   */
  protected function redirecToListing() {
    return new RedirectResponse(Url::fromRoute('entity.viewer_source.collection')->toString());
  }

  /**
   * Get Viewer Source plugins.
   */
  protected function getSourcePlugins() {
    $plugins = [];
    foreach ($this->viewerSourceManager->getDefinitions() as $id => $plugin) {
      $plugin = $this->viewerSourceManager->createInstance($plugin['id']);
      $plugins[$id] = $plugin->getName();
    }
    return $plugins;
  }

  /**
   * Get Viewer Cell plugins.
   */
  protected function getCellPlugins() {
    $plugins = [];
    foreach ($this->viewerCellManager->getDefinitions() as $id => $plugin) {
      $plugin = $this->viewerCellManager->createInstance($plugin['id']);
      $plugins[$id] = $plugin->getName();
    }
    return $plugins;
  }

  /**
   * Get Viewer Type plugins.
   */
  protected function getTypePlugins() {
    $plugins = [];
    foreach ($this->viewerTypeManager->getDefinitions() as $id => $plugin) {
      $plugin = $this->viewerTypeManager->createInstance($plugin['id']);
      $plugins[$id] = $plugin->getName();
    }
    return $plugins;
  }

}
