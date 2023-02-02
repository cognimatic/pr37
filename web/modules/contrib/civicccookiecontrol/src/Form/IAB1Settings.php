<?php

namespace Drupal\civiccookiecontrol\Form;

use Drupal\civiccookiecontrol\CCCConfigNames;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Locale\CountryManager;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The configuration form for cookie control settings.
 */
class IAB1Settings extends ConfigFormBase {

  /**
   * Country manager object from dependency injection.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface|\Drupal\Core\Locale\CountryManager
   */
  protected $countryManager;

  /**
   * Cache object from dependency injection.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * IAB2Settings constructor.
   *
   * @param \Drupal\Core\Locale\CountryManager $countryManager
   *   Injected CountyManager service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Injected config factory service.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Injected cache service.
   */
  public function __construct(
        CountryManager $countryManager,
        ConfigFactoryInterface $config_factory,
        CacheBackendInterface $cache
    ) {
    parent::__construct($config_factory);
    $this->countryManager = $countryManager;
    $this->cache = $cache;

    civiccookiecontrol_check_cookie_categories();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
          $container->get('country_manager'),
          $container->get('config.factory'),
          $container->get('cache.data')
      );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'iab1_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $configData = $this->config(CCCConfigNames::IAB)->get();
    $recommendedStateArray = [];
    foreach ($configData as $key => $configValue) {
      if ($key == 'iabRecommendedState') {
        for ($i = 1; $i <= 5; $i++) {
          if ($form_state->getValue('iabRecommendedState_' . $i)) {
            $recommendedStateArray[$i] =
                          $form_state->getValue('iabRecommendedState_' . $i) ? TRUE : FALSE;
          }
        }
        if (count($recommendedStateArray) > 0) {
          $this->config(CCCConfigNames::IAB)->set($key, Json::encode($recommendedStateArray))->save();
        }
      }
      elseif (strpos($key, 'iab') !== FALSE) {
        if (strpos($key, 'Text') !== FALSE) {
          if ($form_state->getValue($key) != '') {
            $this->config(CCCConfigNames::IAB)->set($key, str_replace([
              "\r\n",
              "\n",
              "\r",
            ], '', $form_state->getValue($key)))->save();
          }
        }
        else {
          $this->config(CCCConfigNames::IAB)->set($key, $form_state->getValue($key))->save();
        }
      }
    }
    $this->cache->delete('civiccookiecontrol_config');
    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [CCCConfigNames::IAB];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['iab'] = [
      '#type' => 'details',
      '#title' => $this->t('IAB V1.0 Settings'),
      '#open' => TRUE,
    ];

