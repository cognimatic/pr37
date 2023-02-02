<?php

namespace Drupal\civiccookiecontrol\CCCConfig;

use Drupal\civiccookiecontrol\CCCConfigNames;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Link;
use Drupal\Component\Serialization\Json;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Abstract for cookie control config generation.
 */
abstract class AbstractCCCConfig {
  use StringTranslationTrait;

  /**
   * Cookie control configuration array.
   *
   * @var array
   */
  protected $config;

  /**
   * Cookie contro configuration object.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $cccConfig;

  /**
   * IAB configuration array.
   *
   * @var array
   */
  protected $iabConfig;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The cache service.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * AbstractCCCConfig constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   Injected config service.
   * @param \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   *   Injected entity type manager service.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $dateFormatter
   *   Injected date formatter service.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Injected cache service.
   */
  public function __construct(
        ConfigFactoryInterface $config,
        EntityTypeManager $entityTypeManager,
        DateFormatterInterface $dateFormatter,
        CacheBackendInterface $cache
    ) {
    $this->cccConfig = $config->get(CCCConfigNames::COOKIECONTROL);
    $this->entityTypeManager = $entityTypeManager;
    $this->dateFormatter = $dateFormatter;
    $this->cache = $cache;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
          $container->get('config.factory'),
          $container->get('entity_type.manager'),
          $container->get('date.formatter'),
          $container->get('cache.data')
      );
  }

  /**
   * Function to construct cookie configuration object.
   */
  public function loadCookieConfig() {
    $this->config['apiKey'] = $this->cccConfig
      ->get('civiccookiecontrol_api_key');
    $this->config['product'] = $this->cccConfig
      ->get('civiccookiecontrol_product');
    $this->config['logConsent'] = $this->cccConfig
      ->get('civiccookiecontrol_log_consent') ? TRUE : FALSE;
    $this->config['consentCookieExpiry'] = (int) $this->cccConfig->get('civiccookiecontrol_consent_cookie_expiry');
    $this->config['encodeCookie'] = $this->cccConfig
      ->get('civiccookiecontrol_encode_cookie') ? TRUE : FALSE;
    $this->config['subDomains'] = $this->cccConfig
      ->get('civiccookiecontrol_sub_domains') ? TRUE : FALSE;
    $this->config['notifyOnce'] = $this->cccConfig
      ->get('civiccookiecontrol_notify_once') ? TRUE : FALSE;
    $this->config['rejectButton'] = $this->cccConfig
      ->get('civiccookiecontrol_reject_button') ? TRUE : FALSE;
    $this->config['toggleType'] = $this->cccConfig
      ->get('civiccookiecontrol_toggle_type');
    $this->config['closeStyle'] = $this->cccConfig
      ->get('civiccookiecontrol_close_style');
    $this->config['settingsStyle'] = $this->cccConfig
      ->get('civiccookiecontrol_settings_style');
    $this->config['initialState'] = $this->cccConfig
      ->get('civiccookiecontrol_initial_state');
    $this->config['layout'] = $this->cccConfig
      ->get('civiccookiecontrol_layout');
    $this->config['position'] = $this->cccConfig
      ->get('civiccookiecontrol_widget_position');
    $this->config['theme'] = $this->cccConfig
      ->get('civiccookiecontrol_widget_theme');
    if (!empty($this->cccConfig
      ->get('civiccookiecontrol_onload'))) {
      $this->config['onLoad'] = "function(){" . $this->cccConfig
        ->get('civiccookiecontrol_onload') . "}";
    }

    $this->config['necessaryCookies'] = $this->loadNecessaryCookieList();
    $this->config['optionalCookies'] = $this->loadCookieCategoryList();
    $this->config['excludedCountries'] = $this->loadExcludedCountryList();

    $this->config['debug'] = $this->cccConfig
      ->get('civiccookiecontrol_debug') ? TRUE : FALSE;
  }

  /**
   * Function to construct statement object configuration object.
   */
  public function loadStatementObject() {
    $type = 'statement';
    $this->config[$type] = [
      'description' => $this->cccConfig
        ->get('civiccookiecontrol_stmt_descr'),
      'name' => $this->cccConfig
        ->get('civiccookiecontrol_stmt_name'),
      'updated' => !empty($this->cccConfig
        ->get('civiccookiecontrol_stmt_date')) ? $this->dateFormatter->format(strtotime($this->cccConfig
        ->get('civiccookiecontrol_stmt_date')), 'custom', 'd/m/Y') : "",
    ];
    if ($nid = $this->cccConfig->get('civiccookiecontrol_privacynode')) {
      $privacyNodeUrl = Link::createFromRoute(
            $this->t("Privacy Policy"),
            'entity.node.canonical',
            ['node' => $nid]
        );
      global $base_url;
      $this->config[$type]['url'] = $base_url . $privacyNodeUrl->getUrl()
        ->toString();
    }

    $this->config[$type] = array_filter($this->config[$type], 'strlen');
  }

  /**
   * Function to construct accessibility object.
   */
  public function loadAccessibilityObject() {
    $this->config['accessibility'] = [
      'accessKey' => $this->cccConfig
        ->get('civiccookiecontrol_access_key'),
      'highlightFocus' => $this->cccConfig
        ->get('civiccookiecontrol_highlight_focus'),
    ];
    $this->config['accessibility'] = array_filter($this->config['accessibility'], 'strlen');
  }

  /**
   * Constructs cookie control text object.
   */
  public function loadTextObject() {
    $this->config['text'] = [
      'title' => $this->cccConfig
        ->get('civiccookiecontrol_title_text'),
      'intro' => $this->cccConfig
        ->get('civiccookiecontrol_intro_text'),
      'acceptRecommended' => $this->cccConfig
        ->get('civiccookiecontrol_accept_recommended'),
      'acceptSettings' => $this->cccConfig
        ->get('civiccookiecontrol_accept_settings'),
      'rejectSettings' => $this->cccConfig
        ->get('civiccookiecontrol_reject_settings'),
      'necessaryTitle' => $this->cccConfig
        ->get('civiccookiecontrol_necessary_title_text'),
      'necessaryDescription' => $this->cccConfig
        ->get('civiccookiecontrol_necessary_desc_text'),
      'thirdPartyTitle' => $this->cccConfig
        ->get('civiccookiecontrol_third_party_title_text'),
      'thirdPartyDescription' => $this->cccConfig
        ->get('civiccookiecontrol_third_party_desc_text'),
      'on' => $this->cccConfig
        ->get('civiccookiecontrol_on_text'),
      'off' => $this->cccConfig
        ->get('civiccookiecontrol_off_text'),
      'notifyTitle' => $this->cccConfig
        ->get('civiccookiecontrol_notify_title_text'),
      'notifyDescription' => $this->cccConfig
        ->get('civiccookiecontrol_notify_desc_text'),
      'accept' => $this->cccConfig
        ->get('civiccookiecontrol_accept_text'),
      'reject' => $this->cccConfig
        ->get('civiccookiecontrol_reject_text'),
      'settings' => $this->cccConfig
        ->get('civiccookiecontrol_setting_text'),
      'closeLabel' => $this->cccConfig
        ->get('civiccookiecontrol_close_label'),
    ];
    $this->config['text'] = array_filter($this->config['text'], 'strlen');
  }

  /**
   * Constructs cookie control branding object.
   */
  public function loadBrandingObject() {
    $this->config['branding'] = [
      'fontFamily' => $this->cccConfig
        ->get('civiccookiecontrol_font_family'),
      'fontSizeTitle' => $this->cccConfig
        ->get('civiccookiecontrol_font_size_title') . 'em',
      'fontSizeHeaders' => $this->cccConfig
        ->get('civiccookiecontrol_font_size_headers'),
      'fontSize' => $this->cccConfig
        ->get('civiccookiecontrol_font_size') . 'em',
      'fontColor' => $this->cccConfig
        ->get('civiccookiecontrol_font_color'),
      'backgroundColor' => $this->cccConfig
        ->get('civiccookiecontrol_background_color'),
      'acceptText' => $this->cccConfig
        ->get('civiccookiecontrol_accept_text_color'),
      'acceptBackground' => $this->cccConfig
        ->get('civiccookiecontrol_accept_background_color'),
      'toggleText' => $this->cccConfig
        ->get('civiccookiecontrol_toggle_text'),
      'toggleColor' => $this->cccConfig
        ->get('civiccookiecontrol_toggle_color'),
      'toggleBackground' => $this->cccConfig
        ->get('civiccookiecontrol_toggle_background'),
      'alertText' => $this->cccConfig
        ->get('civiccookiecontrol_alert_text'),
      'alertBackground' => $this->cccConfig
        ->get('civiccookiecontrol_alert_background'),
      'buttonIcon' => $this->cccConfig
        ->get('civiccookiecontrol_button_icon'),
      'buttonIconWidth' => $this->cccConfig
        ->get('civiccookiecontrol_button_icon_width') . 'px',
      'buttonIconHeight' => $this->cccConfig
        ->get('civiccookiecontrol_button_icon_height') . 'px',
      'removeIcon' => $this->cccConfig
        ->get('civiccookiecontrol_remove_icon') ? TRUE : FALSE,
      'removeAbout' => $this->cccConfig
        ->get('civiccookiecontrol_remove_about_text') ? TRUE : FALSE,
    ];
    foreach ($this->config['branding'] as $key => $item) {
      if (empty($this->config['branding'][$key])) {
        unset($this->config['branding'][$key]);
      }
    }
  }

  /**
   * Function to load necessary cookies list.
   */
  public function loadNecessaryCookieList() {
    $necessaryCookies = $this->entityTypeManager
      ->getStorage('necessarycookie')
      ->loadMultiple();
    $necessaryCookiesRetArray = [];

    foreach ($necessaryCookies as $necCookie) {
      $necessaryCookiesRetArray[] = $necCookie->getNecessaryCookieName();
    }

    return $necessaryCookiesRetArray;
  }

  /**
   * Function to load cookie category entities.
   */
  public function loadCookieCategoryList() {
    $cookieCategories = $this->entityTypeManager
      ->getStorage('cookiecategory')
      ->loadMultiple();
    $cookieCategoriesRetArray = [];
    foreach ($cookieCategories as $cookieCat) {
      $cookieCategory = [];
      $cookieCategory['name'] = $cookieCat->getCookieName();
      $cookieCategory['label'] = $cookieCat->getCookieLabel();
      $cookieCategory['description'] = $cookieCat->getCookieDescription();
      $cookieCategory['cookies'] = explode(',', $cookieCat->getCookies());
      $cookieCategory['onAccept'] = "function(){" . $cookieCat->getOnAcceptCallBack() . "}";
      $cookieCategory['onRevoke'] = "function(){" . $cookieCat->getOnRevokeCallBack() . "}";
      $cookieCategory['recommendedState'] = $cookieCat->getRecommendedState();
      $cookieCategory['lawfulBasis'] = $cookieCat->getlawfulBasis();

      if ((int) $cookieCat->getThirdPartyCookiesCount() > 0) {
        $cookieCategory['thirdPartyCookies'] =
                  Json::decode('[' .
              str_replace(';', ',', stripslashes($cookieCat->getThirdPartyCookies())) .
              ']');
      }

      if ((int) $cookieCat->getVendorsCount() > 0) {
        $cookieCategory['vendors'] =
                  Json::decode('[' .
              str_replace(';', ',', stripslashes($cookieCat->getVendors())) .
              ']');
      }

      $cookieCategoriesRetArray[] = $cookieCategory;
    }
    return $cookieCategoriesRetArray;
  }

  /**
   * Function to load excluded countries list.
   */
  public function loadExcludedCountryList() {
    $excludedCountries = $this->entityTypeManager
      ->getStorage('excludedcountry')
      ->loadMultiple();
    $excludedCountryRetArray = [];

    foreach ($excludedCountries as $exclCountry) {
      $excludedCountryRetArray[] = $exclCountry->getExcludedCountryIsoCode();
    }

    return $excludedCountryRetArray;
  }

  /**
   * Get the cookie control configuration object.
   */
  public function getCccConfigJson() {
    $cid = 'civiccookiecontrol_config';
    $response = &drupal_static(__FUNCTION__);

    if ($cache = $this->cache->get($cid)) {
      $response = $cache->data;
    }
    else {
      $response = (json_encode($this->config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
      $this->cache->set($cid, $response, Cache::PERMANENT, $this->cccConfig->getCacheTags());
    }
    return $response;
  }

}
