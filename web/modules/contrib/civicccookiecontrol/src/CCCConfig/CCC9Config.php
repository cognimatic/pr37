<?php

namespace Drupal\civiccookiecontrol\CCCConfig;

use Drupal\civiccookiecontrol\CCCConfigNames;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Link;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configuration class for cookie control v9 api.
 */
class CCC9Config extends AbstractCCCConfig {

  /**
   * CCC9Config constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   Injected config factory service.
   * @param \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   *   Injected Entity type manager service.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $dateFormatter
   *   Injected date formatter service.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Injected cache service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   Injected language manager service.
   */
  public function __construct(
        ConfigFactoryInterface $config,
        EntityTypeManager $entityTypeManager,
        DateFormatterInterface $dateFormatter,
        CacheBackendInterface $cache,
        LanguageManagerInterface $languageManager
    ) {
    parent::__construct($config, $entityTypeManager, $dateFormatter, $cache, $languageManager);
    $this->iabConfig = $config->get(CCCConfigNames::IAB2);
    $this->loadCookieConfig();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    return $instance;
  }

  /**
   * Function to construct cookie configuration object.
   */
  public function loadCookieConfig() {
    parent::loadCookieConfig();
    $this->config['setInnerHTML'] = TRUE;
    $this->config['mode'] = $this->cccConfig
      ->get('civiccookiecontrol_mode');
    $this->config['acceptBehaviour'] = $this->cccConfig
      ->get('civiccookiecontrol_accept_behaviour');
    $this->config['closeOnGlobalChange'] = $this->cccConfig
      ->get('civiccookiecontrol_close_on_global_change');
    $this->config['notifyDismissButton'] = $this->cccConfig
      ->get('civiccookiecontrol_notify_dismiss_button') ? TRUE : FALSE;
    $this->config['sameSiteCookie'] = $this->cccConfig
      ->get('civiccookiecontrol_same_site_cookie') ? TRUE : FALSE;
    if ($this->config['sameSiteCookie']) {
      $this->config['sameSiteValue'] = $this->cccConfig
        ->get('civiccookiecontrol_same_site_value');
    }
    else {
      $this->config['sameSiteValue'] = 'None';
    }

    if ($this->iabConfig) {
      $this->config['iabCMP'] = $this->iabConfig
        ->get('iabCMP') ? TRUE : FALSE;
      if ($this->config['iabCMP']) {
        $this->config['iabConfig']['language'] = $this->iabConfig
          ->get('iabLanguage');
        $this->config['iabConfig']['publisherCC'] = $this->iabConfig
          ->get('iabPublisherCC');
        $this->config['iabConfig']['dropDowns'] = $this->iabConfig
          ->get('iabDropDowns');
        $this->config['iabConfig']['fullLegalDescriptions'] = $this->iabConfig
          ->get('iabFullLegalDescription');
        $this->config['iabConfig']['saveOnlyOnClose'] = $this->iabConfig
          ->get('iabSaveOnlyOnClose');
      }
    }
    $this->loadStatementObject();
    $this->loadAccessibilityObject();
    $this->loadTextObject();
    $this->loadBrandingObject();

    // Get locale mode from configuration.
    $mode = $this->cccConfig->get('civiccookiecontrol_locale_mode');

    if ($mode == 'drupal') {
      $this->config['locales'] = $this->loadAltLanguages($this->getCurrentLanguageId());
    }
    else {
      $this->config['locales'] = $this->loadAltLanguages();
    }
  }

  /**
   * Constructs cookie control text object.
   */
  public function loadTextObject() {
    parent::loadTextObject();
    $this->config['text']['cornerButton'] = $this->cccConfig->get('civiccookiecontrol_corner_button');
    $this->config['text']['landmark'] = $this->cccConfig->get('civiccookiecontrol_landmark');
    if ($this->iabConfig && $this->iabConfig->get('iabCMP')) {
      $this->config['text']['iabCMP'] = $this->iabTextObject();
    }
    $this->config['text']['showVendors'] = $this->cccConfig->get('civiccookiecontrol_show_vendors');
    $this->config['text']['thirdPartyCookies'] = $this->cccConfig->get('civiccookiecontrol_third_party_cookies');
    $this->config['text']['readMore'] = $this->cccConfig->get('civiccookiecontrol_read_more');
  }

