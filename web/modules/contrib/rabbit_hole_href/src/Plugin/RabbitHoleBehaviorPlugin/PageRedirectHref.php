<?php

namespace Drupal\rabbit_hole_href\Plugin\RabbitHoleBehaviorPlugin;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Url;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\rabbit_hole\Plugin\RabbitHoleBehaviorPlugin\PageRedirect;

/**
 * Redirects to another page.
 *
 * @RabbitHoleBehaviorPlugin(
 *   id = "page_redirect_href",
 *   label = @Translation("Page redirect with link override")
 * )
 */
class PageRedirectHref extends PageRedirect {
  use StringTranslationTrait;

  /**
   * Returns the action target.
   *
   * Returns the action target like determined as redirect target in
   * Drupal\rabbit_hole\Plugin\RabbitHoleBehaviorPlugin\PageRedirect::performAction().
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The content entity to apply the direct redirect to.
   *
   * @return Symfony\Component\HttpFoundation\Response
   *   The response for the redirect.
   */
  public function getActionTarget(EntityInterface $entity) {
    // @todo Write a Drupal.org issue to make this part a
    // protected function in PageRedirect.
    // We had to duplicate this code because we can't get our target
    // from performAction().
    $target = $entity->get('rh_redirect')->value;
    $bundle_settings = $this->getBundleSettings($entity);

    if (empty($target)) {
      $target = $bundle_settings->get('redirect');
    }

    // Replace any tokens if applicable.
    $langcode = $entity->language()->getId();

    if ($langcode == LanguageInterface::LANGCODE_NOT_APPLICABLE) {
      $langcode = LanguageInterface::LANGCODE_NOT_SPECIFIED;
    }

    if ($this->moduleHandler->moduleExists('token')) {
      $target = $this->token->replace($target,
        [
          $entity->getEntityTypeId() => $entity,
        ],
        [
          'clear' => TRUE,
          'langcode' => $langcode,
        ], new BubbleableMetadata()
      );
    }

    if ($target === '<front>' || $target === '/<front>') {
      // Special case for redirecting to the front page.
      $target = \Drupal::service('url_generator')->generateFromRoute('<front>', [], []);
    }

    // If non-absolute URI, pass URL through Drupal's URL generator to
    // handle languages etc.
    if (!UrlHelper::isExternal($target)) {
      $target = Url::fromUserInput($target);
    }

    return $target;
  }

}
