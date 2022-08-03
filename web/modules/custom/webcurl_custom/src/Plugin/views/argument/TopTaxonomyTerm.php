<?php
namespace Drupal\wcl_views_argument_top_taxonomy\Plugin\views\argument;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\Plugin\views\argument\Taxonomy as ArgumentBase;
/**
 * Defines a filter for finding Top-level taxonomy terms.
 *
 * @ingroup views_argument_handlers
 *
 * @ViewsArgument("top_taxonomy_term")
 */
class TopTaxonomyTerm extends ArgumentBase {
  /**
   * {@inheritdoc}
   */
  public function setArgument($arg) {
    // If we are not dealing with the exception argument, example "all".
    if ($this->isException($arg)) {
      return parent::setArgument($arg);
    }
    $tid = $arg;
    $topTid = $this->findAncestors($tid);
    $this->argument = (int) $topTid;
    return $this->validateArgument($topTid);
  }

  function findAncestors($tid) {
    $topTid = $tid;
    $ancestors = \Drupal::service('entity_type.manager')->getStorage("taxonomy_term")->loadAllParents($tid);
    unset($ancestors[$tid]);
    foreach ($ancestors as $newTid => $term) {
      $topTid = $newTid;
      $topTid = $this->findAncestors($topTid);
    }
    return $topTid;
  }
}