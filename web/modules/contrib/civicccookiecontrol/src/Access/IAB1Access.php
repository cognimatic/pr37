<?php

namespace Drupal\civiccookiecontrol\Access;

use Drupal\civiccookiecontrol\CCCConfigNames;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The service that checks access to IAB1 binded to _iab1_access_check.
 */
class IAB1Access extends CookieControlAccess implements AccessInterface {
  /**
   * The cookie control configuration object.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $cccSettingsConfig;

  /**
   * The service constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   The injected config factory object.
   */
  public function __construct(ConfigFactoryInterface $config) {
    $this->cccSettingsConfig = $config->getEditable(CCCConfigNames::COOKIECONTROL);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
          $container->get('config.factory')
      );
  }

  /**
   * Checks if current users has access to IAB1 configuration form.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user object of current logged user.
   *
   * @return \Drupal\Core\Access\AccessResult|\Drupal\Core\Access\AccessResultAllowed|\Drupal\Core\Access\AccessResultNeutral
   *   Returns the access result to grant user access.
   */
  public function access(AccountInterface $account) {
    return AccessResult::allowedIf(
          $account->hasPermission('administer civiccookiecontrol') &&
          $this->checkApiKey() &&
          ($this->cccSettingsConfig->get('civiccookiecontrol_api_key_version') == 8)
      );
  }

}
