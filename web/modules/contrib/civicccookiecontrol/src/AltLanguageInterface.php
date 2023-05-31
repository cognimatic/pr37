<?php

namespace Drupal\civiccookiecontrol;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * The alternative language interface.
 */
interface AltLanguageInterface extends ConfigEntityInterface {

  /**
   * Returns Alternative Language ISO Code.
   *
   * @return string
   *   The Alternative Language ISO Code.
   */
  public function getAltLanguageIsoCode();

  /**
   * Set Alternative Language ISO Code.
   *
   * @param string $altLanguageIsoCode
   *   Alternative Language ISO Code.
   *
   * @return string
   *   Alternative Language ISO Code.
   */
  public function setAltLanguageIsoCode($altLanguageIsoCode);

  /**
   * Returns Cookie Title in Alternative Language.
   *
   * @return string
   *   Cookie Title in Alternative Language.
   */
  public function getAltLanguageTitle();

  /**
   * Returns Cookie Intro in Alternative Language.
   *
   * @return string
   *   Cookie Intro in Alternative Language.
   */
  public function getAltLanguageIntro();

  /**
   * Sets Cookie Intro in Alternative Language.
   *
   * @param string $altLanguageIntro
   *   Cookie Intro in Alternative Language.
   *
   * @return string
   *   Cookie Intro in Alternative Language.
   */
  public function setAltLanguageIntro($altLanguageIntro);

  /**
   * Sets Title in Alternative Language.
   *
   * @param string $altLanguageTitle
   *   Title in Alternative Language.
   *
   * @return string
   *   Title in Alternative Language.
   */
  public function setAltLanguageTitle($altLanguageTitle);

  /**
   * Returns Cookie Necessary Title in Alternative Language.
   *
   * @return string
   *   Cookie Necessary Title in Alternative Language
   */
  public function getAltLanguageNecessaryTitle();

  /**
   * Sets Necessary Title in Alternative Language.
   *
   * @param string $altLanguageNecessaryTitle
   *   Cookie Necessary Title in Alternative Language.
   *
   * @return string
   *   Cookie Necessary Title in Alternative Language
   */
  public function setAltLanguageNecessaryTitle($altLanguageNecessaryTitle);

  /**
   * Returns Necessary Cookie Description in Alternative Language.
   *
   * @return string
   *   Necessary Cookie Description in Alternative Language.
   */
  public function getAltLanguageNecessaryDescription();

  /**
   * Sets Description Necessary in Alternative Language.
   *
   * @param string $altLanguageNecessaryDescription
   *   Necessary Cookie Description in Alternative Language.
   *
   * @return string
   *   Necessary Cookie Description in Alternative Language.
   */
  public function setAltLanguageNecessaryDescription($altLanguageNecessaryDescription);

  /**
   * Returns On Text in Alternative Language.
   *
   * @return string
   *   On Text in Alternative Language.
   */
  public function getAltLanguageOn();

  /**
   * Sets On Text in Alternative Language.
   *
   * @param string $altLanguageOn
   *   On text in Alternative Language.
   *
   * @return string
   *   On text in Alternative Language.
   */
  public function setAltLanguageOn($altLanguageOn);

  /**
   * Returns Off Text in Alternative Language.
   *
   * @return string
   *   Off Text in Alternative Language.
   */
  public function getAltLanguageOff();

  /**
   * Sets Off Text in Alternative Language.
   *
   * @param string $altLanguageOff
   *   Off text in Alternative Language.
   *
   * @return string
   *   Off text in Alternative Language.
   */
  public function setAltLanguageOff($altLanguageOff);

  /**
   * Returns Notify Title in Alternative Language.
   *
   * @return string
   *   Notify Title in Alternative Language.
   */
  public function getAltLanguageNotifyTitle();

  /**
   * Sets Notify Title in Alternative Language.
   *
   * @param string $altLanguageNotifyTitle
   *   Notify in Alternative Language.
   *
   * @return string
   *   Notify Title in Alternative Language.
   */
  public function setAltLanguageNotifyTitle($altLanguageNotifyTitle);

  /**
   * Returns Notify Description in Alternative Language.
   *
   * @return string
   *   Notify Description in Alternative Language.
   */
  public function getAltLanguageNotifyDescription();

  /**
   * Sets Notify Description in Alternative Language.
   *
   * @param string $altLanguageNotifyDescription
   *   Notify Description in Alternative Language.
   *
   * @return string
   *   Notify Description in Alternative Language.
   */
  public function setAltLanguageNotifyDescription($altLanguageNotifyDescription);

