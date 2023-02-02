<?php

namespace Drupal\civiccookiecontrol\Form\Steps;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Cookie control configuration form steps manager.
 */
class CCCStepsManager {

  /**
   * The array of steps.
   *
   * @var array
   */
  protected $steps;

  /**
   * The step for importing license info.
   *
   * @var CCCLicenseInfo
   */
  protected $cccLicenseInfo;

  /**
   * The step for configuring cookie control settings.
   *
   * @var CCCSettings
   */
  protected $cccSettings;

  /**
   * CCCStepsManager constructor.
   *
   * @param CCCLicenseInfo $cccLicenseInfo
   *   Injected CCCLicenseInfo step.
   * @param CCCSettings $cccSettings
   *   Injected CCCSettings step.
   */
  public function __construct(CCCLicenseInfo $cccLicenseInfo, CCCSettings $cccSettings) {
    $this->cccLicenseInfo = $cccLicenseInfo;
    $this->cccSettings = $cccSettings;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('civiccookiecontrol.CCCLicenseInfo'),
      $container->get('civiccookiecontrol.CCCSettings')
    );
  }

  /**
   * Add a step to the steps property.
   *
   * @param \Drupal\civiccookiecontrol\Form\Steps\CCCStepInterface $step
   *   Step of the form.
   */
  public function addStep(CCCStepInterface $step) {
    $this->steps[$step->getStep()] = $step;
  }

  /**
   * Fetches step from steps property, If it doesn't exist, create step object.
   *
   * @param int $step_id
   *   Step ID.
   *
   * @return \Drupal\civiccookiecontrol\Form\Steps\CCCStepInterface
   *   Return step object.
   */
  public function getStep($step_id) {
    if (isset($this->steps[$step_id])) {
      $step = $this->steps[$step_id];
    }
    else {
      $step = $this->cccLicenseInfo;
      if ($step_id == CCCStepsEnum::CCC_SETTINGS) {
        $step = $this->cccSettings;
      }
      $step->setStepManager($this);
    }

    return $step;
  }

  /**
   * Get all steps.
   *
   * @return array
   *   Steps.
   */
  public function getAllSteps() {
    return $this->steps;
  }

}
