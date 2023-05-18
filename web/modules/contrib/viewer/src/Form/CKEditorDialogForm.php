<?php

namespace Drupal\viewer\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\Xss;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\editor\Ajax\EditorDialogSave;

/**
 * Ckeditor dialog form to insert Viewer block.
 */
class CKEditorDialogForm extends FormBase {
 
  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a CKEditorDialogForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ckeditor5_viewer_dialog_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, string $uuid = NULL) {
    $request = $this->getRequest();

    if ($uuid) {
      $form['uuid'] = [
        '#type' => 'value',
        '#value' => $uuid,
      ];
    }
    $viewers = $this->getViewers();
    $form['viewer'] = [
      '#title' => $this->t('Viewer'),
      '#type' => 'select',
      '#options' => $viewers,
      '#default_value' => !empty($request->get('viewer')) ? (int) $request->get('viewer') : array_key_first($viewers),
      '#required' => TRUE,
    ];

    $form['actions'] = [
      '#type' => 'actions',
      'submit' => [
        '#type' => 'submit',
        '#value' => !empty($request->get('viewer')) ? $this->t('Update') : $this->t('Insert'),
        '#button_type' => 'primary',
        '#ajax' => [
          'callback' => [$this, 'ajaxSubmitForm'],
        ],
      ],
    ];

    return $form;
  }

  /**
   * Ajax submit callback to insert or replace the html in ckeditor.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse|array
   *   Ajax response for injecting html in ckeditor.
   */
  public static function ajaxSubmitForm(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $response->addCommand(new EditorDialogSave([
      'attributes' => [
        'data-viewer' => $form_state->getValue('viewer'),
      ],
    ]));
    $response->addCommand(new CloseModalDialogCommand());
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Required but not used.
  }

  /**
   * Get all viewers.
   */
  protected function getViewers() {
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
    return $options;
  }

}
