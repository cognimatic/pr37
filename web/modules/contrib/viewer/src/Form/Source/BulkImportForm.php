<?php

namespace Drupal\viewer\Form\Source;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Import BulkImportForm form controller.
 *
 * @ingroup viewer
 */
class BulkImportForm extends ConfirmFormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new BulkImportForm.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'viewer_source_bulk_import_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you would like to run imports?');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Run Imports');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return Url::fromRoute('entity.viewer_source.collection');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $form['actions']['cancel']['#attributes']['class'][] = 'dialog-cancel';
    $form['description']['#markup'] = $this->t('This will run all imports but Manual at once. Depending on file sizes this may take time please keep the window open.');
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $batch = [
      'title' => $this->t('Bulk Import'),
      'finished' => '\Drupal\viewer\Services\Batch::completeImportCallback',
      'operations' => [],
    ];
    $ids = $this->entityTypeManager->getStorage('viewer_source')
      ->getQuery()
      ->condition('status', 1)
      ->condition('import_frequency', 0, '!=')
      ->accessCheck(TRUE)
      ->execute();
    $entities = $this->entityTypeManager->getStorage('viewer_source')->loadMultiple($ids);
    foreach ($entities as $viewer_source) {
      if ($plugin = $viewer_source->getSourcePlugin()) {
        $settings = $viewer_source->getSettings();
        $plugin->setImportFrequency($viewer_source->getFrequency())
          ->setBatchSettings($settings)
          ->setBatchViewerSourceEntity($viewer_source)
          ->setBatchFile($settings['path'])
          ->setBatchFileSource($viewer_source->getSourcePluginId());
        $batch['operations'][] = [
          '\Drupal\viewer\Services\Batch::upload',
          [$plugin],
        ];
      }
    }
    batch_set($batch);
    $form_state->setRedirect('entity.viewer_source.collection');
  }

}
