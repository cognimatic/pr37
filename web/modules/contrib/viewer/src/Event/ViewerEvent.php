<?php

namespace Drupal\viewer\Event;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Component\EventDispatcher\Event;

/**
 * Viewer event class to send notifications.
 *
 * @package viewer
 */
class ViewerEvent extends Event {

  /**
   * The Entity.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  private $entity;

  /**
   * The event type.
   *
   * @var mixed
   */
  private $eventType;

  /**
   * Construct ViewerEvent event.
   */
  public function __construct($event_type, EntityInterface $entity) {
    $this->eventType = $event_type;
    $this->entity = $entity;
  }

  /**
   * Get entity.
   */
  public function getEntity() {
    return $this->entity;
  }

  /**
   * Get event type.
   */
  public function getEventType() {
    return $this->eventType;
  }

  /**
   * Get notification settings.
   */
  public function getSettings() {
    return $this->getEntity()->getSetting('notifications');
  }

  /**
   * Check if notification is a type of `success` or `failed`.
   */
  public function validateType($type) {
    $settings = $this->getSettings();
    return !empty($settings['type'])
      ? in_array($settings['type'], ['both', $type])
      : FALSE;
  }

  /**
   * Check if this is a slack notification.
   */
  public function isSlack() {
    $settings = $this->getSettings();
    return !empty($settings['slack']);
  }

  /**
   * Get slack hook URL.
   */
  public function getSlackHookUrl() {
    $settings = $this->getSettings();
    return !empty($settings['slack_hook_url']) ? $settings['slack_hook_url'] : FALSE;
  }

  /**
   * Check if this is an email notification.
   */
  public function isEmail() {
    $settings = $this->getSettings();
    return !empty($settings['email']);
  }

  /**
   * Get email addresses.
   */
  public function getEmailAddresses() {
    $settings = $this->getSettings();
    return !empty($settings['email_addresses']) ? $settings['email_addresses'] : FALSE;
  }

}