  /**
   * {@inheritdoc}
   */
  public function loadStatementObject() {
    $types = ['statement' => '', 'ccpaConfig' => '_ccpa'];
    foreach ($types as $type => $key) {
      $this->config[$type] = [
        'description' => $this->cccConfig
          ->get('civiccookiecontrol' . $key . '_stmt_descr'),
        'name' => $this->cccConfig
          ->get('civiccookiecontrol' . $key . '_stmt_name'),
        'updated' => !empty($this->cccConfig
          ->get('civiccookiecontrol' . $key . '_stmt_date')) ? $this->dateFormatter
          ->format(strtotime($this->cccConfig
            ->get('civiccookiecontrol' . $key . '_stmt_date')), 'custom', 'd/m/Y') : "",
        'rejectButton' => $this->cccConfig
          ->get('civiccookiecontrol' . $key . '_stmt_rejectb'),
      ];
      if ($nid = $this->cccConfig->get('civiccookiecontrol' . $key . '_privacynode')) {
        $privacyNodeUrl = Link::createFromRoute(
              $this->t("Privacy Policy"),
              'entity.node.canonical',
              ['node' => $nid],
              ['absolute' => TRUE]
          );

        $this->config[$type]['url'] = $privacyNodeUrl->getUrl()->toString();
      }

      $this->config[$type] = array_filter($this->config[$type]);
    }
  }

  /**
   * Function to construct accessibility object.
   */
  public function loadAccessibilityObject() {
    parent::loadAccessibilityObject();
    $this->config['accessibility']['overlay'] = $this->cccConfig->get('civiccookiecontrol_overlay');
    $this->config['accessibility']['outline'] = $this->cccConfig->get('civiccookiecontrol_outline');
    $this->config['accessibility']['disableSiteScrolling'] = $this->cccConfig->get('civiccookiecontrol_site_scrolling');
  }

  /**
   * Constructs cookie control branding object.
   */
  public function loadBrandingObject() {
    parent::loadBrandingObject();
    $this->config['branding']['rejectText'] = $this->cccConfig
      ->get('civiccookiecontrol_rejext_text_color');
    $this->config['branding']['rejectBackground'] = $this->cccConfig
      ->get('civiccookiecontrol_reject_background_color');
    $this->config['branding']['closeText'] = $this->cccConfig
      ->get('civiccookiecontrol_close_text');
    $this->config['branding']['closeBackground'] = $this->cccConfig
      ->get('civiccookiecontrol_close_bg_color');
    $this->config['branding']['notifyFontColor'] = $this->cccConfig
      ->get('civiccookiecontrol_notify_font_color');
    $this->config['branding']['notifyBackgroundColor '] = $this->cccConfig
      ->get('civiccookiecontrol_notify_bg_color');
  }

