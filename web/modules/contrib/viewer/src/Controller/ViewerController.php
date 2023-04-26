<?php

namespace Drupal\viewer\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Messenger\MessengerInterface;

/**
 * Viewer entity related controller.
 */
class ViewerController extends ControllerBase {

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * ModalFormContactController constructor.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   Messenger service.
   */
  public function __construct(MessengerInterface $messenger) {
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The Drupal service container.
   *
   * @return static
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('messenger')
    );
  }

  /**
   * Set Viewer active.
   */
  public function setActive($viewer) {
    $viewer->setActive();
    $viewer->save();
    $this->messenger->addMessage($this->t('%name is active', ['%name' => $viewer->label()]));
    return $this->redirect('entity.viewer.collection');
  }

  /**
   * Set Viewer inactive.
   */
  public function setInactive($viewer) {
    $viewer->setInactive();
    $viewer->save();
    $this->messenger->addMessage($this->t('%name is inactive', ['%name' => $viewer->label()]));
    return $this->redirect('entity.viewer.collection');
  }

  /**
   * Set preview Viewer plugin title.
   */
  public function setPreviewerTitle($viewer) {
    return $this->t('%type: %name Preview (%plugin)', [
      '%name' => $viewer->label(),
      '%plugin' => $viewer->getViewerPlugin()->getName(),
      '%type' => ($source = $viewer->getViewerSource()) ? $source->getTypePlugin()->getName() : 'Extended',
    ]);
  }

  /**
   * Set configuration Viewer plugin title.
   */
  public function setConfigurationTitle($viewer) {
    return $this->t('%name Configuration', ['%name' => $viewer->label()]);
  }

  /**
   * Set settings Viewer plugin title.
   */
  public function setSettingsTitle($viewer) {
    return $this->t('%name Settings', ['%name' => $viewer->label()]);
  }

  /**
   * Set filters Viewer plugin title.
   */
  public function setFiltersTitle($viewer) {
    return $this->t('%name Filters', ['%name' => $viewer->label()]);
  }

  /**
   * Set endpoint Viewer plugin title.
   */
  public function setEndpointTitle($viewer) {
    return $this->t('%name Endpoint', ['%name' => $viewer->label()]);
  }

  /**
   * Preview Viewer plugin.
   */
  public function preview($viewer) {
    $build = [];
    $plugin = $viewer->getViewerPlugin();
    $plugin->setViewer($viewer);
    if ($plugin->requirementsAreMet()) {
      $build['preview'] = $plugin->getRenderable();
      $build['preview']['#prefix'] = '<div class="viewer-preview-wrapper">';
      $build['preview']['#suffix'] = '</div>';
      $build['#attached'] = ['library' => ['viewer/viewer.preview']];
    }
    return $build;
  }

}
