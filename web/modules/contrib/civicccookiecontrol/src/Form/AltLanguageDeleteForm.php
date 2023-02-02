<?php

namespace Drupal\civiccookiecontrol\Form;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form to delete Alternative Languages.
 */
class AltLanguageDeleteForm extends EntityConfirmFormBase {
  /**
   * Cache backend object.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * The form constructor.
   *
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Inject the cache service.
   */
  public function __construct(CacheBackendInterface $cache) {
    $this->cache = $cache;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
          $container->get('cache.data')
      );
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t(
          'Are you sure you want to delete Alternative Language %name?',
          ['%name' => $this->entity->label()]
      );
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.altlanguage.collection');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete Alternative Language');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->entity->delete();
    $messenger = $this->messenger();
    $messenger->addMessage($this->t(
          'Alternative Language %label has been deleted.',
          ['%label' => $this->entity->label()]
      ));

    $this->cache->delete('civiccookiecontrol_config');
    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
