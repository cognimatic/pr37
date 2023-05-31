<?php

namespace Drupal\civiccookiecontrol\Form\Steps;

use Drupal\civiccookiecontrol\CCCConfigNames;
use Drupal\civiccookiecontrol\Form\Buttons\CCCFlushFormCacheButton;
use Drupal\civiccookiecontrol\Form\Buttons\CCCSettingsButton;
use Drupal\civiccookiecontrol\Form\Buttons\CCCSubmitButton;
use Drupal\civiccookiecontrol\Form\CCCFormHelper;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Link;
use Drupal\Core\Locale\CountryManager;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Settings step.
 */
class CCCSettings extends CCCBaseStep {

  use StringTranslationTrait;
  use DependencySerializationTrait;
  use MessengerTrait;

  /**
   * The configuration object.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * CCCSettings constructor.
   *
   * @param \Drupal\Core\Locale\CountryManager $countryManager
   *   Injected country manager service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   Injected config factory service.
   * @param \Drupal\Core\File\FileSystemInterface $fileSystem
   *   Injected files system service.
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $tempStoreFactory
   *   Injected tempstore private service.
   */
  public function __construct(
        CountryManager $countryManager,
        ConfigFactoryInterface $config,
        FileSystemInterface $fileSystem,
        PrivateTempStoreFactory $tempStoreFactory
    ) {
    $this->countryManager = $countryManager;
    $this->fileSystem = $fileSystem;
    $this->tempStore = $tempStoreFactory->get('civiccookiecontrol');
    $this->config = $config->getEditable(CCCConfigNames::COOKIECONTROL);
    $this->loadFormElements();
  }

  /**
   * Set current step.
   *
   * @return int
   *   Return CCCStepsEnum value.
   */
  protected function setStep() {
    return CCCStepsEnum::CCC_SETTINGS;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this base step class.
    return new static(
      // Load the service required to construct this class.
          $container->get('country_manager'),
          $container->get('config.factory'),
          $container->get('file_system'),
          $container->get('tempstore.private')
      );
  }

  /**
   * {@inheritdoc}
   */
  public function buildStepFormElements() {
    $form['wrapper'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'ccc-wrapper',
      ],
    ];

