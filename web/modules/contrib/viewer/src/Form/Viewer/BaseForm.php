<?php

namespace Drupal\viewer\Form\Viewer;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;
use Drupal\viewer\Plugin\ViewerManager;
use Drupal\viewer\Plugin\ViewerCellManager;
use Drupal\viewer\Traits\TempKeyValTrait;
use Drupal\viewer\Entity\Viewer;

/**
 * BaseForm abstract class.
 *
 * @ingroup viewer
 */
abstract class BaseForm extends FormBase {

  use DependencySerializationTrait;
  use TempKeyValTrait;

  /**
   * Temporary store factory.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $tempStoreFactory;

  /**
   * Private temp store factory.
   *
   * @var \Drupal\user\PrivateTempStore
   */
  protected $store;

  /**
   * Viewer manager.
   *
   * @var \Drupal\viewer\Plugin\ViewerManager
   */
  protected $viewerManager;

  /**
   * Viewer cell manager.
   *
   * @var \Drupal\viewer\Plugin\ViewerCellManager
   */
  protected $viewerCellManager;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a \Drupal\viewer\BaseForm.
   */
  public function __construct(PrivateTempStoreFactory $temp_store_factory, ViewerManager $viewer, ViewerCellManager $cell_manager, EntityTypeManagerInterface $entity_type_manager) {
    $this->tempStoreFactory = $temp_store_factory;
    $this->store = $this->tempStoreFactory->get('viewer_multistep_data');
    $this->viewerManager = $viewer;
    $this->viewerCellManager = $cell_manager;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('tempstore.private'),
      $container->get('plugin.manager.viewer'),
      $container->get('plugin.manager.viewer_cell'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * Redirect to listing page.
   */
  protected function redirecToListing() {
    return new RedirectResponse(Url::fromRoute('entity.viewer.collection')->toString());
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
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
      '#url' => Url::fromRoute('entity.viewer.collection'),
      '#weight' => 10,
      '#attributes' => ['class' => 'button'],
    ];
    $form_state->disableCache();
    return $form;
  }

  /**
   * Create new Viewer entity.
   */
  public function save() {
    $viewer_plugin_id = $this->getKeyVal('viewer');
    $plugin = $this->viewerManager->createInstance($viewer_plugin_id);
    $viewer = Viewer::create([]);
    $viewer->setName($this->getKeyVal('name'));
    $viewer->setViewerPluginId($viewer_plugin_id);
    if ($plugin->isEmptyViewerSource()) {
      $viewer->setViewerSource(NULL);
    }
    else {
      $viewer->setViewerSource($this->getKeyVal('source'));
      $viewer->mergeIntoSettings(['add_headers' => $this->getKeyVal('add_headers', FALSE)]);
    }
    $viewer->setSettings($this->getKeyVal('settings', []));
    $viewer->setConfiguration($this->getKeyVal('configuration', []));
    $viewer->save();
  }

  /**
   * Get Viewer plugins.
   */
  protected function getViewerPlugins($no_datasource = FALSE) {
    $plugins = [];
    foreach ($this->viewerManager->getDefinitions() as $id => $plugin) {
      $plugin = $this->viewerManager->createInstance($plugin['id']);
      if ($no_datasource) {
        if ($plugin->isEmptyViewerSource()) {
          $plugins[$id] = $plugin->getName();
        }
      }
      else {
        $plugins[$id] = $plugin->getName();
      }
    }
    return $plugins;
  }

  /**
   * Get viewer plugins as options.
   */
  protected function getViewerPluginOptions($source_id = NULL) {
    $type = '';
    if ($viewer_source = $this->loadViewerSource($source_id)) {
      $type = $viewer_source->getTypePlugin()->getPluginId();
    }
    $viewer_plugins = [];
    foreach ($this->getViewerPlugins() as $plugin_id => $label) {
      $plugin_label_string = $label->getUntranslatedString();
      $plugin = $this->viewerManager->createInstance($plugin_id);
      if ($types = $plugin->viewerTypes()) {
        if (in_array($type, $types)) {
          if (strstr($plugin_label_string, ':')) {
            [$group, $group_plugin_label] = explode(':', $plugin_label_string);
            $viewer_plugins[$group][$plugin_id] = trim($group_plugin_label);
          }
          else {
            $viewer_plugins['General'][$plugin_id] = $plugin_label_string;
          }
        }
      }
      else {
        $viewer_plugins['General'][$plugin_id] = $plugin_label_string;
      }
    }
    return $viewer_plugins;
  }

  /**
   * Get default viewer plugin ID from the viewer type.
   */
  protected function getDefaultViewerPlugin($source_id) {
    if ($viewer_source = $this->loadViewerSource($source_id)) {
      return $viewer_source->getTypePlugin()->getDefaultViewerPluginId();
    }
  }

  /**
   * Get cell plugins.
   */
  protected function getCellPlugins() {
    $plugins = [];
    $plugins['as_is'] = $this->t('As is');
    foreach ($this->viewerCellManager->getDefinitions() as $id => $plugin) {
      $plugin = $this->viewerCellManager->createInstance($plugin['id']);
      if (!empty($plugin->getApplicableViewers()) && in_array($this->getKeyVal('viewer'), $plugin->getApplicableViewers())) {
        $plugins[$id] = $plugin->getName();
      }
    }
    return $plugins;
  }

  /**
   * Get list of all available Viewer Sources.
   */
  protected function getSources() {
    $sources = [];
    $storage = $this->entityTypeManager->getStorage('viewer_source');
    $ids = $storage->getQuery()
      ->sort('created', 'DESC')
      ->accessCheck(TRUE)
      ->execute();
    $entities = $storage->loadMultiple($ids);
    foreach ($entities as $entity) {
      $status = $entity->isPublished() ? $this->t('Active') : $this->t('Inactive');
      $sources[$entity->id()] = $entity->label() . ' (' . $status . ')';
    }
    return $sources;
  }

  /**
   * Load Viewer Source by ID.
   */
  protected function loadViewerSource($id) {
    if (!empty($id)) {
      if ($viewer_source = $this->entityTypeManager->getStorage('viewer_source')->load($id)) {
        return $viewer_source;
      }
    }
  }

}
