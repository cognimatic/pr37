<?php

namespace Drupal\content_readability\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\ConfigFactory;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Content Readability Delete Profile Form.
 */
class ContentReadabilityDeleteProfile extends ConfirmFormBase {

  /**
   * Configuration Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * Profile Name.
   */
  protected $profile;

  /**
   * Constructor.
   */
  public function __construct(ConfigFactory $configFactory) {

    $this->configFactory = $configFactory->getEditable('content_readability.settings');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'content_readability.delete_profile.config';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this
      ->t('Are you sure you want to delete the %profile profile', [
        '%profile' => $this->profile,
      ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('content_readability.admin.config');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, string $profile = NULL) {
    $this->profile = $profile;
    $profiles = $this->configFactory->get('content_readability_profiles');
    ;

    if (!($profiles[$profile])) {
      throw new NotFoundHttpException();
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $profiles = $this->configFactory->get('content_readability_profiles');
    unset($profiles[$this->profile]);

    $this->configFactory->set('content_readability_profiles', $profiles);

    $this->configFactory->save();
    $form_state->setRedirect('content_readability.admin.config');
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['content_readability.settings'];
  }

}
