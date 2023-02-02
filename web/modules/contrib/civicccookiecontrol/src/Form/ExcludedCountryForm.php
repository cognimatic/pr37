<?php

namespace Drupal\civiccookiecontrol\Form;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The form to add/edit excluded countries.
 */
class ExcludedCountryForm extends EntityForm {

  /**
   * The cache backend object.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * ExcludedCountryForm constructor.
   *
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Injected cache object.
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

    $excludedCountry = $this->entity;

    if ($this->operation == 'edit') {
      $form['#title'] = $this->t(
            'Edit Excluded Country Iso Code: @name',
            ['@name' => $excludedCountry->label()]
        );
    }
    else {
      $form['#title'] = $this->t('Add Excluded Country Iso Code');
    }

    $form['excludedCountryIsoCode'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Excluded Country Iso Code'),
      '#maxlength' => 255,
      '#default_value' => $excludedCountry->label(),
      '#description' => $this->t("Excluded Country Iso Code"),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $excludedCountry->id(),
      '#machine_name' => [
        'exists' => [$this, 'exist'],
        'source' => ['excludedCountryIsoCode'],
      ],
      '#disabled' => !$excludedCountry->isNew(),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    try {
      $excludedCountry = $this->entity;
      $status = $excludedCountry->save();

      if ($status) {
        $this->messenger()->addMessage(
              $this->t(
                  'Saved the %label Excluded Country.',
                  [
                    '%label' => $excludedCountry->label(),
                  ]
              )
          );
      }
      else {
        $this->messenger()->addMessage(
              $this->t(
                  'The %label Excluded Country was not saved.',
                  [
                    '%label' => $excludedCountry->label(),
                  ]
              )
                );
      }

      $this->cache->delete('civiccookiecontrol_config');
      $form_state->setRedirect('entity.excludedcountry.collection');
    }
    catch (EntityStorageException $ex) {
      $this->messenger()->addMessage(
            $this->t(
                'The %label Excluded Country already exist.',
                [
                  '%label' => $excludedCountry->label(),
                ]
            )
            );

      $form_state->setRedirect('entity.excludedcountry.collection');
    }
  }

  /**
   * Helper function to check whether an Example configuration entity exists.
   *
   * @param string $id
   *   Excluded country machine name.
   *
   * @return bool
   *   Return value.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function exist($id) {
    $entity = $this->entityTypeManager->getStorage('excludedcountry')->getQuery()
      ->condition('id', $id)
      ->execute();
    return (bool) $entity;
  }

}