  /**
   * Get IAB v2.0 configuration object.
   */
  public function iabTextObject() {
    $iabText = [];
    $iabText['panelTitle'] = $this->iabConfig->get('iabPanelTitle');
    $iabText['panelIntro1'] = $this->iabConfig->get('iabPanelIntro1');
    $iabText['panelIntro2'] = $this->iabConfig->get('iabPanelIntro2');
    $iabText['panelIntro3'] = $this->iabConfig->get('iabPanelIntro3');
    $iabText['aboutIab'] = $this->iabConfig->get('iabAboutIab');
    $iabText['iabName'] = $this->iabConfig->get('iabName');
    $iabText['iabLink'] = $this->iabConfig->get('iabLink');
    $iabText['acceptAll'] = $this->iabConfig->get('iabAcceptAll');
    $iabText['rejectAll'] = $this->iabConfig->get('iabRejectAll');
    $iabText['purposes'] = $this->iabConfig->get('iabPurposes');
    $iabText['specialPurposes'] = $this->iabConfig->get('iabSpecialPurposes');
    $iabText['features'] = $this->iabConfig->get('iabFeatures');
    $iabText['specialFeatures'] = $this->iabConfig->get('iabSpecialFeatures');
    $iabText['dataUse'] = $this->iabConfig->get('iabDataUse');
    $iabText['vendors'] = $this->iabConfig->get('iabVendors');
    $iabText['purposeLegitimateInterest'] = $this->iabConfig->get('iabPurposeLegitimateInterest');
    $iabText['legalDescription'] = $this->iabConfig->get('iabLegalDescription');
    $iabText['vendorLegitimateInterest'] = $this->iabConfig->get('iabVendorLegitimateInterest');
    $iabText['objectPurposeLegitimateInterest'] = $this->iabConfig->get('iabObjectPurposeLegitimateInterest');
    $iabText['objectVendorLegitimateInterest'] = $this->iabConfig->get('iabObjectVendorLegitimateInterest');
    $iabText['relyConsent'] = $this->iabConfig->get('iabRelyConsent');
    $iabText['relyLegitimateInterest'] = $this->iabConfig->get('iabRelyLegitimateInterest');
    $iabText['savePreferences'] = $this->iabConfig->get('iabSavePreferences');
    $iabText['cookieMaxAge'] = $this->iabConfig->get('iabCookieMaxAge');
    $iabText['usesNonCookieAccessTrue'] = $this->iabConfig->get('iabUsesNonCookieAccessTrue');
    $iabText['usesNonCookieAccessFalse'] = $this->iabConfig->get('iabUsesNonCookieAccessFalse');
    $iabText['storageDisclosures'] = $this->iabConfig->get('iabStorageDisclosures');
    $iabText['disclosureDetailsColumn'] = $this->iabConfig->get('iabDisclosureDetailsColumn');
    $iabText['disclosurePurposesColumn'] = $this->iabConfig->get('iabDisclosurePurposesColumn');
    $iabText['seconds'] = $this->iabConfig->get('iabSeconds');
    $iabText['minutes'] = $this->iabConfig->get('iabMinutes');
    $iabText['hours'] = $this->iabConfig->get('iabHours');
    $iabText['days'] = $this->iabConfig->get('iabDays');

    return $iabText;
  }

