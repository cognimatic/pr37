<?php


namespace Drupal\abc_bins;


interface WebserviceInterface {

  public function getDatesByRoute($routeName);

  public function getDatesByUprn($uprn);

  public function getRoute($routeName);

  public function getProperties($postcode);
}
