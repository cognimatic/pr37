<?php

namespace Drupal\civiccookiecontrol\Form;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The form to add/edit necessary cookies.
 */
class NecessaryCookieForm extends EntityForm {

  /**
   * Cache object from dependency injection.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * CookieCategoryForm constructor.
   *
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The cache backend.
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
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $necessaryCookie = $this->entity;

    if ($this->operation == 'edit') {
      $form['#title'] = $this->t('Edit Necessary Cookie: @name', ['@name' => $necessaryCookie->label()]);
    }
    else {
      $form['#title'] = $this->t('Add Necessary Cookie');
    }

    $form['necessaryCookieName'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Necessary Cookie Name'),
      '#maxlength' => 255,
      '#default_value' => $necessaryCookie->label(),
      '#placeholder' => $this->t("Cookie Name ex. JSESSIONID"),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $necessaryCookie->id(),
      '#machine_name' => [
        'exists' => [$this, 'exist'],
        'source' => ['necessaryCookieName'],
      ],
      '#disabled' => !$necessaryCookie->isNew(),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    try {
      $necessaryCookie = $this->entity;
      $status = $necessaryCookie->save();

      if ($status) {
        $this->messenger()->addMessage(
              $this->t(
                  'Saved the %label Necessary Cookie.',
                  [
                    '%label' => $necessaryCookie->label(),
                  ]
              )
          );
      }
      else {
        $this->messenger()->addMessage(
              $this->t(
                  'The %label Necessary Cookie was not saved.',
                  [
                    '%label' => $necessaryCookie->label(),
                  ]
              )
                );
      }
      $this->cache->delete('civiccookiecontrol_config');
      $form_state->setRedirect('entity.necessarycookie.collection');
      return SAVED_UPDATED;
    }
    catch (EntityStorageException $ex) {
      $this->messenger()->addMessage(
            $this->t(
                'The %label Necessary Cookie Already Exist.',
                [
                  '%label' => $necessaryCookie->label(),
                ]
            )
            );

      $form_state->setRedirect('entity.necessarycookie.collection');
      return -1;
    }
  }

  /**
   * Helper function to check whether an Example configuration entity exists.
   *
   * @param string $id
   *   The Necessary cookie entity machine name.
   *
   * @return bool
   *   Returns true if machine name already exist.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function exist($id) {
    $entity = $this->entityTypeManager->getStorage('necessarycookie')->getQuery()
      ->condition('id', $id)
      ->execute();
    return (bool) $entity;
  }

}
