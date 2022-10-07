<?php

namespace Drupal\media_link_enhancements\Plugin\Linkit\Matcher;

use Drupal\Core\Entity\EntityInterface;
use Drupal\linkit\Plugin\Linkit\Matcher\EntityMatcher;

/**
 * Provides specific LinkIt matchers for our custom entity type.
 *
 * @Matcher(
 *   id = "entity:media",
 *   label = @Translation("Media"),
 *   target_entity = "media",
 *   provider = "media_link_enhancements"
 * )
 */
class MediaLinkEnhancementsMediaEntityMatcher extends EntityMatcher {

  /**
   * Builds the path string used in the suggestion.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The matched entity.
   *
   * @return string
   *   The path for this entity.
   */
  protected function buildPath(EntityInterface $entity) {

    // Pass the linkit_matcher option to provide context to the URL generator.
    // This is the only deviation from the extended class.
    $path = $entity->toUrl('canonical', [
      'path_processing' => FALSE,
      'linkit_matcher' => TRUE,
    ])->toString();

    // For media entities, check if standalone URLs are allowed. If not, then
    // strip '/edit' from the end of the canonical URL returned
    // by $entity->toUrl().
    if ($entity->getEntityTypeId() == 'media') {
      $standalone_url = \Drupal::config('media.settings')->get('standalone_url');
      if (!$standalone_url) {
        // Strip "/edit".
        $path = substr($path, 0, -5);
      }
    }
    return $path;
  }

}
