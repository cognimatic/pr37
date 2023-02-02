<?php

namespace Drupal\civiccookiecontrol\CCCConfig;

use Drupal\civiccookiecontrol\CCCConfigNames;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Link;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configuration class for cookie control v8 api.
 */
class CCC8Config extends AbstractCCCConfig {

  /**
   * CCC8Config constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   Injected config factory service.
   * @param \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   *   Injected Entity type manager service.
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
    parent::__construct($config, $entityTypeManager, $dateFormatter, $cache);
    $this->iabConfig = $config->get(CCCConfigNames::IAB);
    $this->loadCookieConfig();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return parent::create($container);
  }

  /**
   * Function to construct cookie configuration object.
   */
  public function loadCookieConfig() {
    parent::loadCookieConfig();
    if ($this->iabConfig) {
      $this->config['iabCMP'] = $this->iabConfig
        ->get('iabCMP') ? TRUE : FALSE;
      $this->config['iabConfig']['gdprAppliesGlobally'] = $this->iabConfig
        ->get('iabGdprAppliesGlobally') ? TRUE : FALSE;
      $this->config['iabConfig']['recommendedState'] = Json::decode($this->iabConfig
        ->get('iabRecommendedState'));
    }

    $this->loadStatementObject();
    $this->loadAccessibilityObject();
    $this->loadTextObject();
    $this->loadBrandingObject();

    $this->config['locales'] = $this->loadAltLanguagesList();
  }

  /**
   * Constructs cookie control text object.
   */
  public function loadTextObject() {
    parent::loadTextObject();
    $this->config['text']['accessibilityAlert'] = $this->cccConfig
      ->get('civiccookiecontrol_accessibility_alert');

    if ($this->iabConfig) {
      $this->config['text']['iabCMP'] = $this->iabTextObject();
    }
  }

  /**
   * Get IAB v1.0 configuration object.
   */
  public function iabTextObject() {
    $iabText = [];
    $iabText['label'] = $this->iabConfig->get('iabLabelText');
    $iabText['description'] = $this->iabConfig->get('iabDescriptionText');
    $iabText['configure'] = $this->iabConfig->get('iabConfigureText');
    $iabText['panelTitle'] = $this->iabConfig->get('iabPanelTitleText');
    $iabText['panelIntro'] = $this->iabConfig->get('iabPanelIntroText');
    $iabText['aboutIab'] = $this->iabConfig->get('iabAboutIabText');
    $iabText['iabName'] = $this->iabConfig->get('iabIabNameText');
    $iabText['iabLink'] = $this->iabConfig->get('iabIabLinkText');
    $iabText['panelBack'] = $this->iabConfig->get('iabPanelBackText');
    $iabText['vendorTitle'] = $this->iabConfig->get('iabVendorTitleText');
    $iabText['vendorConfigure'] = $this->iabConfig->get('iabVendorConfigureText');
    $iabText['vendorBack'] = $this->iabConfig->get('iabVendorBackText');
    $iabText['acceptAll'] = $this->iabConfig->get('iabAcceptAllText');
    $iabText['rejectAll'] = $this->iabConfig->get('iabRejectAllText');
    $iabText['back'] = $this->iabConfig->get('iabBackText');

    return $iabText;
  }

  /**
   * Function to load alternative languages list.
   */
  public function loadAltLanguagesList() {
    $altLanguages = $this->entityTypeManager
      ->getStorage('altlanguage')
      ->loadMultiple();
    $locales = [];

    foreach ($altLanguages as $altLang) {
      $locale['locale'] = $altLang->getAltLanguageIsoCode();
      $locale['text']['title'] = $altLang->getAltLanguageTitle();
      $locale['text']['intro'] = $altLang->getAltLanguageIntro();
      $locale['text']['acceptRecommended'] = $altLang->getAltLanguageAcceptRecommended();
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
      $locale['text']['closeLabel'] = $altLang->getAltLanguageCloseLabel();
      $locale['text']['accessibilityAlert'] = $altLang->getAltLanguageAccessibilityAlert();
      $locale['text']['optionalCookies'] = $altLang->getAltLanguageOptionalCookies();
      $locale['text']['statement']['description'] = $altLang->getAltLanguageStmtDescrText();
      $locale['text']['statement']['name'] = $altLang->getAltLanguageStmtNameText();
      if ($nid = $altLang->getAltLanguageStmtUrl()) {
        $privacyNodeUrl = Link::createFromRoute(
              $altLang->getAltLanguageStmtUrl(),
              'entity.node.canonical',
              ['node' => $nid]
          );
        global $base_url;
        $locale['text']['statement']['url'] = $base_url . $privacyNodeUrl->getUrl()
          ->toString();
      }
      $locale['text']['statement']['updated'] = !empty($altLang->getAltLanguageStmtDate()) ?
            $this->dateFormatter->format(strtotime($altLang->getAltLanguageStmtDate()), 'custom', 'd/m/Y') : NULL;

      $locale['text']['iabCMP']['label'] = $altLang->getAltLanguageIabLabelText();
      $locale['text']['iabCMP']['description'] = $altLang->getAltLanguageIabDescriptionText();
      $locale['text']['iabCMP']['configure'] = $altLang->getAltLanguageIabConfigureText();
      $locale['text']['iabCMP']['panelTitle'] = $altLang->getAltLanguageIabPanelTitleText();
      $locale['text']['iabCMP']['panelIntro'] = $altLang->getAltLanguageIabPanelIntroText();
      $locale['text']['iabCMP']['aboutIab'] = $altLang->getAltLanguageIabAboutIabText();
      $locale['text']['iabCMP']['iabName'] = $altLang->getAltLanguageIabIabNameText();
      $locale['text']['iabCMP']['iabLink'] = $altLang->getAltLanguageIabIabLinkText();
      $locale['text']['iabCMP']['panelBack'] = $altLang->getAltLanguageIabPanelBackText();
      $locale['text']['iabCMP']['vendorTitle'] = $altLang->getAltLanguageIabVendorTitleText();
      $locale['text']['iabCMP']['vendorConfigure'] = $altLang->getAltLanguageIabVendorConfigureText();
      $locale['text']['iabCMP']['vendorBack'] = $altLang->getAltLanguageIabVendorBackText();
      $locale['text']['iabCMP']['acceptAll'] = $altLang->getAltLanguageIabAcceptAllText();
      $locale['text']['iabCMP']['rejectAll'] = $altLang->getAltLanguageIabRejectAllText();
      $locale['text']['iabCMP']['back'] = $altLang->getAltLanguageIabBackText();

      $locales[] = $locale;
    }

    return $locales;
  }

}
