<?php

namespace Drupal\civiccookiecontrol\Entity;

use Drupal\civiccookiecontrol\AltLanguageInterface;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the Example entity.
 *
 * @ConfigEntityType(
 *   id = "altlanguage",
 *   label = @Translation("Alternative Language"),
 *   handlers = {
 *     "list_builder" =
 *   "Drupal\civiccookiecontrol\Controller\AltLanguageListBuilder",
 *     "form" = {
 *       "add" = "Drupal\civiccookiecontrol\Form\AltLanguageForm",
 *       "edit" = "Drupal\civiccookiecontrol\Form\AltLanguageForm",
 *       "delete" = "Drupal\civiccookiecontrol\Form\AltLanguageDeleteForm",
 *     }
 *   },
 *   config_prefix = "altlanguage",
 *   admin_permission = "administer civiccookiecontrol",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "altLanguageIsoCode",
 *     "uuid" = "uuid",
 *   },
 *   links = {
 *     "edit-form" =
 *   "/admin/config/system/cookiecontrol/altlanguage/{altlanguage}",
 *     "delete-form" =
 *   "/admin/config/system/cookiecontrol/altlanguage/{altlanguage}/delete",
 *   },
 *   config_export = {
 *     "id",
 *     "altLanguageIsoCode",
 *     "altLanguageTitle",
 *     "altLanguageIntro",
 *     "altLanguageNecessaryTitle",
 *     "altLanguageNecessaryDescription",
 *     "altLanguageOn",
 *     "altLanguageOff",
 *     "altLanguageNotifyTitle",
 *     "altLanguageNotifyDescription",
 *     "altLanguageAccept",
 *     "altLanguageAcceptRecommended",
 *     "altLanguageSettings",
 *     "altLanguageThirdPartyTitle",
 *     "altLanguageThirdPartyDescription",
 *     "altLanguageOptionalCookies",
 *     "altLanguageStmtDescrText",
 *     "altLanguageStmtNameText",
 *     "altLanguageStmtUrl",
 *     "altLanguageStmtDate",
 *     "altLanguageCcpaStmtDescrText",
 *     "altLanguageCcpaStmtNameText",
 *     "altLanguageCcpaStmtRejectButtonText",
 *     "altLanguageCcpaStmtUrl",
 *     "altLanguageCcpaStmtDate",
 *     "altLanguageShowVendors",
 *     "altLanguageThirdPartyCookies",
 *     "altLanguageReadMore",
 *     "altLanguageIabLabelText",
 *     "altLanguageIabDescriptionText",
 *     "altLanguageIabConfigureText",
 *     "altLanguageIabPanelTitleText",
 *     "altLanguageIabPanelIntroText",
 *     "altLanguageIabAboutIabText",
 *     "altLanguageIabIabNameText",
 *     "altLanguageIabIabLinkText",
 *     "altLanguageIabPanelBackText",
 *     "altLanguageIabVendorTitleText",
 *     "altLanguageIabVendorConfigureText",
 *     "altLanguageIabVendorBackText",
 *     "altLanguageIabAcceptAllText",
 *     "altLanguageIabRejectAllText",
 *     "altLanguageIabBackText",
 *     "altLanguageMode",
 *     "altLanguageLocation",
 *     "altLanguageReject",
 *     "altLanguageAcceptSettings",
 *     "altLanguageRejectSettings",
 *     "altLanguageCloseLabel",
 *     "altLanguageAccessibilityAlert",
 *     "altLanguageIabLegalDescription",
 *     "altLanguageIabPanelTitle",
 *     "altLanguageIabPanelIntro1",
 *     "altLanguageIabPanelIntro2",
 *     "altLanguageIabPanelIntro3",
 *     "altLanguageIabAboutIab",
 *     "altLanguageIabName",
 *     "altLanguageIabLink",
 *     "altLanguageIabPurposes",
 *     "altLanguageIabSpecialPurposes",
 *     "altLanguageIabFeatures",
 *     "altLanguageIabSpecialFeatures",
 *     "altLanguageIabDataUse",
 *     "altLanguageIabVendors",
 *     "altLanguageIabOn",
 *     "altLanguageIabOff",
 *     "altLanguageIabPurposeLegitimateInterest",
 *     "altLanguageIabVendorLegitimateInterest",
 *     "altLanguageIabObjectPurposeLegitimateInterest",
 *     "altLanguageIabObjectVendorLegitimateInterest",
 *     "altLanguageIabRelyConsent",
 *     "altLanguageIabRelyLegitimateInterest",
 *     "altLanguageIabSavePreferences",
 *     "altLanguageIabAcceptAll",
 *     "altLanguageIabRejectAll",
 *     "altLanguageIabCookieMaxAge",
 *     "altLanguageIabUsesNonCookieAccessTrue",
 *     "altLanguageIabUsesNonCookieAccessFalse",
 *     "altLanguageIabStorageDisclosures",
 *     "altLanguageIabDisclosureDetailsColumn",
 *     "altLanguageIabDisclosurePurposesColumn",
 *     "altLanguageIabSeconds",
 *     "altLanguageIabMinutes",
 *     "altLanguageIabHours",
 *     "altLanguageIabDays",
 *   }
 * )
 */
class AltLanguage extends ConfigEntityBase implements AltLanguageInterface {

  /**
   * The altlanguage ID.
   *
   * @var string
   */
  public $id;

  /**
   * The Alternative Language ISO Code.
   *
   * @var string
   */
  public $altLanguageIsoCode;

  /**
   * The Alternative Language Title.
   *
   * @var string
   */
  public $altLanguageTitle;

  /**
   * The Alternative Language Intro.
   *
   * @var string
   */
  public $altLanguageIntro;

  /**
   * The Alternative Language Necessary Title.
   *
   * @var string
   */
  public $altLanguageNecessaryTitle;

  /**
   * The Alternative Language Necessary Description.
   *
   * @var string
   */
  public $altLanguageNecessaryDescription;


  /**
   * The Alternative Language On Text.
   *
   * @var string
   */
  public $altLanguageOn;

  /**
   * The Alternative Language Off Text.
   *
   * @var string
   */
  public $altLanguageOff;

  /**
   * The notify title in Alternative Language.
   *
   * @var string
   */
  public $altLanguageNotifyTitle;

  /**
   * The notify Description in Alternative Language.
   *
   * @var string
   */
  public $altLanguageNotifyDescription;

  /**
   * The accept text in Alternative Language.
   *
   * @var string
   */
  public $altLanguageAccept;

  /**
   * The accept Recommended text in Alternative Language.
   *
   * @var string
   */
  public $altLanguageAcceptRecommended;

  /**
   * The settings text in Alternative Language.
   *
   * @var string
   */
  public $altLanguageSettings;

  /**
   * The Third party cookie title in Alternative Language.
   *
   * @var string
   */
  public $altLanguageThirdPartyTitle;

  /**
   * The Third party cookie Description in Alternative Language.
   *
   * @var string
   */
  public $altLanguageThirdPartyDescription;

  /**
   * The oprional cookies label in Alternative Language.
   *
   * @var string
   */
  public $altLanguageOptionalCookies;

  /**
   * The Statement Description text in Alternative Language.
   *
   * @var string
   */
  public $altLanguageStmtDescrText;

  /**
   * The Statement Name text in Alternative Language.
   *
   * @var string
   */
  public $altLanguageStmtNameText;

  /**
   * The Statement URL for Alternative Language.
   *
   * @var string
   */
  public $altLanguageStmtUrl;

  /**
   * The Statement Updated Date for Alternative Language.
   *
   * @var string
   */
  public $altLanguageStmtDate;

  /**
   * The CCPA Statement Description text in Alternative Language.
   *
   * @var string
   */
  public $altLanguageCcpaStmtDescrText;

  /**
   * The CCPA Statement Reject Button text in Alternative Language.
   *
   * @var string
   */
  public $altLanguageCcpaStmtRejectButtonText;

