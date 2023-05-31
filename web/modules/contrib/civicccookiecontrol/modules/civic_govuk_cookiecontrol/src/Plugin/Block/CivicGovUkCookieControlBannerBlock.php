<?php

namespace Drupal\civic_govuk_cookiecontrol\Plugin\Block;

/**
 * @file
 * Contains \Drupal\civic_govuk_cookiecontrol\Plugin\Block\CivicGovUkCookieControlBannerBlock.
 */

use Drupal\civic_govuk_cookiecontrol\GovUKConfigNames;
use Drupal\civiccookiecontrol\Entity\AltLanguage;
use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\locale\StringStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Language\LanguageManagerInterface;

/**
 * Provides a govuk_cookiecontrol_banner_block block.
 *
 * @Block(
 *   id = "govuk_cookiecontrol_banner_block",
 *   admin_label = @Translation("GovUk CookieControl Banner"),
 * )
 */
class CivicGovUkCookieControlBannerBlock extends BlockBase implements ContainerFactoryPluginInterface {
  use LoggerChannelTrait;

  /**
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;
  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;
  /**
   * The cookie control configuration settings.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $cookieControlConfig;
  /**
   * The GOV UK CookieControl configuration settings.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $cookieControlGovUkConfig;

  /**
   * Entity type manager object from dependency injection.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The string locale storage service.
   *
   * @var \Drupal\locale\StringStorageInterface
   */
  protected $localeStorage;