  /**
   * Returns Accept Text in Alternative Language.
   *
   * @return string
   *   Accept Text in Alternative Language.
   */
  public function getAltLanguageAccept();

  /**
   * Sets Accept Text in Alternative Language.
   *
   * @param string $altLanguageAccept
   *   Accept Text in Alternative Language.
   *
   * @return string
   *   Accept Text in Alternative Language.
   */
  public function setAltLanguageAccept($altLanguageAccept);

  /**
   * Returns Reject Text in Alternative Language.
   *
   * @return string
   *   Reject Text in Alternative Language.
   */
  public function getAltLanguageReject();

  /**
   * Sets Reject Text in Alternative Language.
   *
   * @param string $altLanguageReject
   *   Reject Text in Alternative Language.
   *
   * @return string
   *   Reject Text in Alternative Language.
   */
  public function setAltLanguageReject($altLanguageReject);

  /**
   * Returns Accept Recommended Settings Text in Alternative Language.
   *
   * @return string
   *   Accept Recommended Settings  Text in Alternative Language.
   */
  public function getAltLanguageAcceptRecommended();

  /**
   * Sets Accept Recommended Settings Text in Alternative Language.
   *
   * @param string $altLanguageAcceptRecommended
   *   Accept Recommended Settings Text in Alternative Language.
   *
   * @return string
   *   Accept Recommended Settings Text in Alternative Language.
   */
  public function setAltLanguageAcceptRecommended($altLanguageAcceptRecommended);

  /**
   * Returns Settings Text in Alternative Language.
   *
   * @return string
   *   Settings Text in Alternative Language.
   */
  public function getAltLanguageSettings();

  /**
   * Sets Settings Text in Alternative Language.
   *
   * @param string $altLanguageSettings
   *   Settings Text in Alternative Language.
   *
   * @return string
   *   Settings Text in Alternative Language.
   */
  public function setAltLanguageSettings($altLanguageSettings);

  /**
   * Returns Accept Settings Text in Alternative Language.
   *
   * @return string
   *   Accept settings Text in Alternative Language.
   */
  public function getAltLanguageAcceptSettings();

  /**
   * Sets Accept Settings Text in Alternative Language.
   *
   * @param string $altLanguageAcceptSettings
   *   Accpet Settings Text in Alternative Language.
   *
   * @return string
   *   Accept Settings Text in Alternative Language.
   */
  public function setAltLanguageAcceptSettings($altLanguageAcceptSettings);

  /**
   * Returns Reject Settings Text in Alternative Language.
   *
   * @return string
   *   Reject settings Text in Alternative Language.
   */
  public function getAltLanguageRejectSettings();

  /**
   * Sets Reject Settings Text in Alternative Language.
   *
   * @param string $altLanguageRejectSettings
   *   Reject Settings Text in Alternative Language.
   *
   * @return string
   *   Reject Settings Text in Alternative Language.
   */
  public function setAltLanguageRejectSettings($altLanguageRejectSettings);

  /**
   * Returns Third Party Title in Alternative Language.
   *
   * @return string
   *   Third Party Title in Alternative Language.
   */
  public function getAltLanguageThirdPartyTitle();

  /**
   * Sets Third Party Title in Alternative Language.
   *
   * @param string $altLanguageThirdPartyTitle
   *   Third Party Title in Alternative Language.
   *
   * @return string
   *   Third Party Title in Alternative Language.
   */
  public function setAltLanguageThirdPartyTitle($altLanguageThirdPartyTitle);

  /**
   * Returns Third Party Description in Alternative Language.
   *
   * @return string
   *   Third Party Description in Alternative Language.
   */
  public function getAltLanguageThirdPartyDescription();

  /**
   * Sets Third Party Description in Alternative Language.
   *
   * @param string $altLanguageThirdPartyDescription
   *   Third Party Description in Alternative Language.
   *
   * @return string
   *   Third Party Description in Alternative Language.
   */
  public function setAltLanguageThirdPartyDescription($altLanguageThirdPartyDescription);

  /**
   * Returns Optional Cookies in Alternative Language.
   *
   * @return string
   *   Optional Cookies in Alternative Language.
   */
  public function getAltLanguageOptionalCookies();

  /**
   * Sets Optional Cookies Label in Alternative Language.
   *
   * @param string $altLanguageOptionalCookies
   *   Optional Cookies in Alternative Language.
   *
   * @return string
   *   Optional Cookies in Alternative Language.
   */
  public function setAltLanguageOptionalCookies($altLanguageOptionalCookies);

