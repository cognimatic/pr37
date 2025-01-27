<?php

namespace Drupal\civiccookiecontrol\Controller;

use Drupal\civiccookiecontrol\Access\CookieControlAccess;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Link;

/**
 * Provides a listing of Cookie Categories.
 */
class CookieCategoryListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function load() {
    if (empty($this->getEntityIds()) && (CookieControlAccess::checkApiKey())) {
      $cookieCategoriesUrl = Link::createFromRoute(
        $this->t("Cookie Category"),
        'entity.cookiecategory.collection', [], ['absolute' => TRUE]
      );

      $messenger = $this->messenger();
      $messenger->addMessage(
            $this->t('You need to add at least <b>one</b> @ccurl for the Cookie Control module to properly operate.',
              ['@ccurl' => $cookieCategoriesUrl->toString()]),
            $messenger::TYPE_ERROR
        );
    }
    return parent::load();
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Machine name');
    $header['cookieName'] = $this->t('Cookie Name');
    $header['cookieLabel'] = $this->t('Cookie Label');
    $header['recommendedState'] = $this->t('Recommended State');
    $header['lawfulBasis'] = $this->t('Lawful basis');

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['id'] = $entity->id();
    $row['cookieName'] = $entity->label();
    $row['cookieLabel'] = $entity->getCookieLabel();
    $row['recommendedState'] = $entity->getRecommendedState() ? 'on' : 'off';
    $row['lawfulBasis'] = $entity->getLawfulBasis();

    // You probably want a few more properties here...
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);
    $operations['edit']['attributes'] = [
      'class' => ['use-ajax'],
      'data-accepts' => 'application/vnd.drupal-modal',
      'data-dialog-type' => ['dialog'],
      'data-dialog-options' => [Json::encode(['width' => 700])],
    ];
    $operations['delete']['attributes'] = [
      'class' => ['use-ajax'],
      'data-accepts' => 'application/vnd.drupal-modal',
      'data-dialog-type' => ['dialog'],
      'data-dialog-options' => [Json::encode(['width' => 700])],
    ];
    return $operations;
  }

}
