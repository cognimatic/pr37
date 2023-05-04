<?php

namespace Drupal\prod_check_rest\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;

/**
 * Provides a resource to list all the active modules.
 *
 * @RestResource(
 *   id = "prod_check_modules",
 *   label = @Translation("List of all active modules"),
 *   uri_paths = {
 *     "canonical" = "/prod_check/modules"
 *   }
 * )
 */
class ProdCheckModulesResource extends ResourceBase {

  /**
   * @return \Drupal\rest\ResourceResponse
   */
  public function get() {
    return new ResourceResponse(\Drupal::service('extension.list.module')->getAllInstalledInfo(), 200);
  }

}