  /**
   * Returns Statement Description Text in Alternative Language.
   *
   * @return string
   *   Statement Description Text in Alternative Language.
   */
  public function getAltLanguageStmtDescrText();

  /**
   * Sets Statement Description Text in Alternative Language.
   *
   * @param string $altLanguageStmtDescrText
   *   Statement Description Text in Alternative Language.
   *
   * @return string
   *   Statement Description Text in Alternative Language.
   */
  public function setAltLanguageStmtDescrText($altLanguageStmtDescrText);

  /**
   * Returns Statement Name Text in Alternative Language.
   *
   * @return string
   *   Statement Name Text in Alternative Language.
   */
  public function getAltLanguageStmtNameText();

  /**
   * Sets Statement Name Text in Alternative Language.
   *
   * @param string $altLanguageStmtNameText
   *   Statement Name Text in Alternative Language.
   *
   * @return string
   *   Statement Name Text in Alternative Language.
   */
  public function setAltLanguageStmtNameText($altLanguageStmtNameText);

  /**
   * Returns Statement URL for Alternative Language.
   *
   * @return string
   *   Statement URL in Alternative Language.
   */
  public function getAltLanguageStmtUrl();

  /**
   * Sets Statement URL in Alternative Language.
   *
   * @param string $altLanguageStmtUrl
   *   Statement URL in Alternative Language.
   *
   * @return string
   *   Statement URL in Alternative Language.
   */
  public function setAltLanguageStmtUrl($altLanguageStmtUrl);

  /**
   * Returns Statement Updated Date in Alternative Language.
   *
   * @return string
   *   Statement Updated Date in Alternative Language.
   */
  public function getAltLanguageStmtDate();

  /**
   * Sets Statement Date in Alternative Language.
   *
   * @param string $altLanguageStmtDate
   *   Statement Date for Alternative Language.
   *
   * @return string
   *   Statement Date in Alternative Language.
   */
  public function setAltLanguageStmtDate($altLanguageStmtDate);

  /**
   * Returns CCPA Statement Description Text in Alternative Language.
   *
   * @return string
   *   CCPA Statement Description Text in Alternative Language.
   */
  public function getAltLanguageCcpaStmtDescrText();

  /**
   * Sets CCPA Statement Description Text in Alternative Language.
   *
   * @param string $altLanguageCcpaStmtDescrText
   *   CCPA Statement Description Text in Alternative Language.
   *
   * @return string
   *   CCPA Statement Description Text in Alternative Language.
   */
  public function setAltLanguageCcpaStmtDescrText($altLanguageCcpaStmtDescrText);

  /**
   * Returns CCPA Statement Reject Button Text in Alternative Language.
   *
   * @return string
   *   CCPA Statement Reject Button Text in Alternative Language.
   */
  public function getAltLanguageCcpaStmtRejectButtonText();

  /**
   * Sets CCPA Statement Reject Button Text in Alternative Language.
   *
   * @param string $altLanguageCcpaStmtRejectButtonText
   *   CCPA Statement Reject Button Text in Alternative Language.
   *
   * @return string
   *   CCPA Statement Reject Button Text in Alternative Language.
   */
  public function setAltLanguageCcpaStmtRejectButtonText($altLanguageCcpaStmtRejectButtonText);

  /**
   * CCPA Returns Statement Name Text in Alternative Language.
   *
   * @return string
   *   CCPA Statement Name Text in Alternative Language.
   */
  public function getAltLanguageCcpaStmtNameText();

  /**
   * CCPA Sets Statement Name Text in Alternative Language.
   *
   * @param string $altLanguageCcpaStmtNameText
   *   CCPA Statement Name Text in Alternative Language.
   *
   * @return string
   *   CCPA Statement Name Text in Alternative Language.
   */
  public function setAltLanguageCcpaStmtNameText($altLanguageCcpaStmtNameText);

  /**
   * Returns CCPA Statement URL for Alternative Language.
   *
   * @return string
   *   CCPA Statement URL in Alternative Language.
   */
  public function getAltLanguageCcpaStmtUrl();

  /**
   * Sets CCPA Statement URL in Alternative Language.
   *
   * @param string $altLanguageCcpaStmtUrl
   *   CCPA Statement URL in Alternative Language.
   *
   * @return string
   *   CCPA Statement URL in Alternative Language.
   */
  public function setAltLanguageCcpaStmtUrl($altLanguageCcpaStmtUrl);