  /**
   * The CCPA Statement Name text in Alternative Language.
   *
   * @var string
   */
  public $altLanguageCcpaStmtNameText;

  /**
   * The CCPA Statement URL for Alternative Language.
   *
   * @var string
   */
  public $altLanguageCcpaStmtUrl;

  /**
   * The CCPA Statement Updated Date for Alternative Language.
   *
   * @var string
   */
  public $altLanguageCcpaStmtDate;

  /**
   * The Show Vendors text in Alternative Language.
   *
   * @var string
   */
  public $altLanguageShowVendors;

  /**
   * The third party cookies text in Alternative Language.
   *
   * @var string
   */

  public $altLanguageThirdPartyCookies;

  /**
   * The read more text in Alternative Language.
   *
   * @var string
   */
  public $altLanguageReadMore;

  /**
   * The iab label text in alternative language.
   *
   * @var string
   */
  public $altLanguageIabLabelText;

  /**
   * The iab description text in alternative language.
   *
   * @var string
   */
  public $altLanguageIabDescriptionText;

  /**
   * The iab configure text in alternative language.
   *
   * @var string
   */
  public $altLanguageIabConfigureText;

  /**
   * The iab panel title text in alternative language.
   *
   * @var string
   */
  public $altLanguageIabPanelTitleText;

  /**
   * The iab panel intro text in alternative language.
   *
   * @var string
   */
  public $altLanguageIabPanelIntroText;

  /**
   * The iab about iab text in alternative language.
   *
   * @var string
   */
  public $altLanguageIabAboutIabText;

  /**
   * The iab name text in alternative language.
   *
   * @var string
   */
  public $altLanguageIabIabNameText;

  /**
   * The iab link text in alternative language.
   *
   * @var string
   */
  public $altLanguageIabIabLinkText;

  /**
   * The iab panel back text in alternative language.
   *
   * @var string
   */
  public $altLanguageIabPanelBackText;

  /**
   * The iab vendor title text in alternative language.
   *
   * @var string
   */
  public $altLanguageIabVendorTitleText;

  /**
   * The iab vendor configure text in alternative language.
   *
   * @var string
   */
  public $altLanguageIabVendorConfigureText;

  /**
   * The iab vendor back text in alternative language.
   *
   * @var string
   */
  public $altLanguageIabVendorBackText;

  /**
   * The iab name text in alternative language.
   *
   * @var string
   */
  public $altLanguageIabAcceptAllText;

  /**
   * The iab reject all text in alternative language.
   *
   * @var string
   */
  public $altLanguageIabRejectAllText;

  /**
   * The iab back text in alternative language.
   *
   * @var string
   */
  public $altLanguageIabBackText;

  /**
   * The alternative language mode.
   *
   * @var string
   */
  public $altLanguageMode;

  /**
   * The location for alternative language.
   *
   * @var string
   */
  public $altLanguageLocation;

  /**
   * Reject text alternative language.
   *
   * @var string
   */
  public $altLanguageReject;

  /**
   * Accept settings in alternative language.
   *
   * @var string
   */
  public $altLanguageAcceptSettings;

  /**
   * Reject settings text in alternative language.
   *
   * @var string
   */
  public $altLanguageRejectSettings;

  /**
   * Close label text in alternative language.
   *
   * @var string
   */
  public $altLanguageCloseLabel;

  /**
   * Accessibility alert text in alternative language.
   *
   * @var string
   */
  public $altLanguageAccessibilityAlert;

  /**
   * Accessibility iab panel title text in alternative language.
   *
   * @var string
   */
  public $altLanguageIabPanelTitle;

  /**
   * Accessibility iab panel intro 1 text in alternative language.
   *
   * @var string
   */
  public $altLanguageIabPanelIntro1;

  /**
   * Accessibility iab panel intro 2 text in alternative language.
   *
   * @var string
   */
  public $altLanguageIabPanelIntro2;

  /**
   * Accessibility iab panel intro 2 text in alternative language.
   *
   * @var string
   */
  public $altLanguageIabPanelIntro3;

  /**
   * Accessibility iab about iab text in alternative language.
   *
   * @var string
   */
  public $altLanguageIabAboutIab;

  /**
   * Accessibility iab about iab text in alternative language.
   *
   * @var string
   */
  public $altLanguageIabName;

  /**
   * Accessibility iab link in alternative language.
   *
   * @var string
   */
  public $altLanguageIabLink;

  /**
   * Accessibility iab purposes in alternative language.
   *
   * @var string
   */
  public $altLanguageIabPurposes;

  /**
   * Accessibility iab special purposes in alternative language.
   *
   * @var string
   */
  public $altLanguageIabSpecialPurposes;

  /**
   * Accessibility iab features in alternative language.
   *
   * @var string
   */
  public $altLanguageIabFeatures;

  /**
   * Accessibility iab special features in alternative language.
   *
   * @var string
   */
  public $altLanguageIabSpecialFeatures;

  /**
   * Accessibility iab data use features in alternative language.
   *
   * @var string
   */
  public $altLanguageIabDataUse;

  /**
   * Accessibility iab vendors use features in alternative language.
   *
   * @var string
   */
  public $altLanguageIabVendors;

  /**
   * Accessibility iab on text in alternative language.
   *
   * @var string
   */
  public $altLanguageIabOn;

  /**
   * Accessibility iab off text in alternative language.
   *
   * @var string
   */
  public $altLanguageIabOff;

  /**
   * Accessibility iab purpose legitimate interest text in alternative language.
   *
   * @var string
   */
  public $altLanguageIabPurposeLegitimateInterest;

  /**
   * Accessibility iab vendor legitimate interest text in alternative language.
   *
   * @var string
   */
  public $altLanguageIabVendorLegitimateInterest;

  /**
   * Accessibility iab object purpose legitimate interest in alt language.
   *
   * @var string
   */
  public $altLanguageIabObjectPurposeLegitimateInterest;

  /**
   * Accessibility iab object vendor legitimate interest in alt language.
   *
   * @var string
   */
  public $altLanguageIabObjectVendorLegitimateInterest;

  /**
   * Accessibility iab rely consent interest in alt language.
   *
   * @var string
   */
  public $altLanguageIabRelyConsent;

  /**
   * Accessibility iab rely legitimate interest in alt language.
   *
   * @var string
   */
  public $altLanguageIabRelyLegitimateInterest;

  /**
   * Accessibility iab save preferences in alt language.
   *
   * @var string
   */
  public $altLanguageIabSavePreferences;

  /**
   * Accessibility accept all alt language.
   *
   * @var string
   */
  public $altLanguageIabAcceptAll;

  /**
   * Accessibility reject all alt language.
   *
   * @var string
   */
  public $altLanguageIabRejectAll;

  /**
   * IAB Legal description in alt language.
   *
   * @var string
   */
  public $altLanguageIabLegalDescription;

  /**
   * IAB Cookie Max Age text in alternative language.
   *
   * @var string
   */
  public $altLanguageIabCookieMaxAge;
  /**
   * IAB Uses non cookie access true text in alternative language.
   *
   * @var string
   */
  public $altLanguageIabUsesNonCookieAccessTrue;
  /**
   * IAB Uses non cookie access false text in alternative language.
   *
   * @var string
   */
  public $altLanguageIabUsesNonCookieAccessFalse;
  /**
   * IAB storage disclosure text in alternative language.
   *
   * @var string
   */
  public $altLanguageIabStorageDisclosures;
  /**
   * IAB storage disclosure details column in alternative language.
   *
   * @var string
   */
  public $altLanguageIabDisclosureDetailsColumn;
  /**
   * IAB storage disclosure purposes column in alternative language.
   *
   * @var string
   */
  public $altLanguageIabDisclosurePurposesColumn;
  /**
   * IAB seconds text in alternative language.
   *
   * @var string
   */
  public $altLanguageIabSeconds;
  /**
   * IAB minutes text in alternative language.
   *
   * @var string
   */
  public $altLanguageIabMinutes;
  /**
   * IAB hours text in alternative language.
   *
   * @var string
   */
  public $altLanguageIabHours;
  /**
   * IAB days text in alternative language.
   *
   * @var string
   */
  public $altLanguageIabDays;