    if (CCCFormHelper::validateApiKey(
          $this->config->get('civiccookiecontrol_api_key'),
          $this->config->get('civiccookiecontrol_product')
      ) == $this->config->get('civiccookiecontrol_product')) {
      $form['wrapper']['product_info'] = [
        '#type' => 'details',
        '#title' => $this->t('Your Cookie Control Product Information'),
        '#open' => TRUE,
      ];

      $this->updateCookieControlFormFields(
            $form['wrapper'],
            $this->getValues()['civiccookiecontrol_api_key_version'] ?? $this->config
              ->get('civiccookiecontrol_api_key_version'),
            'product_info'
        );
      $form['wrapper']['text_options'] = [
        '#type' => 'details',
        '#title' => $this->t('Customising Appearance, Text and Behaviour'),
        '#open' => TRUE,
      ];

      $form['wrapper']['text_options']['civiccookiecontrol_warning']['#markup'] =
              "<div class=\"messages messages--warning\">Please note, we do not store information of any kind until
the user opts into one of your cookie categories. If this never happens and initialState is set to open,
the module will re-appear on each subsequent page load.</div>";

      $this->updateCookieControlFormFields($form['wrapper'], $this->config
        ->get('civiccookiecontrol_api_key_version'), 'text_options');

      $form['wrapper']['statement'] = [
        '#type' => 'details',
        '#title' => $this->t('Privacy Statement'),
        '#description' => $this->t(
            "In the following fields you may add the Privacy Statement for your website."
        ),
        '#open' => TRUE,
      ];

      $this->updateCookieControlFormFields($form['wrapper'], $this->config
        ->get('civiccookiecontrol_api_key_version'), 'statement');

      $form['wrapper']['ccpa_statement'] = [
        '#type' => 'details',
        '#title' => $this->t('CCPA Privacy Statement'),
        '#description' => $this->t(
            "In the following fields you may add the CCPA Privacy Statement for your website."
        ),
        '#open' => TRUE,
      ];

      $this->updateCookieControlFormFields($form['wrapper'], $this->config
        ->get('civiccookiecontrol_api_key_version'), 'ccpa_statement');

      $form['wrapper']['custom_widget'] = [
        '#type' => 'details',
        '#title' => $this->t('Custom Branding'),
        '#description' => $this->t("With PRO and PRO_MULTISITE licenses, you are able to set
            all aspects of the module's styling, and remove any back links to CIVIC."),
        '#states' => [
          'invisible' => [
            ':input[name=civiccookiecontrol_product]' => [
              'value' => 'COMMUNITY',
            ],
          ],
        ],
        '#open' => TRUE,
      ];

      $form['wrapper']['custom_widget']['civiccookiecontrol_warning']['#markup'] =
              "<div class=\"messages messages--warning\">Please note, in changing the branding object you take
responsibility for the module's accessibility standard. Should you set the <strong>removeIcon</strong> option
to <strong>true</strong>, it is your responsibility to create your own ever present button that invokes
<strong>CookieControl.toggle()</strong> so that users may still have consistent access to granting and revoking
their consent.</div>";

      $this->updateCookieControlFormFields($form['wrapper'], $this->config
        ->get('civiccookiecontrol_api_key_version'), 'custom_widget');

      $form['wrapper']['accessibility'] = [
        '#type' => 'details',
        '#title' => $this->t('Accessibility'),
        '#description' => $this->t(
            "Determines the accessibility helpers available, such as the accesskey and keyboard focus style."
        ),
        '#open' => TRUE,
      ];
      $this->updateCookieControlFormFields($form['wrapper'], $this->config
        ->get('civiccookiecontrol_api_key_version'), 'accessibility');
    }
    else {
      $this->messenger()
        ->addMessage(
            $this->t("Invalid Cookie Control api key. Please review your license information"),
            $this->messenger::TYPE_ERROR
            );
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getButtons() {
    return [
      new CCCSettingsButton(),
      new CCCSubmitButton(),
      new CCCFlushFormCacheButton(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldNames() {
    $keys = [];
    foreach ($this->cccFormElements[$this->config->get('civiccookiecontrol_api_key_version')] as $elems) {
      foreach ($elems as $k => $elem) {
        $keys[] = $k;
      }
    }
    return $keys;
  }

  /**
   * Update form field array.
   *
   * @param array $form
   *   The form array.
   * @param int $version
   *   Cookie control api key version.
   * @param string $type
   *   Form array field key.
   */
  public function updateCookieControlFormFields(array &$form, $version, $type) {

    if (empty($this->cccFormElements)) {
      $this->loadFormElements();
    }
    if (!empty($form[$type]) && (!empty($this->cccFormElements[$version][$type]))) {
      $form[$type] = array_merge(
            $form[$type],
            $this->getFormFieldArray($this->cccFormElements[$version][$type])
        );
    }
  }

  /**
   * Adds url link in the GDPR / CCPA statement form field.
   *
   * @param array $element
   *   The element to be updated.
   * @param string $key
   *   Key civiccookiecontrol_privacynode | civiccookiecontrol_ccpa_privacynode.
   */
  private function stametementUrlField(array &$element, $key) {

    global $base_url;
    $element['#field_prefix'] = $base_url . '/node/';
    $privacynode = $this->config->get($key);
    if ($privacynode) {
      $privacyNodeUrl = Link::createFromRoute(
            $this->t("View existing privacy policy page"),
            'entity.node.canonical',
            ['node' => $privacynode],
            ['absolute' => TRUE]
        );
      $element['#field_suffix'] = $privacyNodeUrl->toString();
    }
  }

  /**
   * Load form field array.
   *
   * @param array $elements
   *   Form field array.
   *
   * @return array
   *   Form field array.
   */
  public function getFormFieldArray(array $elements) {
    global $base_url;

    foreach ($elements as $key => &$element) {
      if (empty($element['needsApiValidation']) || /*($this->apiKeyValidated))*/
        (CCCFormHelper::validateApiKey(
            $this->config->get('civiccookiecontrol_api_key'),
            $this->config->get('civiccookiecontrol_product')
        )
        == $this->config->get('civiccookiecontrol_product'))) {
        if ($key == 'civiccookiecontrol_privacynode' || $key == 'civiccookiecontrol_ccpa_privacynode') {
          $this->stametementUrlField($element, $key);
        }

        if (($element['#default_value'] == $key) &&
            (array_key_exists('boolOptions', $element) &&
              $element['boolOptions'] == 1)) {
          $element['#options'] = [
            TRUE => 'Yes',
            FALSE => 'No',
          ];
          $element['#default_value'] = $this->config->get($key) ? 1 : 0;
        }
        elseif ($element['#default_value'] == $key) {
          $element['#default_value'] = $this->config->get($key);
        }

        if (empty($element['#placeholder']) && ($key == 'civiccookiecontrol_button_icon')) {
          $element['#placeholder'] = $base_url . file_url_transform_relative(file_create_url(
                theme_get_setting('logo.url', $this->config->get('default'))
            ));
        }

        if (array_key_exists('#format', $element) && $element['#format'] == 'cookie_control_html') {
          $element['#allowed_formats'] = [$element['#format']];
        }
        unset($element['cookieVersion']);
        unset($element['boolOptions']);
        unset($element['needsApiValidation']);
      }
      else {
        unset($elements[$key]);
      }
    }
    return $elements;
  }

}