  /**
   * Returns CCPA Statement Updated Date in Alternative Language.
   *
   * @return string
   *   CCPA Statement Updated Date in Alternative Language.
   */
  public function getAltLanguageCcpaStmtDate();

  /**
   * Sets CCPA Statement Date in Alternative Language.
   *
   * @param string $altLanguageCcpaStmtDate
   *   CCPA Statement Date for Alternative Language.
   *
   * @return string
   *   CCPA Statement Date in Alternative Language.
   */
  public function setAltLanguageCcpaStmtDate($altLanguageCcpaStmtDate);

  /**
   * Returns Close Label text in Alternative Language.
   *
   * @return string
   *   Close Label text in Alternative Language.
   */
  public function getAltLanguageCloseLabel();

  /**
   * Sets Close Label text in Alternative Language.
   *
   * @param string $altLanguageCloseLabel
   *   Close Label text in Alternative Language.
   *
   * @return string
   *   Close Label text in Alternative Language.
   */
  public function setAltLanguageCloseLabel($altLanguageCloseLabel);

  /**
   * Returns Accessibility Alert in Alternative Language.
   *
   * @return string
   *   Accessibility Alert in Alternative Language.
   */
  public function getAltLanguageAccessibilityAlert();

  /**
   * Sets Accessibility Alert in Alternative Language.
   *
   * @param string $altLanguageAccessibilityAlert
   *   Accessibility Alert in Alternative Language.
   *
   * @return string
   *   Accessibility Alert in Alternative Language.
   */
  public function setAltLanguageAccessibility($altLanguageAccessibilityAlert);

  /**
   * Get the iab panel label text in alt language.
   *
   * @return string
   *   The value.
   */
  public function getAltLanguageIabLabelText();

  /**
   * Set iab label text in alt language.
   *
   * @param string $altLanguageIabLabelText
   *   Input text.
   *
   * @return string
   *   The value.
   */
  public function setAltLanguageIabLabelText($altLanguageIabLabelText);

  /**
   * Get the iab description text in alt language.
   *
   * @return string
   *   The value.
   */
  public function getAltLanguageIabDescriptionText();

  /**
   * Set iab description text in alt language.
   *
   * @param string $altLanguageIabDescriptionText
   *   Input text.
   *
   * @return string
   *   The value.
   */
  public function setAltLanguageIabDescriptionText($altLanguageIabDescriptionText);

  /**
   * Get the iab configure text in alt language.
   *
   * @return string
   *   The value.
   */
  public function getAltLanguageIabConfigureText();

  /**
   * Set iab configure text in alt language.
   *
   * @param string $altLanguageIabConfigureText
   *   Input text.
   */
  public function setAltLanguageIabConfigureText($altLanguageIabConfigureText);

  /**
   * Get the iab panel title text in alt language.
   *
   * @return string
   *   The value.
   */
  public function getAltLanguageIabPanelTitleText();

  /**
   * Set iab panel title text in alt language.
   *
   * @param string $altLanguageIabPanelTitleText
   *   Input text.
   */
  public function setAltLanguageIabPanelTitleText($altLanguageIabPanelTitleText);

  /**
   * Get the iab panel intro text in alt language.
   *
   * @return string
   *   The value.
   */
  public function getAltLanguageIabPanelIntroText();

  /**
   * Set iab panel intro text in alt language.
   *
   * @param string $altLanguageIabPanelIntroText
   *   Input text.
   */
  public function setAltLanguageIabPanelIntroText($altLanguageIabPanelIntroText);

  /**
   * Get the iab panel about iab text in alt language.
   *
   * @return string
   *   The value.
   */
  public function getAltLanguageIabAboutIabText();

  /**
   * Set iab about iab text in alt language.
   *
   * @param string $altLanguageIabAboutIabText
   *   Input text.
   */
  public function setAltLanguageIabAboutIabText($altLanguageIabAboutIabText);

  /**
   * Get the iab name text in alt language.
   *
   * @return string
   *   The value.
   */
  public function getAltLanguageIabIabNameText();

  /**
   * Set iab name text in alt language.
   *
   * @param string $altLanguageIabIabNameText
   *   Input text.
   */
  public function setAltLanguageIabIabNameText($altLanguageIabIabNameText);

  /**
   * Get the iab link text in alt language.
   *
   * @return string
   *   The value.
   */
  public function getAltLanguageIabIabLinkText();