  /**
   * Get the mode of alternative language for cookie control v.9.
   *
   * @return string
   *   Gdpr, ccpa, or hidden
   */
  public function getAltLanguageMode() {
    return $this->altLanguageMode;
  }

  /**
   * Set the mode of alternative language for cookie control v.9.
   *
   * @param string $altLanguageMode
   *   Gdpr, ccpa, or hidden.
   */
  public function setAltLanguageMode($altLanguageMode): void {
    $this->altLanguageMode = $altLanguageMode;
  }

  /**
   * Get the alt language Iso code.
   *
   * @return string
   *   Get the alt language Iso code.
   */
  public function getAltLanguageIsoCode() {
    return $this->altLanguageIsoCode;
  }

  /**
   * Set the alt language Iso code.
   *
   * @param string $altLanguageIsoCode
   *   The alt language Iso code.
   */
  public function setAltLanguageIsoCode($altLanguageIsoCode) {
    $this->altLanguageIsoCode = $altLanguageIsoCode;
  }

  /**
   * {@inheritdoc}
   */
  public function getAltLanguageTitle() {
    return $this->altLanguageTitle;
  }

  /**
   * {@inheritdoc}
   */
  public function setAltLanguageTitle($altLanguageTitle) {
    $this->altLanguageTitle = $altLanguageTitle;

    return $this->altLanguageTitle;
  }

  /**
   * {@inheritdoc}
   */
  public function getAltLanguageNecessaryTitle() {
    return $this->altLanguageNecessaryTitle;
  }

  /**
   * {@inheritdoc}
   */
  public function setAltLanguageNecessaryTitle($altLanguageNecessaryTitle) {
    $this->altLanguageNecessaryTitle = $altLanguageNecessaryTitle;
    return $this->altLanguageNecessaryTitle;
  }

  /**
   * {@inheritdoc}
   */
  public function getAltLanguageNecessaryDescription() {
    return $this->altLanguageNecessaryDescription;
  }

  /**
   * {@inheritdoc}
   */
  public function setAltLanguageNecessaryDescription($altLanguageNecessaryDescription) {
    $this->altLanguageNecessaryDescription = $altLanguageNecessaryDescription;
    return $this->altLanguageNecessaryDescription;
  }

  /**
   * {@inheritdoc}
   */
  public function getAltLanguageThirdPartyTitle() {
    return $this->altLanguageThirdPartyTitle;
  }

  /**
   * {@inheritdoc}
   */
  public function setAltLanguageThirdPartyTitle($altLanguageThirdPartyTitle) {
    $this->altLanguageThirdPartyTitle = $altLanguageThirdPartyTitle;
    return $this->altLanguageThirdPartyTitle;
  }

  /**
   * {@inheritdoc}
   */
  public function getAltLanguageThirdPartyDescription() {
    return $this->altLanguageThirdPartyDescription;
  }

  /**
   * {@inheritdoc}
   */
  public function setAltLanguageThirdPartyDescription($altLanguageThirdPartyDescription) {
    $this->altLanguageThirdPartyDescription = $altLanguageThirdPartyDescription;
    return $this->altLanguageThirdPartyDescription;
  }

