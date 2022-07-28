<?php

namespace Drupal\userswitch;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\SessionManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\user\UserInterface;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Defines a UserSwitch service to switch user account.
 */
class UserSwitch {

  use StringTranslationTrait;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The session manager.
   *
   * @var \Symfony\Component\HttpFoundation\Session\Session
   */
  protected $sessionManager;

  /**
   * The session manager.
   *
   * @var Symfony\Component\HttpFoundation\Session\Session
   */
  protected $session;

  /**
   * Drupal\Core\Messenger\MessengerInterface definition.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Drupal\Core\Logger\LoggerChannelInterface definition.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Constructs.
   *
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Session\SessionManagerInterface $session_manager
   *   The session manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Symfony\Component\HttpFoundation\Session\Session $session
   *   The session.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   * @param \Drupal\Core\Logger\LoggerChannelFactory $logger_channel_factory
   *   The channel logger.
   */
  public function __construct(AccountInterface $current_user, ModuleHandlerInterface $module_handler, SessionManagerInterface $session_manager, EntityTypeManagerInterface $entity_type_manager, Session $session, MessengerInterface $messenger, LoggerChannelFactoryInterface $logger_channel_factory) {
    $this->moduleHandler = $module_handler;
    $this->currentUser = $current_user;
    $this->sessionManager = $session_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->session = $session;
    $this->messenger = $messenger;
    $this->logger = $logger_channel_factory->get('UserSwitch');
  }

  /**
   * Check session.
   *
   * @return bool
   *   TRUE when user switch account, FALSE otherwise.
   *
   */
  public function isSwitchUser() {
    return !empty($_SESSION['SwitchCurrentUser']);
  }

  /**
   * Return original user id, FALSE otherwise.
   * Get the user id.
   *
   * @return bool|mixed
   *   Return original user id, FALSE otherwise.
   */
  public function getUserId() {
    if (isset($_SESSION['SwitchCurrentUser'])) {
      return $_SESSION['SwitchCurrentUser'];
    }
    else {
      return FALSE;
    }
  }

  /**
   * User account switch.
   *
   * @param \Drupal\user\UserInterface $target_user
   *   The targeted user.
   *
   * @return bool
   *   Returns true if successfully.
   */
  public function switchToOther(UserInterface $target_user) {
    $account = $this->currentUser->getAccount();
    $this->moduleHandler->invokeAll('user_logout', [$account]);
    $this->sessionManager->regenerate();
    $_SESSION['SwitchCurrentUser'] = $account->id();

    try {
      // Check if the account is instance of user.
      if (!$target_user instanceof UserInterface) {
        return FALSE;
      }

      $this->currentUser->setAccount($target_user);
      $this->session->set('uid', $target_user->id());
      $this->moduleHandler->invokeAll('user_login', [$target_user]);
      return TRUE;
    }
    catch (PluginException $e) {
      $this->logger->error($e->getMessage());
    }
    return FALSE;
  }

  /**
   * Switching back to previous user.
   *
   * @return bool
   *   TRUE when switched back previous account.
   */
  public function switchUserBack() {
    if (empty($_SESSION['SwitchCurrentUser'])) {
      return FALSE;
    }

    try {
      $new_user = $this->entityTypeManager
        ->getStorage('user')
        ->load($_SESSION['SwitchCurrentUser']);
      unset($_SESSION['SwitchCurrentUser']);

      // Check if new user instance of user.
      if (!$new_user instanceof UserInterface) {
        return FALSE;
      }

      $account = $this->currentUser->getAccount();
      $this->moduleHandler->invokeAll('user_logout', [$account]);
      $this->sessionManager->regenerate();
      $this->currentUser->setAccount($new_user);
      $this->session->set('uid', $new_user->id());
      $this->moduleHandler->invokeAll('user_login', [$new_user]);
      return TRUE;
    }
    catch (PluginException $e) {
      $this->logger->error($e->getMessage());
    }
    return FALSE;
  }

}
