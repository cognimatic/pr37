<?php

namespace Drupal\viewer\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Messenger\MessengerInterface;

/**
 * Viewer Source entity related controller.
 */
class ViewerSourceController extends ControllerBase {

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
   * Set Viewer Source active.
   */
  public function setActive($viewer_source) {
    $viewer_source->setActive();
    $viewer_source->save();
    $this->messenger->addMessage($this->t('%name is active', ['%name' => $viewer_source->label()]));
    return $this->redirect('entity.viewer_source.collection');
  }

  /**
   * Set Viewer Source inactive.
   */
  public function setInactive($viewer_source) {
    $viewer_source->setInactive();
    $viewer_source->save();
    $this->messenger->addMessage($this->t('%name is inactive', ['%name' => $viewer_source->label()]));
    return $this->redirect('entity.viewer_source.collection');
  }

  /**
   * Download file.
   */
  public function download($viewer_source) {
    $type_plugin = $viewer_source->getTypePlugin();
    $extensions = array_keys($type_plugin->getExtensions());
    $response = new Response();
    $response->headers->set('Content-Type', $extensions[0]);
    $response->headers->set('Content-Disposition', 'attachment; filename="' . $viewer_source->getFile()->getFilename() . '"');
    $response->setContent($viewer_source->getContents());
    return $response;
  }

  /**
   * Set settings Viewer Source plugin title.
   */
  public function setSettingsTitle($viewer_source) {
    return $this->t('%name Settings', ['%name' => $viewer_source->label()]);
  }

  /**
   * Set next import Viewer Source plugin title.
   */
  public function setNextImportTitle($viewer_source) {
    return $this->t('%name Next Import', ['%name' => $viewer_source->label()]);
  }

  /**
   * Set notification Viewer Source plugin title.
   */
  public function setNotificationsTitle($viewer_source) {
    return $this->t('%name Notifications', ['%name' => $viewer_source->label()]);
  }

}
