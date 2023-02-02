<?php

namespace Drupal\civic_govuk_cookiecontrol\Form;

/**
 * @file
 * Contains \Drupal\civic_govuk_cookiecontrol\Form\CivicGovUkCookieControlSettings.
 */

use Drupal\civic_govuk_cookiecontrol\GovUKConfigNames;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\locale\SourceString;
use Drupal\locale\StringStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Configure cookie control settings for this site.
 */
class CivicGovUkCookieControlSettings extends ConfigFormBase {
  use MessengerTrait;
  /**
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;
  /**
   * The string locale storage service.
   *
   * @var \Drupal\locale\StringStorageInterface
   */
  protected $localeStorage;
  /**
   * The array of defined languages.
   *
   * @var \Drupal\Core\Language\LanguageInterface[]
   */
  protected $langCodes;

  /**
   * CivicGovUkCookieControlSettings constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   Injected config factory service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   Injected language manager service.
   * @param \Drupal\locale\StringStorageInterface $localeStorage
   *   Injected string storage service.
   */
  public function __construct(
        ConfigFactoryInterface $config,
        LanguageManagerInterface $languageManager,
        StringStorageInterface $localeStorage
    ) {
    parent::__construct($config);
    // $this->config = $config->getEditable(GovUKConfigNames::GOVUKSETTINGS);
    // $this->cccConfig = $config->get(GovUKConfigNames::COOKIECONTROL);
    $this->languageManager = $languageManager;
    $this->localeStorage = $localeStorage;
    $this->langCodes = $this->languageManager->getLanguages();
    civiccookiecontrol_check_cookie_categories();
    $this->checkStmtFields();
  }

  /**
   * Checks privacy statement definition in cookie control and display messages.
   */
  private function checkStmtFields() {

    if (empty($this->config(GovUKConfigNames::COOKIECONTROL)->get('civiccookiecontrol_privacynode'))) {
      $this->messenger()->addError($this->t('The privacy policy link is undefined.
            Please add it in the "Privacy Statement" section
            of <a href="/admin/config/system/cookiecontrol">Cookie Control configuration form</a>.'));
    }
    if (empty($this->config(GovUKConfigNames::COOKIECONTROL)->get('civiccookiecontrol_stmt_name'))) {
      $this->messenger()->addError($this->t('The "Statement Name" textfield is empty.
            Please add some text in the "Privacy Statement" section
            of <a href="/admin/config/system/cookiecontrol">Cookie Control configuration form</a>.'));
    }
    if (empty($this->config(GovUKConfigNames::COOKIECONTROL)->get('civiccookiecontrol_stmt_descr'))) {
      $this->messenger()->addError($this->t('The "Statement Description" textfield is empty.
            Please add some text in the "Privacy Statement" section
            of <a href="/admin/config/system/cookiecontrol">Cookie Control configuration form</a>.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
          $container->get('config.factory'),
          $container->get('language_manager'),
          $container->get('locale.storage')
      );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'civic_govuk_cookiecontrol_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      GovUKConfigNames::GOVUKSETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['wrapper-messages'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'messages-wrapper',
      ],
    ];
    $form['wrapper'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'ccc-wrapper',
      ],
    ];

    foreach ($this->langCodes as $key => $lang) {
      $form['wrapper']['gov_uk_texts'][$key] = [
        '#type' => 'details',
        '#title' => $this->t('Gov Uk Texts in @lang', ['@lang' => $lang->getName()]),
        '#open' => TRUE,
      ];

      $this->loadSettings($form['wrapper']['gov_uk_texts'][$key], $key, $lang);
    }

    $form_state->setCached(FALSE);

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save GOV UK texts'),
      '#button_type' => 'primary',
    ];

    $form['#attached'] = [
      'library' => [
        'civiccookiecontrol/civiccookiecontrol.admin_css',
      ],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\locale\StringStorageException
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $configData = $this->config(GovUKConfigNames::GOVUKSETTINGS)->get();
    foreach ($this->langCodes as $langCode => $lang) {
      foreach ($configData as $key => $configValue) {
        if (strpos($key, 'govuk_cookiecontrol') !== FALSE) {
          if ($langCode == 'en') {
            $this->config(GovUKConfigNames::GOVUKSETTINGS)->set($key, $form_state->getValue($key . '_' . $langCode))->save();
          }
          else {
            $string = $this->localeStorage->findString(['source' => $this->config(GovUKConfigNames::GOVUKSETTINGS)->get($key)]);
            if (is_null($string)) {
              $string = new SourceString();
              $string->setString($this->config(GovUKConfigNames::GOVUKSETTINGS)->get($key));
              $string->setStorage($this->localeStorage);
              $string->save();
            }
            $this->localeStorage->createTranslation([
              'lid' => $string->lid,
              'language' => $langCode,
              'translation' => $form_state->getValue($key . '_' . $langCode),
            ])->save();
          }
        }
      }
    }

    parent::submitForm($form, $form_state);
  }

  /**
   * Loads the form fields from the corresponding yml file.
   *
   * @param array $form
   *   The form array.
   * @param string $langCode
   *   Current language code.
   * @param string $lang
   *   Current language string.
   */
  protected function loadSettings(array &$form, $langCode, $lang) {
    $iabYamlPath = drupal_get_path(
          'module',
          'civic_govuk_cookiecontrol'
      ) .
          "/src/Form/GovUkFormElements/civic_govuk_cookiecontrol.texts.yml";

    $formItems = Yaml::parse(file_get_contents($iabYamlPath));
    foreach ($formItems as $key => $element) {
      if (!empty($element['#title'])) {
        $element['#title'] = $element['#title'] . ' ' . $this->t('in @lang', ['@lang' => $lang->getName()]);
      }
      if (!empty($element['#description'])) {
        $element['#description'] = $element['#description'] . ' ' . $this->t('in @lang', ['@lang' => $lang->getName()]);
      }
      if ($element['#default_value'] == $key) {
        if ($langCode == 'en') {
          $element['#default_value'] = $this->config(GovUKConfigNames::GOVUKSETTINGS)->get($key);
        }
        else {
          $translation = $this->localeStorage->findTranslation(
            [
              'source' => $this->config(GovUKConfigNames::GOVUKSETTINGS)->get($key),
              'language' => $langCode,
            ]
          );
          if (!is_null($translation)) {
            $element['#default_value'] = $translation->getString();
          }
          else {
            $element['#default_value'] = '';
          }
        }
      }
      $form[$key . '_' . $langCode] = $element;
    }
  }

}
