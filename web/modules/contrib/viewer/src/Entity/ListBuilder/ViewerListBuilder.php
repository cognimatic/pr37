<?php

namespace Drupal\viewer\Entity\ListBuilder;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Component\Serialization\Json;
use Drupal\viewer\Traits\ArraySorting;

/**
 * Defines a base class to build a listing entities.
 *
 * @ingroup viewer
 */
class ViewerListBuilder extends EntityListBuilder {

  use ArraySorting;

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['name'] = $this->t('Name');
    $header['viewer_plugin'] = $this->t('Viewer');
    $header['viewer_source'] = $this->t('Source');
    $header['status'] = $this->t('Status');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['name'] = $entity->label();
    $row['viewer_plugin'] = ($viewer = $entity->getViewerPlugin()) ? $viewer->getName() : $this->t('Invalid plugin');
    $row['viewer_source'] = ($source = $entity->getViewerSource()) ? $source->label() : $this->t('Extended');
    $row['status'] = $entity->isPublished() ? $this->t('Active') : $this->t('Inactive');
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build = parent::render();
    $build['#attached'] = ['library' => ['viewer/viewer.admin']];
    $build['table']['#empty'] = $this->t('There are no Viewers created, please start by creating Viewer Source.');
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getOperations(EntityInterface $entity) {
    $operations = parent::getOperations($entity);
    $ajax_attributes = [
      'class' => ['use-ajax'],
      'data-dialog-type' => 'modal',
      'data-dialog-options' => Json::encode(['width' => 850]),
    ];
    if ($entity->access('update')) {
      $preview_ajax_attributes = [
        'class' => ['use-ajax'],
        'data-dialog-type' => 'dialog',
        'data-dialog-renderer' => 'off_canvas',
        'data-dialog-options' => Json::encode(['width' => 850]),
      ];
      $operations['iframe_preview'] = [
        'title' => $this->t('Preview'),
        'url' => $this->ensureDestination($entity->toUrl('iframe_preview')),
        'attributes' => $preview_ajax_attributes,
        'weight' => -999,
      ];
      if ($entity->getSettings()) {
        $operations['settings'] = [
          'title' => $this->t('Settings'),
          'url' => $this->ensureDestination($entity->toUrl('settings')),
          'attributes' => $ajax_attributes,
        ];
      }
      if ($entity->getConfiguration()) {
        $operations['configuration'] = [
          'title' => $this->t('Configuration'),
          'url' => $this->ensureDestination($entity->toUrl('configuration')),
          'attributes' => [
            'class' => ['use-ajax'],
            'data-dialog-type' => 'modal',
            'data-dialog-options' => Json::encode(['width' => 850]),
          ],
        ];
      }
      if ($viewer = $entity->getViewerPlugin()) {
        if ($viewer->filterable()) {
          $operations['filters'] = [
            'title' => $this->t('Filters'),
            'url' => $this->ensureDestination($entity->toUrl('filters')),
            'attributes' => [
              'class' => ['use-ajax'],
              'data-dialog-type' => 'modal',
              'data-dialog-options' => Json::encode(['width' => 850]),
            ],
          ];
        }
      }
      $operations['endpoint'] = [
        'title' => $this->t('Endpoint'),
        'url' => $this->ensureDestination($entity->toUrl('endpoint')),
        'attributes' => [
          'class' => ['use-ajax'],
          'data-dialog-type' => 'modal',
          'data-dialog-options' => Json::encode(['width' => 650]),
        ],
      ];
    }
    if ($entity->isPublished() && $entity->access('administer')) {
      $operations['disable'] = [
        'title' => $this->t('Disable'),
        'url' => $this->ensureDestination($entity->toUrl('disable')),
      ];
    }
    if (!$entity->isPublished() && $entity->access('administer')) {
      $operations['enable'] = [
        'title' => $this->t('Enable'),
        'url' => $this->ensureDestination($entity->toUrl('enable')),
      ];
    }
    if (!empty($operations['edit'])) {
      $ajax_attributes['data-dialog-options'] = Json::encode(['width' => 600]);
      $operations['edit']['attributes'] = $ajax_attributes;
    }
    if (!empty($operations['delete'])) {
      $ajax_attributes['data-dialog-options'] = Json::encode(['width' => 600]);
      $operations['delete']['attributes'] = $ajax_attributes;
      $operations = $this->moveOperationBottom($operations, 'delete');
    }
    if (!empty($operations['iframe_preview'])) {
      $operations = $this->moveOperationUp($operations, 'iframe_preview');
    }
    return $operations;
  }

}