  /**
   * CivicGovUkCookieControlBannerBlock constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   Injected config factory service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   Injected language manager service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Injected Entity type manager service.
   * @param \Drupal\locale\StringStorageInterface $localeStorage
   *   Injected string storage service.
   */
  public function __construct(
        array $configuration,
        $plugin_id,
        $plugin_definition,
        ConfigFactoryInterface $config,
        LanguageManagerInterface $languageManager,
        EntityTypeManagerInterface $entityTypeManager,
        StringStorageInterface $localeStorage
    ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->config = $config;
    $this->cookieControlConfig = $this->config->get(GovUKConfigNames::COOKIECONTROL);
    $this->cookieControlGovUkConfig = $this->config->get(GovUKConfigNames::GOVUKSETTINGS);
    $this->languageManager = $languageManager;
    $this->entityTypeManager = $entityTypeManager;
    $this->localeStorage = $localeStorage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $configuration, $plugin_id, $plugin_definition) {

    return new static(
          $configuration,
          $plugin_id,
          $plugin_definition,
          $container->get('config.factory'),
          $container->get('language_manager'),
          $container->get('entity_type.manager'),
          $container->get('locale.storage')
      );
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['fixed_top'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Fixed position (top)'),
      '#description' => $this->t(
          'Check to show the cookie banner at a fixed position on top of the page.
          Note that this setting may not work for all themes.
          '
      ),
      '#default_value' => $this->configuration['fixed_top'] ?? FALSE,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->configuration['fixed_top'] = $values['fixed_top'];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $lang = $this->languageManager->getCurrentLanguage()->getId();
    $bannerBlockConfig = $this->getConfiguration();
    $renderArray = [
      '#theme' => 'civic_govuk_cookiecontrol_banner',
      '#fixed_top'    => $bannerBlockConfig['fixed_top'],
      '#attached'     => [
        'library' => ['civic_govuk_cookiecontrol/civic_govuk_cookiecontrol.banner'],
      ],
      '#attributes' => [
        'class' => ['civic-govuk-cookiecontrol'],
      ],
    ];

    try {
      $altLangIds = $this->entityTypeManager->getStorage('altlanguage')->getQuery()
        ->condition('id', $lang)
        ->execute();
      $altLangEntity = $this->entityTypeManager->getStorage('altlanguage')->loadMultiple($altLangIds);
      $this->loadCookieTextsInRenderArray($renderArray, $lang, array_shift($altLangEntity));
    }
    catch (InvalidPluginDefinitionException | PluginNotFoundException $e) {
      $this->getLogger('civiccookiecontrol')->error($e->getMessage());
      $this->loadCookieTextsInRenderArray($renderArray, 'en');
    }

    return $renderArray;
  }

  /**
   * Loads text fields from configuration into block rendered array.
   *
   * @param array $renderArray
   *   The render array.
   * @param string $lang
   *   Current language.
   * @param \Drupal\civiccookiecontrol\Entity\AltLanguage|null $altLangEntity
   *   The cookie control altlanguage entity.
   *
   * @return mixed
   *   The final render array.
   */
  protected function loadCookieTextsInRenderArray(
    array &$renderArray,
    $lang = 'en',
    AltLanguage $altLangEntity = NULL
  ) {
    if ($lang != 'en' && $altLangEntity != NULL) {
      $renderArray['#title_banner'] = $altLangEntity->getAltLanguageTitle();
      $renderArray['#description'] = $altLangEntity->getAltLanguageIntro();
      $renderArray['#policy_link'] = $altLangEntity->getAltLanguageStmtUrl() ?
            'node/' . $altLangEntity->getAltLanguageStmtUrl() : '';
      $renderArray['#policy_link'] = $this->getPolicyLink(
            $altLangEntity->getAltLanguageStmtUrl(),
            $altLangEntity->getAltLanguageStmtNameText(),
            $altLangEntity->getAltLanguageStmtDescrText(),
        );

      $renderArray['#accept_label'] = $altLangEntity->getAltLanguageAcceptSettings();
      $renderArray['#reject_label'] = $altLangEntity->getAltLanguageRejectSettings();
    }
    else {
      $renderArray['#title_banner'] = $this->cookieControlConfig->get('civiccookiecontrol_title_text');
      $renderArray['#description'] = $this->cookieControlConfig->get('civiccookiecontrol_intro_text');
      $renderArray['#policy_link'] = $this->getPolicyLink(
            $this->cookieControlConfig->get('civiccookiecontrol_privacynode'),
            $this->cookieControlConfig->get('civiccookiecontrol_stmt_name'),
            $this->cookieControlConfig->get('civiccookiecontrol_stmt_descr')
            );
      $renderArray['#accept_label'] = $this->cookieControlConfig->get('civiccookiecontrol_accept_settings');
      $renderArray['#reject_label'] = $this->cookieControlConfig->get('civiccookiecontrol_reject_settings');
    }

    $renderArray['#accepted_cookies'] =
          $this->cookieControlGovUkConfig->get('govuk_cookiecontrol_accepted_cookies_text');
    $renderArray['#rejected_cookies'] =
          $this->cookieControlGovUkConfig->get('govuk_cookiecontrol_rejected_cookies_text');
    $renderArray['#change_cookie_settings_link'] = $this->getChangeCookieSettingsLink(
          $this->cookieControlConfig->get('civiccookiecontrol_privacynode'),
          $this->cookieControlGovUkConfig->get('govuk_cookiecontrol_change_cookie_settings_prefix'),
          $this->cookieControlGovUkConfig->get('govuk_cookiecontrol_change_cookie_settings_link_text'),
          $this->cookieControlGovUkConfig->get('govuk_cookiecontrol_change_cookie_settings_suffix'),
      );
    $renderArray['#hide'] = $this->cookieControlGovUkConfig->get('govuk_cookiecontrol_hide_button_text');
    return $renderArray;
  }

  /**
   * Constructs the policy link in order to be added in render array.
   *
   * @param int $privacyNode
   *   The nid of privacy node loaded from civiccookiecontrol_privacynode.
   * @param string $stmtName
   *   The statement name loaded from civiccookiecontrol_stmt_name.
   * @param string $stmtDescr
   *   The statement description loaded from civiccookiecontrol_stmt_descr.
   *
   * @return string
   *   The link as string to be added in render array.
   */
  protected function getPolicyLink($privacyNode, $stmtName, $stmtDescr) {
    $policy_link = '';
    if ($nid = $privacyNode) {
      $privacyNodeUrl = Link::createFromRoute(
            $stmtName,
            'entity.node.canonical',
            ['node' => $nid],
            [
              'attributes' =>
              ['class' => "cookie-policy govuk-link"],
              'absolute' => TRUE,
            ]
        );
      $policy_link = Xss::filter(
            $stmtDescr . ' ' .
            $privacyNodeUrl->toString()->getGeneratedLink() . '.'
        );
    }
    return $policy_link;
  }

  /**
   * Constructs the link to the page where the banner is displayed.
   *
   * @param int $privacyNode
   *   The nid of privacy node loaded from civiccookiecontrol_privacynode.
   * @param string $prefix
   *   Text retrieved from govuk_cookiecontrol_change_cookie_settings_prefix.
   * @param string $linkText
   *   Text retrieved from govuk_cookiecontrol_change_cookie_settings_link_text.
   * @param string $suffix
   *   Text retrieved from govuk_cookiecontrol_change_cookie_settings_suffix.
   *
   * @return string
   *   The link as string to be added in render array.
   */
  protected function getChangeCookieSettingsLink($privacyNode, $prefix, $linkText, $suffix) {

    $link = '';
    if ($nid = $privacyNode) {
      $privacyNodeUrl = Link::createFromRoute(
            $this->getTranslation($linkText),
            'entity.node.canonical',
            ['node' => $nid],
            [
              'attributes' =>
              ['class' => "cookie-policy govuk-link"],
              'absolute' => TRUE,
            ]
        );
      $link = Xss::filter(
            $this->getTranslation($prefix) . ' ' .
            $privacyNodeUrl->toString()->getGeneratedLink() . ' ' . $this->getTranslation($suffix) . '.'
        );
    }
    return $link;
  }

  /**
   * Get the string translation.
   *
   * @param string $sourceString
   *   The string to be translated.
   *
   * @return string
   *   The string translation.
   */
  protected function getTranslation($sourceString) {
    $lang = $this->languageManager->getCurrentLanguage()->getId();
    if ($lang == 'en') {
      return $sourceString;
    }

    $string = $this->localeStorage->findString(['source' => $sourceString]);
    if (!is_null($string)) {
      $translations = $this->localeStorage->getTranslations([
        'lid' => $string->lid,
        'language' => $lang,
      ]);

      $sourceString = array_shift($translations);
    }

    return $sourceString->getString();
  }

}
