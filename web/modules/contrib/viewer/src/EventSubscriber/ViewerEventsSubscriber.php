<?php

namespace Drupal\viewer\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\viewer\Services\Notifications;
use Drupal\viewer\Event\ViewerEventType;
use Drupal\viewer\Event\ViewerEvent;

/**
 * Viewer event subscriber to send notifications.
 *
 * @package viewer
 */
class ViewerEventsSubscriber implements EventSubscriberInterface {

  /**
   * Notifications service.
   *
   * @var Drupal\viewer\Services\Notifications
   */
  protected $notifications;

  /**
   * Constructor.
   */
  public function __construct(Notifications $notifications) {
    $this->notifications = $notifications;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      ViewerEventType::IMPORT_SUCCESS => 'push',
      ViewerEventType::IMPORT_FAILED => 'push',
    ];
  }

  /**
   * React to successful Viewer Source import/upload.
   *
   * @param \Drupal\viewer\Event\ViewerEvent $event
   *   Entity event.
   */
  public function push(ViewerEvent $event) {
    if ($event->isSlack() && $event->getSlackHookUrl()) {
      $this->notifications->pushSlack($event);
    }
    if ($event->isEmail() && $event->getEmailAddresses()) {
      $this->notifications->pushEmail($event);
    }
  }

}
