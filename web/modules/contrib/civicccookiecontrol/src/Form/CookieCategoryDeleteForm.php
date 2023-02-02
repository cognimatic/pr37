<?php

namespace Drupal\civiccookiecontrol\Form;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Entity configuration form to delete cookie categories.
 */
class CookieCategoryDeleteForm extends EntityConfirmFormBase {

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
   *   Injected cache service.
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
          'Are you sure you want to delete Cookie Category %name?',
          ['%name' => $this->entity->label()]
      );
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.cookiecategory.collection');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete Cookie Category');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->entity->delete();
    $messenger = $this->messenger();
    $messenger->addMessage(
          $this->t(
              'Cookie Category %label has been deleted.',
              ['%label' => $this->entity->label()]
          )
      );
    $this->cache->delete('civiccookiecontrol_config');
    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
