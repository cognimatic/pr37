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
class ViewerSourceListBuilder extends EntityListBuilder {

  use ArraySorting;

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['name'] = $this->t('Name');
    $header['size'] = $this->t('Size');
    $header['type_plugin'] = $this->t('Type');
    $header['source_plugin'] = $this->t('Source');
    $header['import_frequency'] = $this->t('Frequency');
    $header['last_import'] = $this->t('Last Import');
    $header['next_import'] = $this->t('Next Import');
    $header['status'] = $this->t('Status');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $next_import = $entity->getNextImport();
    $row['name'] = $entity->label();
    $row['size'] = $entity->getReadableFileSize();
    $row['type_plugin'] = $entity->getTypePlugin()->getName();
    $row['source_plugin'] = ($viewer_source = $entity->getSourcePlugin()) ? $viewer_source->getName() : $this->t('Invalid plugin');
    $row['import_frequency'] = $entity->getReadableFrequency();
    $row['last_import'] = ($last_import = $entity->getLastImport()) ? $last_import : $this->t('N/A');
    $row['next_import'] = (!empty($next_import) && !empty($entity->getFrequency())) ? $next_import : $this->t('Manual');
    $row['status'] = $entity->isPublished() ? $this->t('Active') : $this->t('Inactive');
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build = parent::render();
    $build['#attached'] = ['library' => ['viewer/viewer.admin']];
    $build['table']['#empty'] = $this->t('There are no Viewer Sources, start by creating new source.');
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
      'data-dialog-options' => Json::encode(['width' => 600]),
    ];
    if ($entity->access('import')) {
      $operations['import'] = [
        'title' => !empty($entity->getFrequency()) ? $this->t('Import') : $this->t('Upload'),
        'url' => $this->ensureDestination($entity->toUrl('import')),
        'attributes' => $ajax_attributes,
        'weight' => -999,
      ];
    }
    if ($entity->access('import')) {
      $operations['download'] = [
        'title' => $this->t('Download'),
        'url' => $this->ensureDestination($entity->toUrl('download')),
      ];
    }
    if ($entity->access('update') && !empty($entity->getFrequency())) {
      $operations['schedule'] = [
        'title' => $this->t('Schedule'),
        'url' => $this->ensureDestination($entity->toUrl('schedule')),
        'attributes' => $ajax_attributes,
      ];
    }
    if ($entity->access('update')) {
      if ($entity->getSettings()) {
        $operations['settings'] = [
          'title' => $this->t('Settings'),
          'url' => $this->ensureDestination($entity->toUrl('settings')),
          'attributes' => $ajax_attributes,
        ];
      }
    }
    if ($entity->access('administer')) {
      $operations['notifications'] = [
        'title' => $this->t('Notifications'),
        'url' => $this->ensureDestination($entity->toUrl('notifications')),
        'attributes' => $ajax_attributes,
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
      $operations['edit']['attributes'] = $ajax_attributes;
    }
    if (!empty($operations['delete'])) {
      $operations['delete']['attributes'] = $ajax_attributes;
      $operations = $this->moveOperationBottom($operations, 'delete');
    }
    if (!empty($operations['import'])) {
      $operations = $this->moveOperationUp($operations, 'import');
    }
    return $operations;
  }

}