  /**
   * {@inheritdoc}
   */
  public function getAltLanguageOptionalCookies() {
    return $this->altLanguageOptionalCookies ? Json::decode($this->altLanguageOptionalCookies) : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function setAltLanguageOptionalCookies($altLanguageOptionalCookies) {
    $this->altLanguageOptionalCookies = $altLanguageOptionalCookies;
    return $this->altLanguageOptionalCookies;
  }

  /**
   * The Intro text in alt language.
   *
   * @return string
   *   Get the Intro text in alt language.
   */
  public function getAltLanguageIntro() {
    return $this->altLanguageIntro;
  }

  /**
   * Set the Intro text in alt language.
   *
   * @param string $altLanguageIntro
   *   The intro text in alt language.
   */
  public function setAltLanguageIntro($altLanguageIntro) {
    $this->altLanguageIntro = $altLanguageIntro;
  }

  /**
   * Returns Statement Description Text in Alternative Language.
   *
   * @return string
   *   Statement Description Text in Alternative Language.
   */
  public function getAltLanguageStmtDescrText() {
    return $this->altLanguageStmtDescrText;
  }

  /**
   * Sets Statement Description Text in Alternative Language.
   *
   * @param string $altLanguageStmtDescrText
   *   Statement Description Text in Alternative Language.
   *
   * @return string
   *   Statement Description Text in Alternative Language.
   */
  public function setAltLanguageStmtDescrText($altLanguageStmtDescrText) {
    $this->altLanguageStmtDescrText = $altLanguageStmtDescrText;

    return $altLanguageStmtDescrText;
  }

  /**
   * Returns Statement Name Text in Alternative Language.
   *
   * @return string
   *   Statement Name Text in Alternative Language.
   */
  public function getAltLanguageStmtNameText() {
    return $this->altLanguageStmtNameText;
  }

  /**
   * Sets Statement Name Text in Alternative Language.
   *
   * @param string $altLanguageStmtNameText
   *   Statement Name Text in Alternative Language.
   *
   * @return string
   *   Statement Name Text in Alternative Language.
   */
  public function setAltLanguageStmtNameText($altLanguageStmtNameText) {
    $this->altLanguageStmtNameText = $altLanguageStmtNameText;

    return $altLanguageStmtNameText;
  }

  /**
   * Returns Statement URL for Alternative Language.
   *
   * @return string
   *   Statement URL in Alternative Language.
   */
  public function getAltLanguageStmtUrl() {
    return $this->altLanguageStmtUrl;
  }

  /**
   * Sets Statement URL in Alternative Language.
   *
   * @param string $altLanguageStmtUrl
   *   Statement URL in Alternative Language.
   *
   * @return string
   *   Statement URL in Alternative Language.
   */
  public function setAltLanguageStmtUrl($altLanguageStmtUrl) {
    $this->altLanguageStmtUrl = $altLanguageStmtUrl;

    return $altLanguageStmtUrl;
  }

  /**
   * Returns Statement Updated Date in Alternative Language.
   *
   * @return string
   *   Statement Updated Date in Alternative Language.
   */
  public function getAltLanguageStmtDate() {
    return $this->altLanguageStmtDate;
  }

  /**
   * Sets Statement Date in Alternative Language.
   *
   * @param string $altLanguageStmtDate
   *   Statement Date for Alternative Language.
   *
   * @return string
   *   Statement Date in Alternative Language.
   */
  public function setAltLanguageStmtDate($altLanguageStmtDate) {
    $this->altLanguageStmtDate = $altLanguageStmtDate;

    return $altLanguageStmtDate;
  }

  /**
   * Returns On Text in Alternative Language.
   *
   * @return string
   *   On Text in Alternative Language.
   */
  public function getAltLanguageOn() {
    return $this->altLanguageOn;
  }

  /**
   * Sets On Text in Alternative Language.
   *
   * @param string $altLanguageOn
   *   On text in Alternative Language.
   *
   * @return string
   *   On text in Alternative Language.
   */
  public function setAltLanguageOn($altLanguageOn) {
    $this->altLanguageOn = $altLanguageOn;
    return $this->altLanguageOn;
  }

  /**
   * Returns Off Text in Alternative Language.
   *
   * @return string
   *   Off Text in Alternative Language.
   */
  public function getAltLanguageOff() {
    return $this->altLanguageOff;
  }

  /**
   * Sets Off Text in Alternative Language.
   *
   * @param string $altLanguageOff
   *   Off text in Alternative Language.
   *
   * @return string
   *   Off text in Alternative Language.
   */
  public function setAltLanguageOff($altLanguageOff) {
    $this->altLanguageOff = $altLanguageOff;
    return $this->altLanguageOff;
  }

  /**
   * Returns Notify Title in Alternative Language.
   *
   * @return string
   *   Notify Title in Alternative Language.
   */
  public function getAltLanguageNotifyTitle() {
    return $this->altLanguageNotifyTitle;
  }

  /**
   * Sets Notify Title in Alternative Language.
   *
   * @param string $altLanguageNotifyTitle
   *   Notify in Alternative Language.
   *
   * @return string
   *   Notify Title in Alternative Language.
   */
  public function setAltLanguageNotifyTitle($altLanguageNotifyTitle) {
    $this->altLanguageNotifyTitle = $altLanguageNotifyTitle;
    return $this->altLanguageNotifyTitle;
  }

  /**
   * Returns Notify Description in Alternative Language.
   *
   * @return string
   *   Notify Description in Alternative Language.
   */
  public function getAltLanguageNotifyDescription() {
    return $this->altLanguageNotifyDescription;
  }

  /**
   * Sets Notify Description in Alternative Language.
   *
   * @param string $altLanguageNotifyDescription
   *   Notify Description in Alternative Language.
   *
   * @return string
   *   Notify Description in Alternative Language.
   */
  public function setAltLanguageNotifyDescription($altLanguageNotifyDescription) {
    $this->altLanguageNotifyDescription = $altLanguageNotifyDescription;
    return $this->altLanguageNotifyDescription;
  }

  /**
   * Returns Accept Text in Alternative Language.
   *
   * @return string
   *   Accept Text in Alternative Language.
   */
  public function getAltLanguageAccept() {
    return $this->altLanguageAccept;
  }

  /**
   * Sets Accept Text in Alternative Language.
   *
   * @param string $altLanguageAccept
   *   Accept Text in Alternative Language.
   *
   * @return string
   *   Accept Text in Alternative Language.
   */
  public function setAltLanguageAccept($altLanguageAccept) {
    $this->altLanguageAccept = $altLanguageAccept;
    return $this->altLanguageAccept;
  }

  /**
   * Returns Settings Text in Alternative Language.
   *
   * @return string
   *   Settings Text in Alternative Language.
   */
  public function getAltLanguageSettings() {
    return $this->altLanguageSettings;
  }

  /**
   * Sets Settings Text in Alternative Language.
   *
   * @param string $altLanguageSettings
   *   Settings Text in Alternative Language.
   *
   * @return string
   *   Settings Text in Alternative Language.
   */
  public function setAltLanguageSettings($altLanguageSettings) {
    $this->altLanguageSettings = $altLanguageSettings;
    return $this->altLanguageSettings;
  }

  /**
   * Returns Accept Recommended Settings Text in Alternative Language.
   *
   * @return string
   *   Accept Recommended Settings  Text in Alternative Language.
   */
  public function getAltLanguageAcceptRecommended() {
    return $this->altLanguageAcceptRecommended;
  }

  /**
   * Sets Accept Recommended Settings Text in Alternative Language.
   *
   * @param string $altLanguageAcceptRecommended
   *   Accept Recommended Settings Text in Alternative Language.
   *
   * @return string
   *   Accept Recommended Settings Text in Alternative Language.
   */
  public function setAltLanguageAcceptRecommended($altLanguageAcceptRecommended) {
    $this->altLanguageAcceptRecommended = $altLanguageAcceptRecommended;
    return $this->altLanguageAcceptRecommended;
  }

  /**
   * Get the reject text in alt language.
   *
   * @return string
   *   The value.
   */
  public function getAltLanguageReject() {
    return $this->altLanguageReject;
  }

  /**
   * Set the reject text in alt language.
   *
   * @param string $altLanguageReject
   *   Input text.
   *
   * @return string
   *   The value.
   */
  public function setAltLanguageReject($altLanguageReject) {
    $this->altLanguageReject = $altLanguageReject;
    return $this->altLanguageReject;
  }

  /**
   * Get the accept settings text in alt language.
   *
   * @return string
   *   The value.
   */
  public function getAltLanguageAcceptSettings() {
    return $this->altLanguageAcceptSettings;
  }

  /**
   * Set the accept settings text in alt language.
   *
   * @param string $altLanguageAcceptSettings
   *   Input text.
   *
   * @return string
   *   The value.
   */
  public function setAltLanguageAcceptSettings($altLanguageAcceptSettings) {
    $this->altLanguageAcceptSettings = $altLanguageAcceptSettings;
    return $this->altLanguageAcceptSettings;
  }

  /**
   * Get the reject settings text in alt language.
   *
   * @return string
   *   The value.
   */
  public function getAltLanguageRejectSettings() {
    return $this->altLanguageRejectSettings;
  }

  /**
   * Set the reject settings text in alt language.
   *
   * @param string $altLanguageRejectSettings
   *   Input text.
   *
   * @return string
   *   The value.
   */
  public function setAltLanguageRejectSettings($altLanguageRejectSettings) {
    $this->altLanguageRejectSettings = $altLanguageRejectSettings;
    return $this->altLanguageRejectSettings;
  }

  /**
   * Get the close lable text in alt language.
   *
   * @return string
   *   The value.
   */
  public function getAltLanguageCloseLabel() {
    return $this->altLanguageCloseLabel;
  }

  /**
   * Set the close label text in alt language.
   *
   * @param string $altLanguageCloseLabel
   *   Input text.
   *
   * @return string
   *   The value.
   */
  public function setAltLanguageCloseLabel($altLanguageCloseLabel) {
    $this->altLanguageCloseLabel = $altLanguageCloseLabel;
    return $this->altLanguageCloseLabel;
  }

  /**
   * Get the accessibility alert text in alt language.
   *
   * @return string
   *   The value.
   */
  public function getAltLanguageAccessibilityAlert() {
    return $this->altLanguageAccessibilityAlert;
  }

  /**
   * Set accessibility alert text in alt language.
   *
   * @param string $altLanguageAccessibilityAlert
   *   Input text.
   *
   * @return string
   *   The value.
   */
  public function setAltLanguageAccessibility($altLanguageAccessibilityAlert) {
    $this->altLanguageAccessibilityAlert = $altLanguageAccessibilityAlert;
    return $this->altLanguageAccessibilityAlert;
  }

  /**
   * Get the iab panel label text in alt language.
   *
   * @return string
   *   The value.
   */
  public function getAltLanguageIabLabelText() {
    return $this->altLanguageIabLabelText;
  }

  /**
   * Set iab label text in alt language.
   *
   * @param string $altLanguageIabLabelText
   *   Input text.
   *
   * @return string
   *   The value.
   */
  public function setAltLanguageIabLabelText($altLanguageIabLabelText) {
    $this->altLanguageIabLabelText = $altLanguageIabLabelText;
    return $this->altLanguageIabLabelText;
  }

  /**
   * Get the iab description text in alt language.
   *
   * @return string
   *   The value.
   */
  public function getAltLanguageIabDescriptionText() {
    return $this->altLanguageIabDescriptionText;
  }

  /**
   * Set iab description text in alt language.
   *
   * @param string $altLanguageIabDescriptionText
   *   Input text.
   *
   * @return string
   *   The value.
   */
  public function setAltLanguageIabDescriptionText($altLanguageIabDescriptionText) {
    $this->altLanguageIabDescriptionText = $altLanguageIabDescriptionText;
    return $this->altLanguageIabDescriptionText;
  }

  /**
   * Get the iab configure text in alt language.
   *
   * @return string
   *   The value.
   */
  public function getAltLanguageIabConfigureText() {
    return $this->altLanguageIabConfigureText;
  }

  /**
   * Set iab configure text in alt language.
   *
   * @param string $altLanguageIabConfigureText
   *   Input text.
   */
  public function setAltLanguageIabConfigureText($altLanguageIabConfigureText) {
    $this->altLanguageIabConfigureText = $altLanguageIabConfigureText;
  }

  /**
   * Get the iab panel title text in alt language.
   *
   * @return string
   *   The value.
   */
  public function getAltLanguageIabPanelTitleText() {
    return $this->altLanguageIabPanelTitleText;
  }

  /**
   * Set iab panel title text in alt language.
   *
   * @param string $altLanguageIabPanelTitleText
   *   Input text.
   */
  public function setAltLanguageIabPanelTitleText($altLanguageIabPanelTitleText) {
    $this->altLanguageIabPanelTitleText = $altLanguageIabPanelTitleText;
  }

  /**
   * Get the iab panel intro text in alt language.
   *
   * @return string
   *   The value.
   */
  public function getAltLanguageIabPanelIntroText() {
    return $this->altLanguageIabPanelIntroText;
  }

  /**
   * Set iab panel intro text in alt language.
   *
   * @param string $altLanguageIabPanelIntroText
   *   Input text.
   */
  public function setAltLanguageIabPanelIntroText($altLanguageIabPanelIntroText) {
    $this->altLanguageIabPanelIntroText = $altLanguageIabPanelIntroText;
  }

  /**
   * Get the iab panel about iab text in alt language.
   *
   * @return string
   *   The value.
   */
  public function getAltLanguageIabAboutIabText() {
    return $this->altLanguageIabAboutIabText;
  }

  /**
   * Set iab about iab text in alt language.
   *
   * @param string $altLanguageIabAboutIabText
   *   Input text.
   */
  public function setAltLanguageIabAboutIabText($altLanguageIabAboutIabText) {
    $this->altLanguageIabAboutIabText = $altLanguageIabAboutIabText;
  }

  /**
   * Get the iab name text in alt language.
   *
   * @return string
   *   The value.
   */
  public function getAltLanguageIabIabNameText() {
    return $this->altLanguageIabIabNameText;
  }

  /**
   * Set iab name text in alt language.
   *
   * @param string $altLanguageIabIabNameText
   *   Input text.
   */
  public function setAltLanguageIabIabNameText($altLanguageIabIabNameText) {
    $this->altLanguageIabIabNameText = $altLanguageIabIabNameText;
  }

  /**
   * Get the iab link text in alt language.
   *
   * @return string
   *   The value.
   */
  public function getAltLanguageIabIabLinkText() {
    return $this->altLanguageIabIabLinkText;
  }

  /**
   * Set iab link text in alt language.
   *
   * @param string $altLanguageIabIabLinkText
   *   Input text.
   */
  public function setAltLanguageIabIabLinkText($altLanguageIabIabLinkText) {
    $this->altLanguageIabIabLinkText = $altLanguageIabIabLinkText;
  }

  /**
   * Get the iab panel back text in alt language.
   *
   * @return string
   *   The value.
   */
  public function getAltLanguageIabPanelBackText() {
    return $this->altLanguageIabPanelBackText;
  }

  /**
   * Set iab panel back text in alt language.
   *
   * @param string $altLanguageIabPanelBackText
   *   Input text.
   */
  public function setAltLanguageIabPanelBackText($altLanguageIabPanelBackText) {
    $this->altLanguageIabPanelBackText = $altLanguageIabPanelBackText;
  }

  /**
   * Get the iab vendor title text in alt language.
   *
   * @return string
   *   The value.
   */
  public function getAltLanguageIabVendorTitleText() {
    return $this->altLanguageIabVendorTitleText;
  }

  /**
   * Set iab vendor title text in alt language.
   *
   * @param string $altLanguageIabVendorTitleText
   *   Input text.
   */
  public function setAltLanguageIabVendorTitleText($altLanguageIabVendorTitleText) {
    $this->altLanguageIabVendorTitleText = $altLanguageIabVendorTitleText;
  }

  /**
   * Get the iab vendor configure text in alt language.
   *
   * @return string
   *   The value.
   */
  public function getAltLanguageIabVendorConfigureText() {
    return $this->altLanguageIabVendorConfigureText;
  }

  /**
   * Set iab vendor configure text in alt language.
   *
   * @param string $altLanguageIabVendorConfigureText
   *   Input text.
   */
  public function setAltLanguageIabVendorConfigureText($altLanguageIabVendorConfigureText) {
    $this->altLanguageIabVendorConfigureText = $altLanguageIabVendorConfigureText;
  }

  /**
   * Get the iab vendor back text in alt language.
   *
   * @return string
   *   The value.
   */
  public function getAltLanguageIabVendorBackText() {
    return $this->altLanguageIabVendorBackText;
  }

  /**
   * Set iab vendor back text in alt language.
   *
   * @param string $altLanguageIabVendorBackText
   *   Input text.
   */
  public function setAltLanguageIabVendorBackText($altLanguageIabVendorBackText) {
    $this->altLanguageIabVendorBackText = $altLanguageIabVendorBackText;
  }

  /**
   * Get the iab accept configure text in alt language.
   *
   * @return string
   *   The value.
   */
  public function getAltLanguageIabAcceptAllText() {
    return $this->altLanguageIabAcceptAllText;
  }

  /**
   * Set iab accept all text in alt language.
   *
   * @param string $altLanguageIabAcceptAllText
   *   Input text.
   */
  public function setAltLanguageIabAcceptAllText($altLanguageIabAcceptAllText) {
    $this->altLanguageIabAcceptAllText = $altLanguageIabAcceptAllText;
  }

  /**
   * Get the iab reject all text in alt language.
   *
   * @return string
   *   The value.
   */
  public function getAltLanguageIabRejectAllText() {
    return $this->altLanguageIabRejectAllText;
  }

  /**
   * Set iab reject all text in alt language.
   *
   * @param string $altLanguageIabRejectAllText
   *   Input text.
   */
  public function setAltLanguageIabRejectAllText($altLanguageIabRejectAllText) {
    $this->altLanguageIabRejectAllText = $altLanguageIabRejectAllText;
  }

  /**
   * Get the iab back text in alt language.
   *
   * @return string
   *   The value.
   */
  public function getAltLanguageIabBackText() {
    return $this->altLanguageIabBackText;
  }

  /**
   * Set iab back text in alt language.
   *
   * @param string $altLanguageIabBackText
   *   Input text.
   */
  public function setAltLanguageIabBackText($altLanguageIabBackText) {
    $this->altLanguageIabBackText = $altLanguageIabBackText;
  }

  /**
   * Get the iab panel title text in alt language.
   *
   * @return string
   *   The value.
   */
  public function getAltLanguageIabPanelTitle() {
    return $this->altLanguageIabPanelTitle;
  }

  /**
   * Set iab panel title in alt language.
   *
   * @param string $altLanguageIabPanelTitle
   *   Input text.
   */
  public function setAltLanguageIabPanelTitle($altLanguageIabPanelTitle): void {
    $this->altLanguageIabPanelTitle = $altLanguageIabPanelTitle;
  }

  /**
   * Get the iab panel intro 1 text in alt language.
   *
   * @return string
   *   The value.
   */
  public function getAltLanguageIabPanelIntro1() {
    return $this->altLanguageIabPanelIntro1;
  }

  /**
   * Set iab panel intro 1 in alt language.
   *
   * @param string $altLanguageIabPanelIntro1
   *   Input text.
   */
  public function setAltLanguageIabPanelIntro1($altLanguageIabPanelIntro1): void {
    $this->altLanguageIabPanelIntro1 = $altLanguageIabPanelIntro1;
  }

  /**
   * Get the iab panel intro 2 text in alt language.
   *
   * @return string
   *   The value.
   */
  public function getAltLanguageIabPanelIntro2() {
    return $this->altLanguageIabPanelIntro2;
  }

  /**
   * Set iab panel intro 2 in alt language.
   *
   * @param string $altLanguageIabPanelIntro2
   *   Input text.
   */
  public function setAltLanguageIabPanelIntro2($altLanguageIabPanelIntro2): void {
    $this->altLanguageIabPanelIntro2 = $altLanguageIabPanelIntro2;
  }

  /**
   * Get the iab panel intro 3 text in alt language.
   *
   * @return string
   *   The value.
   */
  public function getAltLanguageIabPanelIntro3() {
    return $this->altLanguageIabPanelIntro3;
  }

  /**
   * Set iab panel intro 3 in alt language.
   *
   * @param string $altLanguageIabPanelIntro3
   *   Input text.
   */
  public function setAltLanguageIabPanelIntro3($altLanguageIabPanelIntro3): void {
    $this->altLanguageIabPanelIntro3 = $altLanguageIabPanelIntro3;
  }

  /**
   * Get the iab about iab text in alt language.
   *
   * @return string
   *   The value.
   */
  public function getAltLanguageIabAboutIab() {
    return $this->altLanguageIabAboutIab;
  }

  /**
   * Set iab about iab in alt language.
   *
   * @param string $altLanguageIabAboutIab
   *   Input text.
   */
  public function setAltLanguageIabAboutIab($altLanguageIabAboutIab): void {
    $this->altLanguageIabAboutIab = $altLanguageIabAboutIab;
  }

  /**
   * Get the iab name text in alt language.
   *
   * @return string
   *   The value.
   */
  public function getAltLanguageIabName() {
    return $this->altLanguageIabName;
  }

  /**
   * Set iab name in alt language.
   *
   * @param string $altLanguageIabName
   *   Input text.
   */
  public function setAltLanguageIabName($altLanguageIabName): void {
    $this->altLanguageIabName = $altLanguageIabName;
  }

  /**
   * Get the iab name link in alt language.
   *
   * @return string
   *   The value.
   */
  public function getAltLanguageIabLink() {
    return $this->altLanguageIabLink;
  }

  /**
   * Set iab link in alt language.
   *
   * @param string $altLanguageIabLink
   *   Input text.
   */
  public function setAltLanguageIabLink($altLanguageIabLink): void {
    $this->altLanguageIabLink = $altLanguageIabLink;
  }

  /**
   * Get the iab purpose text in alt language.
   *
   * @return string
   *   The value.
   */
  public function getAltLanguageIabPurposes() {
    return $this->altLanguageIabPurposes;
  }

  /**
   * Set iab purposes in alt language.
   *
   * @param string $altLanguageIabPurposes
   *   Input text.
   */
  public function setAltLanguageIabPurposes($altLanguageIabPurposes): void {
    $this->altLanguageIabPurposes = $altLanguageIabPurposes;
  }

  /**
   * Get the iab special purposes text in alt language.
   *
   * @return string
   *   The value.
   */
  public function getAltLanguageIabSpecialPurposes() {
    return $this->altLanguageIabSpecialPurposes;
  }

  /**
   * Set iab special purposes in alt language.
   *
   * @param string $altLanguageIabSpecialPurposes
   *   Input text.
   */
  public function setAltLanguageIabSpecialPurposes($altLanguageIabSpecialPurposes): void {
    $this->altLanguageIabSpecialPurposes = $altLanguageIabSpecialPurposes;
  }

  /**
   * Get the iab features text in alt language.
   *
   * @return string
   *   The value.
   */
  public function getAltLanguageIabFeatures() {
    return $this->altLanguageIabFeatures;
  }

  /**
   * Set iab features in alt language.
   *
   * @param string $altLanguageIabFeatures
   *   Input text.
   */
  public function setAltLanguageIabFeatures($altLanguageIabFeatures): void {
    $this->altLanguageIabFeatures = $altLanguageIabFeatures;
  }

  /**
   * Get the iab special features text in alt language.
   *
   * @return string
   *   The value.
   */
  public function getAltLanguageIabSpecialFeatures() {
    return $this->altLanguageIabSpecialFeatures;
  }

  /**
   * Set iab special features in alt language.
   *
   * @param string $altLanguageIabSpecialFeatures
   *   Input text.
   */
  public function setAltLanguageIabSpecialFeatures($altLanguageIabSpecialFeatures): void {
    $this->altLanguageIabSpecialFeatures = $altLanguageIabSpecialFeatures;
  }

  /**
   * Get the iab data use text in alt language.
   *
   * @return string
   *   The value.
   */
  public function getAltLanguageIabDataUse() {
    return $this->altLanguageIabDataUse;
  }

  /**
   * Set iab data use in alt language.
   *
   * @param string $altLanguageIabDataUse
   *   Input text.
   */
  public function setAltLanguageIabDataUse($altLanguageIabDataUse): void {
    $this->altLanguageIabDataUse = $altLanguageIabDataUse;
  }

  /**
   * Get the iab vendors text in alt language.
   *
   * @return string
   *   The value.
   */
  public function getAltLanguageIabVendors() {
    return $this->altLanguageIabVendors;
  }

  /**
   * Set iab vendors in alt language.
   *
   * @param string $altLanguageIabVendors
   *   Input text.
   */
  public function setAltLanguageIabVendors($altLanguageIabVendors): void {
    $this->altLanguageIabVendors = $altLanguageIabVendors;
  }

  /**
   * Get the iab on text in alt language.
   *
   * @return string
   *   The value.
   */
  public function getAltLanguageIabOn() {
    return $this->altLanguageIabOn;
  }

  /**
   * Set iab vendors use features in alt language.
   *
   * @param string $altLanguageIabOn
   *   Input text.
   */
  public function setAltLanguageIabOn($altLanguageIabOn): void {
    $this->altLanguageIabOn = $altLanguageIabOn;
  }

  /**
   * Get the iab off text in alt language.
   *
   * @return string
   *   The value.
   */
  public function getAltLanguageIabOff() {
    return $this->altLanguageIabOff;
  }

  /**
   * Set iab off in alt language.
   *
   * @param string $altLanguageIabOff
   *   Input text.
   */
  public function setAltLanguageIabOff($altLanguageIabOff): void {
    $this->altLanguageIabOff = $altLanguageIabOff;
  }

  /**
   * Get the iab purpose legitimate interest text in alt language.
   *
   * @return string
   *   The value.
   */
  public function getAltLanguageIabPurposeLegitimateInterest() {
    return $this->altLanguageIabPurposeLegitimateInterest;
  }

  /**
   * Set iab purpose legitimate interest in alt language.
   *
   * @param string $altLanguageIabPurposeLegitimateInterest
   *   Input text.
   */
  public function setAltLanguageIabPurposeLegitimateInterest($altLanguageIabPurposeLegitimateInterest): void {
    $this->altLanguageIabPurposeLegitimateInterest = $altLanguageIabPurposeLegitimateInterest;
  }

  /**
   * Get the iab purpose legitimate interest text in alt language.
   *
   * @return string
   *   The value.
   */
  public function getAltLanguageIabVendorLegitimateInterest() {
    return $this->altLanguageIabVendorLegitimateInterest;
  }

  /**
   * Set iab vendor legitimate interest in alt language.
   *
   * @param string $altLanguageIabVendorLegitimateInterest
   *   Input text.
   */
  public function setAltLanguageIabVendorLegitimateInterest($altLanguageIabVendorLegitimateInterest): void {
    $this->altLanguageIabVendorLegitimateInterest = $altLanguageIabVendorLegitimateInterest;
  }

  /**
   * Get the iab object purpose legitimate interest text in alt language.
   *
   * @return string
   *   The value.
   */
  public function getAltLanguageIabObjectPurposeLegitimateInterest() {
    return $this->altLanguageIabObjectPurposeLegitimateInterest;
  }

  /**
   * Set iab vendor legitimate interest in alt language.
   *
   * @param string $altLanguageIabObjectPurposeLegitimateInterest
   *   Input text.
   */
  public function setAltLanguageIabObjectPurposeLegitimateInterest($altLanguageIabObjectPurposeLegitimateInterest): void {
    $this->altLanguageIabObjectPurposeLegitimateInterest = $altLanguageIabObjectPurposeLegitimateInterest;
  }

  /**
   * Get the iab object purpose vendor legitimate interest text in alt language.
   *
   * @return string
   *   The value.
   */
  public function getAltLanguageIabObjectVendorLegitimateInterest() {
    return $this->altLanguageIabObjectVendorLegitimateInterest;
  }

  /**
   * Set iab object vendor legitimate interest in alt language.
   *
   * @param string $altLanguageIabObjectVendorLegitimateInterest
   *   Input text.
   */
  public function setAltLanguageIabObjectVendorLegitimateInterest($altLanguageIabObjectVendorLegitimateInterest): void {
    $this->altLanguageIabObjectVendorLegitimateInterest = $altLanguageIabObjectVendorLegitimateInterest;
  }

  /**
   * Get the iab rely text in alt language.
   *
   * @return string
   *   The value.
   */
  public function getAltLanguageIabRelyConsent() {
    return $this->altLanguageIabRelyConsent;
  }

  /**
   * Set iab rely consent in alt language.
   *
   * @param string $altLanguageIabRelyConsent
   *   Input text.
   */
  public function setAltLanguageIabRelyConsent($altLanguageIabRelyConsent): void {
    $this->altLanguageIabRelyConsent = $altLanguageIabRelyConsent;
  }

  /**
   * Get the iab rely legitimate interest text in alt language.
   *
   * @return string
   *   The value.
   */
  public function getAltLanguageIabRelyLegitimateInterest() {
    return $this->altLanguageIabRelyLegitimateInterest;
  }

  /**
   * Set iab rely legitimate interest in alt language.
   *
   * @param string $altLanguageIabRelyLegitimateInterest
   *   Input text.
   */
  public function setAltLanguageIabRelyLegitimateInterest($altLanguageIabRelyLegitimateInterest): void {
    $this->altLanguageIabRelyLegitimateInterest = $altLanguageIabRelyLegitimateInterest;
  }

  /**
   * Get the iab save preferences text in alt language.
   *
   * @return string
   *   The value.
   */
  public function getAltLanguageIabSavePreferences() {
    return $this->altLanguageIabSavePreferences;
  }

  /**
   * Set iab save preferences in alt language.
   *
   * @param string $altLanguageIabSavePreferences
   *   Input text.
   */
  public function setAltLanguageIabSavePreferences($altLanguageIabSavePreferences): void {
    $this->altLanguageIabSavePreferences = $altLanguageIabSavePreferences;
  }

  /**
   * Get the iab accept all text in alt language.
   *
   * @return string
   *   The value.
   */
  public function getAltLanguageIabAcceptAll() {
    return $this->altLanguageIabAcceptAll;
  }

  /**
   * Set iab accept all in alt language.
   *
   * @param string $altLanguageIabAcceptAll
   *   Input text.
   */
  public function setAltLanguageIabAcceptAll($altLanguageIabAcceptAll): void {
    $this->altLanguageIabAcceptAll = $altLanguageIabAcceptAll;
  }

  /**
   * Get the iab reject all text in alt language.
   *
   * @return string
   *   The value.
   */
  public function getAltLanguageIabRejectAll() {
    return $this->altLanguageIabRejectAll;
  }

  /**
   * Set iab accept all in alt language.
   *
   * @param string $altLanguageIabRejectAll
   *   Input text.
   */
  public function setAltLanguageIabRejectAll($altLanguageIabRejectAll): void {
    $this->altLanguageIabRejectAll = $altLanguageIabRejectAll;
  }

  /**
   * Get the alt language location.
   *
   * @return string
   *   The value.
   */
  public function getAltLanguageLocation() {
    return $this->altLanguageLocation;
  }

  /**
   * Set alt language location.
   *
   * @param string $altLanguageLocation
   *   Input text.
   */
  public function setAltLanguageLocation($altLanguageLocation): void {
    $this->altLanguageLocation = $altLanguageLocation;
  }

  /**
   * Returns CCPA Statement Description Text in Alternative Language.
   *
   * @return string
   *   CCPA Statement Description Text in Alternative Language.
   */
  public function getAltLanguageCcpaStmtDescrText() {
    return $this->altLanguageCcpaStmtDescrText;
  }

  /**
   * Sets CCPA Statement Description Text in Alternative Language.
   *
   * @param string $altLanguageCcpaStmtDescrText
   *   CCPA Statement Description Text in Alternative Language.
   *
   * @return string
   *   CCPA Statement Description Text in Alternative Language.
   */
  public function setAltLanguageCcpaStmtDescrText($altLanguageCcpaStmtDescrText) {
    $this->altLanguageCcpaStmtDescrText = $altLanguageCcpaStmtDescrText;

    return $altLanguageCcpaStmtDescrText;
  }

  /**
   * Returns CCPA Statement Reject Button Text in Alternative Language.
   *
   * @return string
   *   CCPA Statement Reject Button Text in Alternative Language.
   */
  public function getAltLanguageCcpaStmtRejectButtonText() {
    return $this->altLanguageCcpaStmtRejectButtonText;
  }

  /**
   * Sets CCPA Statement Reject Button Text in Alternative Language.
   *
   * @param string $altLanguageCcpaStmtRejectButtonText
   *   CCPA Statement Reject Button Text in Alternative Language.
   *
   * @return string
   *   CCPA Statement Reject Button Text in Alternative Language.
   */
  public function setAltLanguageCcpaStmtRejectButtonText($altLanguageCcpaStmtRejectButtonText) {
    $this->altLanguageCcpaStmtRejectButtonText = $altLanguageCcpaStmtRejectButtonText;

    return $altLanguageCcpaStmtRejectButtonText;
  }

  /**
   * CCPA Returns Statement Name Text in Alternative Language.
   *
   * @return string
   *   CCPA Statement Name Text in Alternative Language.
   */
  public function getAltLanguageCcpaStmtNameText() {
    return $this->altLanguageCcpaStmtNameText;
  }

  /**
   * CCPA Sets Statement Name Text in Alternative Language.
   *
   * @param string $altLanguageCcpaStmtNameText
   *   CCPA Statement Name Text in Alternative Language.
   *
   * @return string
   *   CCPA Statement Name Text in Alternative Language.
   */
  public function setAltLanguageCcpaStmtNameText($altLanguageCcpaStmtNameText) {
    $this->$altLanguageCcpaStmtNameText = $altLanguageCcpaStmtNameText;

    return $altLanguageCcpaStmtNameText;
  }

  /**
   * Returns CCPA Statement URL for Alternative Language.
   *
   * @return string
   *   CCPA Statement URL in Alternative Language.
   */
  public function getAltLanguageCcpaStmtUrl() {
    return $this->altLanguageCcpaStmtUrl;
  }

  /**
   * Sets CCPA Statement URL in Alternative Language.
   *
   * @param string $altLanguageCcpaStmtUrl
   *   CCPA Statement URL in Alternative Language.
   *
   * @return string
   *   CCPA Statement URL in Alternative Language.
   */
  public function setAltLanguageCcpaStmtUrl($altLanguageCcpaStmtUrl) {
    $this->altLanguageCcpaStmtUrl = $altLanguageCcpaStmtUrl;

    return $altLanguageCcpaStmtUrl;
  }

  /**
   * Returns CCPA Statement Updated Date in Alternative Language.
   *
   * @return string
   *   CCPA Statement Updated Date in Alternative Language.
   */
  public function getAltLanguageCcpaStmtDate() {
    return $this->altLanguageCcpaStmtDate;
  }

  /**
   * Sets CCPA Statement Date in Alternative Language.
   *
   * @param string $altLanguageCcpaStmtDate
   *   CCPA Statement Date for Alternative Language.
   *
   * @return string
   *   CCPA Statement Date in Alternative Language.
   */
  public function setAltLanguageCcpaStmtDate($altLanguageCcpaStmtDate) {
    $this->altLanguageCcpaStmtDate = $altLanguageCcpaStmtDate;

    return $altLanguageCcpaStmtDate;
  }

  /**
   * Returns Show Vendors Text in Alternative Language.
   *
   * @return string
   *   Show Vendors Text in Alternative Language.
   */
  public function getAltLanguageShowVendors(): string {
    return $this->altLanguageShowVendors;
  }

  /**
   * Sets Show Vendors Text in Alternative Language.
   *
   * @param string $altLanguageShowVendors
   *   Show Vendors Text for Alternative Language.
   */
  public function setAltLanguageShowVendors(string $altLanguageShowVendors): void {
    $this->altLanguageShowVendors = $altLanguageShowVendors;
  }

  /**
   * Third Party Cookies Text in Alternative Language.
   *
   * @return string
   *   Third Party Cookies in Alternative Language.
   */
  public function getAltLanguageThirdPartyCookies(): string {
    return $this->altLanguageThirdPartyCookies;
  }

  /**
   * Sets Third Party Cookies Text in Alternative Language.
   *
   * @param string $altLanguageThirdPartyCookies
   *   Third Party Cookies Text for Alternative Language.
   */
  public function setAltLanguageThirdPartyCookies(string $altLanguageThirdPartyCookies): void {
    $this->altLanguageThirdPartyCookies = $altLanguageThirdPartyCookies;
  }

  /**
   * Read More Text in Alternative Language.
   *
   * @return string
   *   Read more text in Alternative Language.
   */
  public function getAltLanguageReadMore(): string {
    return $this->altLanguageReadMore;
  }

  /**
   * Sets Read More Text in Alternative Language.
   *
   * @param string $altLanguageReadMore
   *   Read More Text for Alternative Language.
   */
  public function setAltLanguageReadMore(string $altLanguageReadMore): void {
    $this->altLanguageReadMore = $altLanguageReadMore;
  }

  /**
   * Get IAB Legal description in alt language.
   *
   * @return string
   *   IAB Legal description in alt language.
   */
  public function getAltLanguageIabLegalDescription() {
    return $this->altLanguageIabLegalDescription;
  }

  /**
   * Set IAB Legal description in alt language.
   *
   * @param string $altLanguageIabLegalDescription
   *   IAB Legal description in alt language.
   */
  public function setAltLanguageIabLegalDescription(string $altLanguageIabLegalDescription) {
    $this->altLanguageIabLegalDescription = $altLanguageIabLegalDescription;
  }

  /**
   * Getter for Iab cookie max age in alt language.
   *
   * @return string
   *   The returned string value
   */
  public function getAltLanguageIabCookieMaxAge() {
    return $this->altLanguageIabCookieMaxAge;
  }

  /**
   * Setter for Iab cookie max age in alt language.
   *
   * @param string $altLanguageIabCookieMaxAge
   *   The Iab cookie max age string.
   */
  public function setAltLanguageIabCookieMaxAge($altLanguageIabCookieMaxAge): void {
    $this->altLanguageIabCookieMaxAge = $altLanguageIabCookieMaxAge;
  }

  /**
   * Getter for iab uses non cookie access true.
   *
   * @return string
   *   The returned string value
   */
  public function getAltLanguageIabUsesNonCookieAccessTrue() {
    return $this->altLanguageIabUsesNonCookieAccessTrue;
  }

  /**
   * Setter for iab uses non cookie access true.
   *
   * @param string $altLanguageIabUsesNonCookieAccessTrue
   *   The iab uses non cookie access true text.
   */
  public function setAltLanguageIabUsesNonCookieAccessTrue($altLanguageIabUsesNonCookieAccessTrue): void {
    $this->altLanguageIabUsesNonCookieAccessTrue = $altLanguageIabUsesNonCookieAccessTrue;
  }

  /**
   * Getter for iab uses non cookie access false.
   *
   * @return string
   *   The returned string value
   */
  public function getAltLanguageIabUsesNonCookieAccessFalse() {
    return $this->altLanguageIabUsesNonCookieAccessFalse;
  }

  /**
   * Setter for iab uses non cookie access true.
   *
   * @param string $altLanguageIabUsesNonCookieAccessFalse
   *   The iab uses non cookie access true text.
   */
  public function setAltLanguageIabUsesNonCookieAccessFalse($altLanguageIabUsesNonCookieAccessFalse): void {
    $this->altLanguageIabUsesNonCookieAccessFalse = $altLanguageIabUsesNonCookieAccessFalse;
  }

  /**
   * Getter for iab storage disclosures in alt language.
   *
   * @return string
   *   The returned string value
   */
  public function getAltLanguageIabStorageDisclosures() {
    return $this->altLanguageIabStorageDisclosures;
  }

  /**
   * Setter for iab storage disclosures in alt language.
   *
   * @param mixed $altLanguageIabStorageDisclosures
   *   The iab storage disclosures in alt language text.
   */
  public function setAltLanguageIabStorageDisclosures($altLanguageIabStorageDisclosures): void {
    $this->altLanguageIabStorageDisclosures = $altLanguageIabStorageDisclosures;
  }

  /**
   * Getter for iab storage disclosure details column in alt language.
   *
   * @return string
   *   The returned string value
   */
  public function getAltLanguageIabDisclosureDetailsColumn() {
    return $this->altLanguageIabDisclosureDetailsColumn;
  }

  /**
   * Setter for iab storage disclosure details column in alt language.
   *
   * @param string $altLanguageIabDisclosureDetailsColumn
   *   The iab storage disclosure details column in alt language text.
   */
  public function setAltLanguageIabDisclosureDetailsColumn($altLanguageIabDisclosureDetailsColumn): void {
    $this->altLanguageIabDisclosureDetailsColumn = $altLanguageIabDisclosureDetailsColumn;
  }

  /**
   * Getter for iab storage disclosure purposes column in alt language.
   *
   * @return string
   *   The returned string value
   */
  public function getAltLanguageIabDisclosurePurposesColumn() {
    return $this->altLanguageIabDisclosurePurposesColumn;
  }

  /**
   * Setter for iab storage disclosure details column in alt language.
   *
   * @param string $altLanguageIabDisclosurePurposesColumn
   *   The iab storage disclosure details column in alt language text.
   */
  public function setAltLanguageIabDisclosurePurposesColumn($altLanguageIabDisclosurePurposesColumn): void {
    $this->altLanguageIabDisclosurePurposesColumn = $altLanguageIabDisclosurePurposesColumn;
  }

  /**
   * Getter for iab seconds in alt language.
   *
   * @return string
   *   The returned string value
   */
  public function getAltLanguageIabSeconds() {
    return $this->altLanguageIabSeconds;
  }

  /**
   * Setter for iab seconds in alt language.
   *
   * @param string $altLanguageIabSeconds
   *   The iab seconds in alt language text.
   */
  public function setAltLanguageIabSeconds($altLanguageIabSeconds): void {
    $this->altLanguageIabSeconds = $altLanguageIabSeconds;
  }

  /**
   * Getter for iab minutes in alt language.
   *
   * @return string
   *   The returned string value
   */
  public function getAltLanguageIabMinutes() {
    return $this->altLanguageIabMinutes;
  }

  /**
   * Setter for iab minutes in alt language.
   *
   * @param string $altLanguageIabMinutes
   *   The iab minutes in alt language.
   */
  public function setAltLanguageIabMinutes($altLanguageIabMinutes): void {
    $this->altLanguageIabMinutes = $altLanguageIabMinutes;
  }

  /**
   * Getter for iab hours in alt language.
   *
   * @return string
   *   The returned string value
   */
  public function getAltLanguageIabHours() {
    return $this->altLanguageIabHours;
  }

  /**
   * Setter for iab hours in alt language.
   *
   * @param string $altLanguageIabHours
   *   The iab hours in alt language.
   */
  public function setAltLanguageIabHours($altLanguageIabHours): void {
    $this->altLanguageIabHours = $altLanguageIabHours;
  }

  /**
   * Getter for iab days in alt language.
   *
   * @return string
   *   The returned string value
   */
  public function getAltLanguageIabDays() {
    return $this->altLanguageIabDays;
  }

  /**
   * Setter for iab days in alt language.
   *
   * @param string $altLanguageIabDays
   *   The iab days in alt language.
   */
  public function setAltLanguageIabDays($altLanguageIabDays): void {
    $this->altLanguageIabDays = $altLanguageIabDays;
  }

}
