<?php

namespace Drupal\civiccookiecontrol\Form\Steps;

use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\TempStore\TempStoreException;
use Symfony\Component\Yaml\Yaml;

/**
 * Class BaseStep.
 *
 * @package Drupal\civiccookiecontrol\Forms\Step
 */
abstract class CCCBaseStep implements CCCStepInterface {
  use LoggerChannelTrait;
  /**
   * The array of step form elements.
   *
   * @var array
   */
  public $cccFormElements;

  /**
   * PrivateTempStore object.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStore
   */
  public $tempStore;

  /**
   * File url generator object.
   *
   * @var \Drupal\Core\File\FileUrlGenerator
   */
  public $fileUrlGenerator;

  /**
   * Filesystem object.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  public $fileSystem;

  /**
   * Country manager object.
   *
   * @var \Drupal\Core\Locale\CountryManager
   */

  public $countryManager;
  /**
   * Multi steps of the form.
   *
   * @var CCCStepInterface
   */
  protected $step;

  /**
   * Values of element.
   *
   * @var array
   */
  protected $values;

  /**
   * Step manager.
   *
   * @var \Drupal\civiccookiecontrol\Form\Steps\CCCStepsManager
   */
  private $stepManager;

  /**
   * BaseStep constructor.
   */
  public function __construct() {
    $this->step = $this->setStep();
  }

  /**
   * {@inheritdoc}
   */
  public function getStep() {
    return $this->step;
  }

  /**
   * {@inheritdoc}
   */
  public function isLastStep() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function setValues(array $values) {
    $this->values = $values;
  }

  /**
   * {@inheritdoc}
   */
  public function getValues() {
    return $this->values;
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldNames() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldsValidators() {
    return [];
  }

  /**
   * Set current step.
   *
   * @return int
   *   Return CCCStepsEnum value.
   */
  abstract protected function setStep();

  /**
   * Get steps manager.
   *
   * @return \Drupal\civiccookiecontrol\Form\Steps\CCCStepsManager
   *   The manager.
   */
  public function getStepManager() {
    return $this->stepManager;
  }

  /**
   * Set the steps manager object.
   *
   * @param \Drupal\civiccookiecontrol\Form\Steps\CCCStepsManager $stepManager
   *   The step manager.
   */
  public function setStepManager(CCCStepsManager $stepManager): void {
    $this->stepManager = $stepManager;
  }

  /**
   * Construct form field array.
   */
  public function loadFormElements() {

    $cccFormElements = $this->tempStore->get('cccFormElements');

    if (empty($cccFormElements)) {
      $ymlFiles = $this->fileSystem->scanDirectory(DRUPAL_ROOT . '/' . \Drupal::service('extension.list.module')->getPath('civiccookiecontrol') . "/src/Form/CookieControlFormElements", '/.*\.yml$/');
      foreach ($ymlFiles as $file_path => $ymlFile) {
        $file_contents = file_get_contents($file_path);
        $formItems = Yaml::parse($file_contents);
        foreach ($formItems as $key => $element) {
          if (in_array(8, $element['cookieVersion'])) {
            $this->cccFormElements[8][$ymlFile->name][$key] = $element;
          }
          if (in_array(9, $element['cookieVersion'])) {
            if ($element['#type'] == 'radios' && array_key_exists('#cc9options', $element)) {
              $element['#options'] = array_merge($element['#options'], $element['#cc9options']);
            }
            $this->cccFormElements[9][$ymlFile->name][$key] = $element;
          }
          unset($element['cookieVersion']);
          unset($element['#cc9options']);
        }
      }
      try {
        $this->tempStore->set('cccFormElements', $this->cccFormElements);
      }
      catch (TempStoreException $e) {
        $this->getLogger('civiccookiecontrol')->notice($e->getMessage());
      }
    }
    else {
      $this->cccFormElements = $cccFormElements;
    }
  }

}
