<?php

namespace Drupal\civiccookiecontrol\Access;

use Drupal\civiccookiecontrol\CCCConfigNames;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The service that checks access to IAB2 binded to _iab2_enabled_access_check.
 */
class IAB2EnabledAccess extends CookieControlAccess implements AccessInterface {

  /**
   * The cookie control configuration object.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $cccSettingsConfig;
  /**
   * The IAB 2 configuration object.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $iab2Config;
  /**
   * The IAB1 access service.
   *
   * @var IAB1Access
   */
  protected $iab1Access;

  /**
   * The constructor of the service that checks access to IAB2.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   Inject config factory service.
   * @param IAB1Access $iab1Access
   *   Inject service to check IAB1 access.
   */
  public function __construct(ConfigFactoryInterface $config, IAB1Access $iab1Access) {
    $this->cccSettingsConfig = $config->getEditable(CCCConfigNames::COOKIECONTROL);
    $this->iab2Config = $config->getEditable(CCCConfigNames::IAB2);
    $this->iab1Access = $iab1Access;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
          $container->get('config.factory'),
          $container->get('civiccookiecontrol.IAB1Access')
      );
  }

  /**
   * Checks if current users has access to IAB2 configuration form.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user object of current logged user.
   *
   * @return \Drupal\Core\Access\AccessResult|\Drupal\Core\Access\AccessResultAllowed|\Drupal\Core\Access\AccessResultNeutral
   *   Returns the access result to grant user access.
   */
  public function access(AccountInterface $account) {
    if ($this->cccSettingsConfig
      ->get('civiccookiecontrol_api_key_version') == 8) {
      return $this->iab1Access->checkAccess($account);
    }
    elseif ($this->cccSettingsConfig
      ->get('civiccookiecontrol_api_key_version') == 9) {
      return AccessResult::allowedIf(
            $account->hasPermission('administer civiccookiecontrol') &&
            $this->checkApiKey() &&
            ($this->iab2Config->get('iabCMP') == FALSE)
            );
    }
  }

}