  /**
   * Function to load alternative languages.
   *
   * When $lang is not provided ('browser' mode), the returned array will have
   * all alternative languages.
   * When $lang is provided ('drupal' mode), the returned array will have only
   * the reference alt language from alter language configuration. If not found,
   * the returned array will be empty, so the default language will be used.
   *
   * @param null|string $lang
   *   Language code.
   *
   * @return array
   *   The language(s) in array.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function loadAltLanguages($lang = NULL) {

    $altLanguages = $this->entityTypeManager
      ->getStorage('altlanguage')
      ->loadMultiple();
    $locales = [];

    $languageMapping = [];
    foreach ($altLanguages as $key => $value) {
      $languageMapping[$key] = $value->getAltLanguageIsoCode();
    }

    // For locale mode 'drupal', when reference alt language not found, return
    // empty array.
    if ($lang && !in_array($lang, $languageMapping)) {
      return $locales;
    }

    // For locale mode 'drupal', when reference alt language exists, use only
    // this language for the loop.
    $altLanguage = [];
    if ($lang && in_array($lang, $languageMapping)) {
      $langCCId = array_search($lang, $languageMapping);
      $altLanguage[] = $altLanguages[$langCCId];
    }

    $altLanguagesArray = $lang ? $altLanguage : $altLanguages;

    foreach ($altLanguagesArray as $altLang) {
      $locale['locale'] = strtolower(str_replace('-', '_', $altLang->getAltLanguageIsoCode()));
      $locale['mode'] = $altLang->getAltLanguageMode();
      $locale['location'] = $altLang->getAltLanguageLocation();

      $locale['text']['closeLabel'] = $altLang->getAltLanguageCloseLabel();
      $locale['text']['accessibilityAlert'] = $altLang->getAltLanguageAccessibilityAlert();
      $locale['optionalCookies'] = $altLang->getAltLanguageOptionalCookies();
      $locale['text']['title'] = $altLang->getAltLanguageTitle();
      $locale['text']['intro'] = $altLang->getAltLanguageIntro();
      $locale['text']['acceptRecommended'] = $altLang->getAltLanguageAcceptRecommended();
      $locale['text']['acceptSettings'] = $altLang->getAltLanguageAcceptSettings();
      $locale['text']['rejectSettings'] = $altLang->getAltLanguageRejectSettings();
      $locale['text']['necessaryTitle'] = $altLang->getAltLanguageNecessaryTitle();
      $locale['text']['necessaryDescription'] = $altLang->getAltLanguageNecessaryDescription();
      $locale['text']['thirdPartyTitle'] = $altLang->getAltLanguageThirdPartyTitle();
      $locale['text']['thirdPartyDescription'] = $altLang->getAltLanguageThirdPartyDescription();
      $locale['text']['on'] = $altLang->getAltLanguageOn();
      $locale['text']['off'] = $altLang->getAltLanguageOff();
      $locale['text']['notifyTitle'] = $altLang->getAltLanguageNotifyTitle();
      $locale['text']['notifyDescription'] = $altLang->getAltLanguageNotifyDescription();
      $locale['text']['accept'] = $altLang->getAltLanguageAccept();
      $locale['text']['reject'] = $altLang->getAltLanguageReject();
      $locale['text']['settings'] = $altLang->getAltLanguageSettings();
      $locale['text']['showVendors'] = $altLang->getAltLanguageShowVendors();
      $locale['text']['thirdPartyCookies'] = $altLang->getAltLanguageThirdPartyCookies();
      $locale['text']['readMore'] = $altLang->getAltLanguagereadMore();

      $locale['statement']['description'] = $altLang->getAltLanguageStmtDescrText();
      $locale['statement']['name'] = $altLang->getAltLanguageStmtNameText();

      if (($nid = $altLang->getAltLanguageStmtUrl()) && ($locale['mode'] != 'nothing')) {
        $privacyNodeUrl = Link::createFromRoute(
              $altLang->getAltLanguageStmtUrl(),
              'entity.node.canonical',
              ['node' => $nid],
              ['absolute' => TRUE]
          );
        $locale['statement']['url'] = $privacyNodeUrl->getUrl()->toString();
      }
      $locale['statement']['updated'] = !empty($altLang->getAltLanguageStmtDate()) ?
        $this->dateFormatter
          ->format(strtotime($altLang->getAltLanguageStmtDate()), 'custom', 'd/m/Y') : NULL;

      $locale['ccpaConfig']['description'] = $altLang->getAltLanguageCcpaStmtDescrText();
      $locale['ccpaConfig']['name'] = $altLang->getAltLanguageCcpaStmtNameText();
      if ($nid = $altLang->getAltLanguageCcpaStmtUrl() && $locale['mode'] != 'nothing') {
        $privacyNodeUrl = Link::createFromRoute(
              $altLang->getAltLanguageCcpaStmtUrl(),
              'entity.node.canonical',
              ['node' => $nid],
              ['absolute' => TRUE]
          );
        $locale['ccpaConfig']['url'] = $privacyNodeUrl->getUrl()->toString();
      }
      $locale['ccpaConfig']['updated'] = !empty($altLang->getAltLanguageCcpaStmtDate()) ?
        $this->dateFormatter
          ->format(strtotime($altLang->getAltLanguageCcpaStmtDate()), 'custom', 'd/m/Y') : NULL;

      $locale['ccpaConfig']['rejectButton'] = $altLang->getAltLanguageCcpaStmtRejectButtonText();

      if (($this->iabConfig->get('iabCMP') == 1)) {
        $locale['text']['iabCMP']['panelTitle'] = $altLang->getAltLanguageIabPanelTitleText();
        $locale['text']['iabCMP']['panelIntro1'] = $altLang->getAltLanguageIabPanelIntro1();
        $locale['text']['iabCMP']['panelIntro2'] = $altLang->getAltLanguageIabPanelIntro2();
        $locale['text']['iabCMP']['panelIntro3'] = $altLang->getAltLanguageIabPanelIntro3();
        $locale['text']['iabCMP']['aboutIab'] = $altLang->getAltLanguageIabAboutIab();
        $locale['text']['iabCMP']['iabName'] = $altLang->getAltLanguageIabName();
        $locale['text']['iabCMP']['iabLink'] = $altLang->getAltLanguageIabLink();
        $locale['text']['iabCMP']['purposes'] = $altLang->getAltLanguageIabPurposes();
        $locale['text']['iabCMP']['specialPurposes'] = $altLang->getAltLanguageIabSpecialPurposes();
        $locale['text']['iabCMP']['features'] = $altLang->getAltLanguageIabFeatures();
        $locale['text']['iabCMP']['specialFeatures'] = $altLang->getAltLanguageIabSpecialFeatures();
        $locale['text']['iabCMP']['dataUse'] = $altLang->getAltLanguageIabDataUse();
        $locale['text']['iabCMP']['vendors'] = $altLang->getAltLanguageIabVendors();
        $locale['text']['on'] = $altLang->getAltLanguageIabOn();
        $locale['text']['off'] = $altLang->getAltLanguageIabOff();
        $locale['text']['iabCMP']['purposeLegitimateInterest'] =
          $altLang->getAltLanguageIabPurposeLegitimateInterest();
        $locale['text']['iabCMP']['vendorLegitimateInterest'] =
          $altLang->getAltLanguageIabVendorLegitimateInterest();
        $locale['text']['iabCMP']['objectPurposeLegitimateInterest'] =
          $altLang->getAltLanguageIabObjectPurposeLegitimateInterest();
        $locale['text']['iabCMP']['objectVendorLegitimateInterest'] =
          $altLang->getAltLanguageIabObjectVendorLegitimateInterest();
        $locale['text']['iabCMP']['relyConsent'] = $altLang->getAltLanguageIabRelyConsent();
        $locale['text']['iabCMP']['relyLegitimateInterest'] =
          $altLang->getAltLanguageIabRelyLegitimateInterest();
        $locale['text']['iabCMP']['savePreferences'] = $altLang->getAltLanguageIabSavePreferences();
        $locale['text']['iabCMP']['acceptAll'] = $altLang->getAltLanguageIabAcceptAll();
        $locale['text']['iabCMP']['rejectAll'] = $altLang->getAltLanguageIabRejectAll();
        $locale['text']['iabCMP']['legalDescription'] = $altLang->getAltLanguageIabLegalDescription();
        $locale['text']['iabCMP']['cookieMaxAge'] = $altLang->getAltLanguageIabCookieMaxAge();
        $locale['text']['iabCMP']['usesNonCookieAccessTrue'] =
          $altLang->getAltLanguageIabUsesNonCookieAccessTrue();
        $locale['text']['iabCMP']['usesNonCookieAccessFalse'] =
          $altLang->getAltLanguageIabUsesNonCookieAccessFalse();
        $locale['text']['iabCMP']['storageDisclosures'] = $altLang->getAltLanguageIabStorageDisclosures();
        $locale['text']['iabCMP']['disclosureDetailsColumn'] =
          $altLang->getAltLanguageIabDisclosureDetailsColumn();
        $locale['text']['iabCMP']['disclosurePurposesColumn'] =
          $altLang->getAltLanguageIabDisclosurePurposesColumn();
        $locale['text']['iabCMP']['seconds'] = $altLang->getAltLanguageIabSeconds();
        $locale['text']['iabCMP']['minutes'] = $altLang->getAltLanguageIabMinutes();
        $locale['text']['iabCMP']['hours'] = $altLang->getAltLanguageIabHours();
        $locale['text']['iabCMP']['days'] = $altLang->getAltLanguageIabDays();
      }
      $locales[] = $locale;
    }

    return $locales;
  }

  /**
   * Get the cookie control configuration object.
   */
  public function getCccConfigJson() {
    $lang = $this->getCurrentLanguageId();
    $cid = 'civiccookiecontrol_config_' . $lang;
    $response = &drupal_static(__FUNCTION__);

    if ($cache = $this->cache->get($cid)) {
      $response = $cache->data;
    }
    else {
      if ($this->cccConfig->get('civiccookiecontrol_locale_mode') == 'drupal') {
        $this->config['locale'] = $lang;
      }
      $response = (json_encode($this->config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
      $this->cache->set($cid, $response, Cache::PERMANENT, $this->cccConfig->getCacheTags());
    }
    return $response;
  }

}