    $form['iab']['iabCMP'] = [
      '#type' => 'radios',
      '#title' => $this->t("Enable IAB Support."),
      '#options' => [
        TRUE => $this->t("Yes"),
        FALSE => $this->t('No'),
      ],
      '#default_value' => $this->config(CCCConfigNames::IAB)
        ->get('iabCMP') ? 1 : 0,
      '#description' => $this->t("Whether or not Cookie Control supports the IAB's TCF v1.1."),
    ];
    $form['iab']['iabGdprAppliesGlobally'] = [
      '#type' => 'radios',
      '#title' => $this->t('Obtain consent from all users regardless of their location'),
      '#options' => [
        TRUE => $this->t("Yes"),
        FALSE => $this->t('No'),
      ],
      '#states' => [
            // Action to take.
        'invisible' => [
          ':input[name=iabCMP]' => [
            'value' => 0,
          ],
        ],
      ],
      '#default_value' => $this->config(CCCConfigNames::IAB)
        ->get('iabGdprAppliesGlobally') ? 1 : 0,
      '#description' => $this->t("Determines whether or not consent should be obtained from all users
        regardless of their location, or if we ought to only seek it from those within the EU. Please note,
        if you have excludedCountries set up as part of your main Cookie Control configuration, this value will
        dynamically change to match depending on the locale of the site visitor."),
    ];

    $form['iabRecommendedState'] = [
      '#type' => 'details',
      '#title' => $this->t('IAB Recommended State'),
      '#open' => FALSE,
      '#states' => [
            // Action to take.
        'invisible' => [
          ':input[name=iabCMP]' => [
            'value' => 0,
          ],
        ],
      ],
    ];

    $iabRecommendedStateArray = Json::decode($this->config(CCCConfigNames::IAB)->get('iabRecommendedState'));
    $form['iabRecommendedState']['iabRecommendedState_1'] = [
      '#type' => 'radios',
      '#title' => $this->t('Recommended State for IAB purpose of information storage and access.'),
      '#options' => [
        TRUE => $this->t("On"),
        FALSE => $this->t('off'),
      ],
      '#default_value' => $iabRecommendedStateArray[1] ? 1 : 0,
      '#description' => $this->t("Sets the default value for information storage and access IAB purpose."),
    ];
    $form['iabRecommendedState']['iabRecommendedState_2'] = [
      '#type' => 'radios',
      '#title' => $this->t('Recommended State for IAB purpose of Personalisation.'),
      '#options' => [
        TRUE => $this->t("On"),
        FALSE => $this->t('off'),
      ],
      '#default_value' => $iabRecommendedStateArray[2] ? 1 : 0,
      '#description' => $this->t("Sets the default value for Personalisation IAB purpose."),
    ];

    $form['iabRecommendedState']['iabRecommendedState_3'] = [
      '#type' => 'radios',
      '#title' => $this->t('Recommended State for IAB purpose of Ad selection, delivery, reporting.'),
      '#options' => [
        TRUE => $this->t("On"),
        FALSE => $this->t('off'),
      ],
      '#default_value' => $iabRecommendedStateArray[3] ? 1 : 0,
      '#description' => $this->t("Sets the default value for Ad selection, delivery, reporting IAB purpose."),
    ];
    $form['iabRecommendedState']['iabRecommendedState_4'] = [
      '#type' => 'radios',
      '#title' => $this->t('Recommended State for IAB purpose of Content selection, delivery, reporting.'),
      '#options' => [
        TRUE => $this->t("On"),
        FALSE => $this->t('off'),
      ],
      '#default_value' => $iabRecommendedStateArray[4] ? 1 : 0,
      '#description' => $this->t("Sets the default value for Content selection, delivery, reporting IAB purpose."),
    ];
    $form['iabRecommendedState']['iabRecommendedState_5'] = [
      '#type' => 'radios',
      '#title' => $this->t('Recommended State for IAB purpose of Measurement.'),
      '#options' => [
        TRUE => $this->t("On"),
        FALSE => $this->t('off'),
      ],
      '#default_value' => $iabRecommendedStateArray[5] ? 1 : 0,
      '#description' => $this->t("Sets the default value for Measurement IAB purpose."),
    ];

    $form['iabTexts'] = [
      '#type' => 'details',
      '#title' => $this->t('IAB Texts'),
      '#open' => FALSE,
      '#states' => [
            // Action to take.
        'invisible' => [
          ':input[name=iabCMP]' => [
            'value' => 0,
          ],
        ],
      ],
    ];

    $form['iabTexts']['iabLabelText'] = [
      '#type' => 'textfield',
      '#title' => $this->t('IAB Label'),
      '#description' => $this->t('Replacement text for "Ad Vendors"'),
      '#default_value' => $this->config(CCCConfigNames::IAB)->get('iabLabelText'),
    ];
    $form['iabTexts']['iabDescriptionText'] = [
      '#type' => 'textarea',
      '#title' => $this->t('IAB Description'),
      '#description' => $this->t('Set the description text for IAB'),
      '#default_value' => $this->config(CCCConfigNames::IAB)
        ->get('iabDescriptionText'),
    ];
    $form['iabTexts']['iabConfigureText'] = [
      '#type' => 'textfield',
      '#title' => $this->t('IAB Configure Text'),
      '#description' => $this->t('Set the label for the IAB cofiguration button.'),
      '#default_value' => $this->config(CCCConfigNames::IAB)->get('iabConfigureText'),
    ];
    $form['iabTexts']['iabPanelTitleText'] = [
      '#type' => 'textfield',
      '#title' => $this->t('IAB Panel Title'),
      '#description' => $this->t('Set the title for the IAB panel.'),
      '#default_value' => $this->config(CCCConfigNames::IAB)->get('iabPanelTitleText'),
    ];
    $form['iabTexts']['iabPanelIntroText'] = [
      '#type' => 'textarea',
      '#title' => $this->t('IAB Panel Introduction Text'),
      '#description' => $this->t('Set the introductory text for the IAB panel.'),
      '#default_value' => $this->config(CCCConfigNames::IAB)->get('iabPanelIntroText'),
    ];
    $form['iabTexts']['iabAboutIabText'] = [
      '#type' => 'textarea',
      '#title' => $this->t('About IAB Text'),
      '#description' => $this->t('Set the about AIB text.'),
      '#default_value' => $this->config(CCCConfigNames::IAB)->get('iabAboutIabText'),
    ];
    $form['iabTexts']['iabIabNameText'] = [
      '#type' => 'textarea',
      '#title' => $this->t('About IAB Text'),
      '#description' => $this->t('Set the about AIB text.'),
      '#default_value' => $this->config(CCCConfigNames::IAB)->get('iabIabNameText'),
    ];

    $form['iabTexts']['iabIabLinkText'] = [
      '#type' => 'url',
      '#title' => $this->t('IAB Link'),
      '#description' => $this->t('Set the URL for IAB link.'),
      '#default_value' => $this->config(CCCConfigNames::IAB)->get('iabIabLinkText'),
    ];

    $form['iabTexts']['iabPanelBackText'] = [
      '#type' => 'textfield',
      '#title' => $this->t('IAB Panel Back Text'),
      '#description' => $this->t('Set the text for the "Back" button.'),
      '#default_value' => $this->config(CCCConfigNames::IAB)->get('iabPanelBackText'),
    ];
    $form['iabTexts']['iabVendorTitleText'] = [
      '#type' => 'textfield',
      '#title' => $this->t('IAB Vendor Title Text'),
      '#description' => $this->t('Set the text for  Vendor Title.'),
      '#default_value' => $this->config(CCCConfigNames::IAB)->get('iabVendorTitleText'),
    ];
    $form['iabTexts']['iabVendorConfigureText'] = [
      '#type' => 'textfield',
      '#title' => $this->t('IAB Vendor Configure Text'),
      '#description' => $this->t('Set the text for IAB vendors configuration button.'),
      '#default_value' => $this->config(CCCConfigNames::IAB)->get('iabVendorConfigureText'),
    ];
    $form['iabTexts']['iabVendorBackText'] = [
      '#type' => 'textfield',
      '#title' => $this->t('IAB Back to Vendor purposes title.'),
      '#description' => $this->t('Sets label for the back to vendor purposes button.'),
      '#default_value' => $this->config(CCCConfigNames::IAB)->get('iabVendorBackText'),
    ];
    $form['iabTexts']['iabAcceptAllText'] = [
      '#type' => 'textfield',
      '#title' => $this->t('IAB Accept All Label.'),
      '#description' => $this->t('Sets label for the "Accept All" button.'),
      '#default_value' => $this->config(CCCConfigNames::IAB)->get('iabAcceptAllText'),
    ];
    $form['iabTexts']['iabRejectAllText'] = [
      '#type' => 'textfield',
      '#title' => $this->t('IAB Reject All Label.'),
      '#description' => $this->t('Sets label for the "Reject All" button.'),
      '#default_value' => $this->config(CCCConfigNames::IAB)->get('iabRejectAllText'),
    ];
    $form['iabTexts']['iabBackText'] = [
      '#type' => 'textfield',
      '#title' => $this->t('IAB "Back" button text.'),
      '#description' => $this->t('Sets label for the "Back" button.'),
      '#default_value' => $this->config(CCCConfigNames::IAB)->get('iabBackText'),
    ];

    $form_state->setCached(FALSE);

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save IAB Configuration'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

}
