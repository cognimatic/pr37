<?php

namespace Drupal\viewer\Services;

use GuzzleHttp\Client;
use Drupal\Core\Mail\MailManager;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\viewer\Event\ViewerEventType;
use Drupal\viewer\Event\ViewerEvent;

/**
 * A service to send Slack/Email notifications.
 *
 * @ingroup viewer
 */
class Notifications {

  use StringTranslationTrait;

  /**
   * Logger factory.
   *
   * @var Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * HTTP client.
   *
   * @var GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * Mail manager service.
   *
   * @var Drupal\Core\Mail\MailManager
   */
  protected $mailManager;

  /**
   * Notification service constructor.
   */
  public function __construct(LoggerChannelFactoryInterface $logger_factory, Client $http_client, MailManager $mail_manager) {
    $this->loggerFactory = $logger_factory;
    $this->httpClient = $http_client;
    $this->mailManager = $mail_manager;
  }

  /**
   * Send a message to a Slack channel.
   */
  public function pushSlack(ViewerEvent $event) {
    $entity = $event->getEntity();
    if ($event->validateType('success') && $event->getEventType() == ViewerEventType::IMPORT_SUCCESS) {
      $message = $this->t('Success: @label successfully imported', ['@label' => $entity->label()]);
    }
    if ($event->validateType('failed') && $event->getEventType() == ViewerEventType::IMPORT_FAILED) {
      $message = $this->t('Failed: @label imported failed', ['@label' => $entity->label()]);
    }
    if (!empty($message)) {
      $request = $this->httpClient->post($event->getSlackHookUrl(), ['json' => ['text' => $message]]);
      if ($request->getStatusCode() !== 200) {
        $this->loggerFactory->get('viewer')
          ->warning($this->t('Unable to send Slack notification for %label', ['%label' => $entity->label()]));
      }
    }
  }

  /**
   * Send a message to a list of email addresses.
   */
  public function pushEmail(ViewerEvent $event) {
    $entity = $event->getEntity();
    $params = [];
    if ($event->validateType('success') && $event->getEventType() == ViewerEventType::IMPORT_SUCCESS) {
      $params['subject'] = $this->t('Success: @label successfully imported', ['@label' => $entity->label()]);
    }
    if ($event->validateType('failed') && $event->getEventType() == ViewerEventType::IMPORT_FAILED) {
      $params['subject'] = $this->t('Failed: @label imported failed', ['@label' => $entity->label()]);
    }
    if (!empty($params['subject'])) {
      $params['message'] = $params['subject'];
      $mailer = $this->mailManager
        ->mail('viewer', 'notification', $event->getEmailAddresses(), 'en', $params, NULL, TRUE);
      if ($mailer['result'] !== TRUE) {
        $this->loggerFactory->get('viewer')
          ->warning($this->t('Unable to send Email notification for %label', ['%label' => $entity->label()]));
      }
    }
  }

}