  /**
   * Set iab link text in alt language.
   *
   * @param string $altLanguageIabIabLinkText
   *   Input text.
   */
  public function setAltLanguageIabIabLinkText($altLanguageIabIabLinkText);

  /**
   * Get the iab panel back text in alt language.
   *
   * @return string
   *   The value.
   */
  public function getAltLanguageIabPanelBackText();

  /**
   * Set iab panel back text in alt language.
   *
   * @param string $altLanguageIabPanelBackText
   *   Input text.
   */
  public function setAltLanguageIabPanelBackText($altLanguageIabPanelBackText);

  /**
   * Get the iab vendor title text in alt language.
   *
   * @return string
   *   The value.
   */
  public function getAltLanguageIabVendorTitleText();

  /**
   * Set iab vendor title text in alt language.
   *
   * @param string $altLanguageIabVendorTitleText
   *   Input text.
   */
  public function setAltLanguageIabVendorTitleText($altLanguageIabVendorTitleText);

  /**
   * Get the iab vendor configure text in alt language.
   *
   * @return string
   *   The value.
   */
  public function getAltLanguageIabVendorConfigureText();

  /**
   * Set iab vendor configure text in alt language.
   *
   * @param string $altLanguageIabVendorConfigureText
   *   Input text.
   */
  public function setAltLanguageIabVendorConfigureText($altLanguageIabVendorConfigureText);

  /**
   * Get the iab vendor back text in alt language.
   *
   * @return string
   *   The value.
   */
  public function getAltLanguageIabVendorBackText();

  /**
   * Set iab vendor back text in alt language.
   *
   * @param string $altLanguageIabVendorBackText
   *   Input text.
   */
  public function setAltLanguageIabVendorBackText($altLanguageIabVendorBackText);

  /**
   * Get the iab accept configure text in alt language.
   *
   * @return string
   *   The value.
   */
  public function getAltLanguageIabAcceptAllText();

  /**
   * Set iab accept all text in alt language.
   *
   * @param string $altLanguageIabAcceptAllText
   *   Input text.
   */
  public function setAltLanguageIabAcceptAllText($altLanguageIabAcceptAllText);

  /**
   * Get the iab reject all text in alt language.
   *
   * @return string
   *   The value.
   */
  public function getAltLanguageIabRejectAllText();

  /**
   * Set iab reject all text in alt language.
   *
   * @param string $altLanguageIabRejectAllText
   *   Input text.
   */
  public function setAltLanguageIabRejectAllText($altLanguageIabRejectAllText);

  /**
   * Get the iab back text in alt language.
   *
   * @return string
   *   The value.
   */
  public function getAltLanguageIabBackText();

  /**
   * Set iab back text in alt language.
   *
   * @param string $altLanguageIabBackText
   *   Input text.
   */
  public function setAltLanguageIabBackText($altLanguageIabBackText);

  /**
   * Returns Show Vendors Text in Alternative Language.
   *
   * @return string
   *   Show Vendors Text in Alternative Language.
   */
  public function getAltLanguageShowVendors();

  /**
   * Sets Show Vendors Text in Alternative Language.
   *
   * @param string $altLanguageShowVendors
   *   Show Vendors Text for Alternative Language.
   */
  public function setAltLanguageShowVendors(string $altLanguageShowVendors);

  /**
   * Third Party Cookies Text in Alternative Language.
   *
   * @return string
   *   Third Party Cookies in Alternative Language.
   */
  public function getAltLanguageThirdPartyCookies();

  /**
   * Sets Third Party Cookies Text in Alternative Language.
   *
   * @param string $altLanguageThirdPartyCookies
   *   Third Party Cookies Text for Alternative Language.
   */
  public function setAltLanguageThirdPartyCookies(string $altLanguageThirdPartyCookies);

  /**
   * Read More Text in Alternative Language.
   *
   * @return string
   *   Read more text in Alternative Language.
   */
  public function getAltLanguageReadMore();

  /**
   * Sets Read More Text in Alternative Language.
   *
   * @param string $altLanguageReadMore
   *   Read More Text for Alternative Language.
   */
  public function setAltLanguageReadMore(string $altLanguageReadMore);

  /**
   * Get IAB Legal description in alt language.
   *
   * @return string
   *   IAB Legal description in alt language.
   */
  public function getAltLanguageIabLegalDescription();

  /**
   * Set IAB Legal description in alt language.
   *
   * @param string $altLanguageIabLegalDescription
   *   IAB Legal description in alt language.
   */
  public function setAltLanguageIabLegalDescription(string $altLanguageIabLegalDescription);

}
