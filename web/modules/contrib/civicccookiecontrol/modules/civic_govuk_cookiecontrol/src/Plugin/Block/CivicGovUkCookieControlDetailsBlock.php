<?php

namespace Drupal\civic_govuk_cookiecontrol\Plugin\Block;

/**
 * @file
 * Contains \Drupal\civic_govuk_cookiecontrol\Plugin\Block\CivicGovUkCookieControlDetailsBlock.
 */

use Drupal\civic_govuk_cookiecontrol\GovUKConfigNames;
use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a govuk_cookiecontrol_details_block block.
 *
 * @Block(
 *   id = "govuk_cookiecontrol_details_block",
 *   admin_label = @Translation("GovUk CookieControl Details"),
 * )
 */
class CivicGovUkCookieControlDetailsBlock extends BlockBase implements ContainerFactoryPluginInterface {
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
   * Entity type manager object from dependency injection.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;
  /**
   * The GOV UK CookieControl configuration settings.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $cookieControlGovUkConfig;

  /**
   * CivicGovUkCookieControlDetailsBlock constructor.
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
   */
  public function __construct(
        array $configuration,
        $plugin_id,
        $plugin_definition,
        ConfigFactoryInterface $config,
        LanguageManagerInterface $languageManager,
        EntityTypeManagerInterface $entityTypeManager
    ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->config = $config;
    $this->cookieControlConfig = $this->config->get(GovUKConfigNames::COOKIECONTROL);
    $this->cookieControlGovUkConfig = $this->config->get(GovUKConfigNames::GOVUKSETTINGS);
    $this->languageManager = $languageManager;
    $this->entityTypeManager = $entityTypeManager;
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
          $container->get('entity_type.manager')
      );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $lang = $this->languageManager->getCurrentLanguage()->getId();

    $renderArray = [
      '#theme' => 'civic_govuk_cookiecontrol_details',
      '#attached' => [
        'library' => ['civic_govuk_cookiecontrol/civic_govuk_cookiecontrol.details'],
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
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function loadCookieTextsInRenderArray(array &$renderArray, $lang = 'en', $altLangEntity = NULL) {
    $categories = [];
    $storage = $this->entityTypeManager->getStorage('cookiecategory');
    $ids = $storage->getQuery('cookiecategory')->execute();
    $categoriesList = $storage->loadMultiple($ids);

    if ($lang != 'en' && $altLangEntity != NULL) {
      $optCookiesAltLang = $altLangEntity->getAltLanguageOptionalCookies();
      $i = 0;
      foreach ($categoriesList as $entity) {
        $categories[] = [
          'id' => $entity->id(),
          'title' => $entity->label(),
          'cookieLabel' => array_key_exists($i, $optCookiesAltLang) ? $optCookiesAltLang[$i]['label'] : $entity->getCookieLabel(),
          'cookieDescription' => array_key_exists($i, $optCookiesAltLang) ? $optCookiesAltLang[$i]['description'] : $entity->getCookieDescription(),
        ];
        $i++;
      }
      $renderArray['#categories'] = $categories;
      $renderArray['#title_details'] = $altLangEntity->getAltLanguageTitle();
      $renderArray['#description'] = $altLangEntity->getAltLanguageIntro();
      $renderArray['#yes_text'] = $altLangEntity->getAltLanguageOn();
      $renderArray['#no_text'] = $altLangEntity->getAltLanguageOff();
    }
    else {
      foreach ($categoriesList as $entity) {
        $categories[] = [
          'id' => $entity->id(),
          'title' => $entity->label(),
          'cookieLabel' => $entity->getCookieLabel(),
          'cookieDescription' => $entity->getCookieDescription(),
        ];
      }
      $renderArray['#categories'] = $categories;
      $renderArray['#title_details'] = $this->cookieControlConfig->get('civiccookiecontrol_title_text');
      $renderArray['#description'] = $this->cookieControlConfig->get('civiccookiecontrol_intro_text');
      $renderArray['#yes_text'] = $this->cookieControlConfig->get('civiccookiecontrol_on_text');
      $renderArray['#no_text'] = $this->cookieControlConfig->get('civiccookiecontrol_off_text');
    }
    $renderArray['#optional_cookie_text'] =
          $this->cookieControlGovUkConfig->get('govuk_cookiecontrol_optional_cookie_text');

    $renderArray['#saved_settings_text'] =
          $this->cookieControlGovUkConfig->get('govuk_cookiecontrol_saved_settings_text');

    $renderArray['#save_and_continue'] =
          $this->cookieControlGovUkConfig->get('govuk_cookiecontrol_save_and_continue');
    $renderArray['#allow_cookies_question_prefix'] =
          $this->cookieControlGovUkConfig->get('govuk_cookiecontrol_allow_cookies_question_prefix');
    $renderArray['#allow_cookies_question_suffix'] =
           $this->cookieControlGovUkConfig->get('govuk_cookiecontrol_allow_cookies_question_suffix');

    return $renderArray;
  }

}
