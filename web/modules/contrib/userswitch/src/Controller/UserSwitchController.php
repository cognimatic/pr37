<?php

namespace Drupal\userswitch\Controller;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Database\Connection;
use Drupal\user\UserInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\userswitch\UserSwitch;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Provides route responses for the Example module.
 */
class UserSwitchController extends ControllerBase {

  /**
   * Drupal\userswitch\UserSwitch definition.
   *
   * @var \Drupal\userswitch\UserSwitch
   */
  protected $userSwitch;

  /**
   * Drupal\Core\Session\AccountInterface definition.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Drupal\Core\Database\Connection definition.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Drupal\Core\Messenger\MessengerInterface definition.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs a new UserSwitchController object.
   */
  public function __construct(AccountInterface $currentUser, UserSwitch $userSwitch, Connection $database, MessengerInterface $messenger) {
    $this->currentUser = $currentUser;
    $this->userSwitch = $userSwitch;
    $this->database = $database;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user'),
      $container->get('userswitch'),
      $container->get('database'),
      $container->get('messenger')
    );
  }

  /**
   * Returns a simple page.
   *
   * @return array
   *   A simple renderable array.
   */
  public function userSwitchList() {

    $current_uid = $this->currentUser->id();

    try {
      $header = [
        'id' => [
          'data' => $this->t('ID'),
          'specifier' => 'uid',
          'sort' => 'desc',
        ],
      ];
      $storage = \Drupal::entityTypeManager()->getStorage('user');
      $query = $storage->getQuery();
      $query->condition('status', '1')
        ->condition('uid', $current_uid, '!=')
        ->condition('uid', '0', '!=')
        ->tableSort($header)
        ->pager(50);
      $results = $query->execute();

      $header = ['#', 'Name', 'Mail', 'Operations'];
      // Initialize an empty array.
      $output = [];

      $users = \Drupal::entityTypeManager()
        ->getStorage('user')
        ->loadMultiple($results);

      // Next, loop through the $results array.
      foreach ($users as $user) {
        if ($user instanceof UserInterface) {
          $url = Url::fromRoute('userswitch.user.switch', ['user' => $user->id()]);

          $_link = Link::fromTextAndUrl($this->t('Switch to account'), $url);
          $output[$user->id()] = [
            'userid' => $user->id(),
            'Username' => $user->label(),
            'email' => $user->get('mail')->value,
            'link' => $_link,
          ];
        }
      }
    $element[] = [
        '#theme' => 'table',
        '#header' => $header,
        '#rows' => $output,
      ];

    $element[] = [
        '#type' => 'pager',
      ];

      return $element;

    }
    catch (PluginException $e) {
      \Drupal::logger('userswitch')->error($e->getMessage());
      return [];
    }
  }

  /**
   * Switch to new user.
   *
   * @param \Drupal\user\UserInterface $user
   *   The selected user.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   Returns redirect response.
   */
  public function switchuser(UserInterface $user) {
    if ($this->userSwitch->switchToOther($user)) {
      $message = $this->t('You are now @user.', ['@user' => $this->currentUser->getDisplayName()]);
      $this->messenger->addMessage($message);
    }

    $url = Url::fromRoute('entity.user.canonical', ['user' => $user->id()])
      ->toString();
    $response = new RedirectResponse($url);
    $response->send();
    return new Response();
  }

  /**
   * Switch back to original user.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   Returns redirect response.
   */
  public function switchbackuser() {
    // Store current user name for messages.
    $account_name = $this->currentUser->getDisplayName();
    $get_uid = $this->userSwitch->getUserId();

    if ($get_uid) {
      if ($this->userSwitch->switchUserBack()) {
        $message = $this->t('Switch account as @user.', ['@user' => $account_name]);
        $this->messenger->addMessage($message);
      }
      else {
        $message = $this->t('Error trying as @user.', ['@user,' => $account_name]);
        $this->messenger->addMessage($message, $this->messenger::TYPE_ERROR);
      }
      $url = Url::fromRoute('entity.user.canonical', ['user' => $get_uid])
        ->toString();
    }
    else {
      $url = Url::fromRoute('user.admin_index');
    }

    $response = new RedirectResponse($url);
    $response->send();
    return new Response();
  }

  /**
   * Checks access for this controller.
   *
   * @return \Drupal\Core\Access\AccessResultAllowed|\Drupal\Core\Access\AccessResultForbidden
   *   Returns access handler.
   */
  public function getUserSwitchPermissions() {
    if ($this->userSwitch->isSwitchUser()) {
      return AccessResult::allowed();
    }
    return AccessResult::forbidden();
  }

}
