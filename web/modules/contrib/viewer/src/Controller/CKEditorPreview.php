<?php

namespace Drupal\viewer\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Render\Renderer;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountProxy;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\editor\Entity\Editor;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Returns the preview for Viewer.
 */
class CKEditorPreview extends ControllerBase {

  use StringTranslationTrait;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The controller constructor.
   *
   * @param \Drupal\Core\Render\Renderer $renderer
   *   The renderer service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(Renderer $renderer, EntityTypeManagerInterface $entity_type_manager) {
    $this->renderer = $renderer;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('renderer'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * Controller callback that renders the preview for CKeditor.
   */
  public function preview(Request $request, Editor $editor) {
    $viewer = (int) $request->query->get('viewer');

    try {
      if (!$viewer) {
        throw new \Exception();
      }
      
      $storage = $this->entityTypeManager->getStorage('viewer');
      $ids = $storage->getQuery()
        ->condition('id', $viewer)
        ->sort('created', 'DESC')
        ->accessCheck(TRUE)
        ->execute();
      $entities = $storage->loadMultiple($ids);
      if ($entity = reset($entities)) {
        $source = $entity->getViewerSource();
        $build = [
          '#type' => 'inline_template',
          '#template' => '<div class="preview-viewer-tag">{{ viewer }}</div>
            <div class="viewer-preview-wrapper">
            {% if viewer_label %}
              <div class="preview-viewer-item preview-viewer-label"><span>{{ viewer_label }} ({{ viewer_status }})</span></div>
              {% if has_source %}
                <div class="preview-viewer-item preview-viewer-nobg">{{ pullingfrom }} <em>{{ source }}</em></div>
              {% endif %}
            {% else %}
              <div class="preview-viewer-item preview-viewer-na">{{ viewer_na }}</div>
            {% endif %}
            </div>',
          '#context' => [
            'viewer' => $this->t('Viewer'),
            'viewer_na' => $this->t('Invalid Viewer'),
            'pullingfrom' => $this->t('showing data from'),
            'viewer_label' => $entity->label(),
            'viewer_status' => $entity->isPublished() ? $this->t('Active') : $this->t('Inactive'),
            'source' => $this->t('@name (@size, @type)', [
              '@name' => !empty($source) ? $source->label() : FALSE,
              '@size' => !empty($source) ? $source->getReadableFileSize() : FALSE,
              '@type' => !empty($source) ? $source->getTypePluginId() : FALSE,
            ]),
            'has_source' => !empty($source),
          ],
        ];
      }
      else {
        throw new \Exception();
      }
    }
    catch (\Exception $e) {
      $build = [
        'markup' => [
          '#type' => 'markup',
          '#markup' => $this->t('Incorrect configuration for Viewer.'),
        ],
      ];
    }
    return new Response($this->renderer->renderRoot($build));
  }

  /**
   * Access callback for viewing the preview.
   *
   * @param \Drupal\editor\Entity\Editor $editor
   *   The editor.
   * @param \Drupal\Core\Session\AccountProxy $account
   *   The current user.
   *
   * @return \Drupal\Core\Access\AccessResult|\Drupal\Core\Access\AccessResultReasonInterface
   *   The acccess result.
   */
  public function checkAccess(Editor $editor, AccountProxy $account) {
    return AccessResult::allowedIfHasPermission($account, 'use text format ' . $editor->getFilterFormat()->id());
  }

}
