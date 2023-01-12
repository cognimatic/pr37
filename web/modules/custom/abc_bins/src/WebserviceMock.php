<?php

namespace Drupal\abc_bins;


class WebserviceMock implements WebserviceInterface {

  /**
   * Constructs a WebserviceMock object.
   */
  public function __construct($http_client = NULL) {
  }

  /**
   * @param $routeName
   */
  public function getDatesByRoute($routeName) {
    return $this->getFixture('routes-dates.json');
  }

  public function getDatesByUprn($uprn) {
    return $this->getFixture('uprn-dates.json');
  }

  public function getRoute($routeName) {
    return $this->getFixture('routes.json');
  }

  public function getProperties($postcode) {
    return $this->getFixture('properties.json');
  }

  private function getFixture($filename) {
    $fixtures_path = Drupal\Core\Extension\ExtensionPathResolver::getPath('module', 'abc_bins');
    $fixtures_path .= '/fixtures';
    $json = file_get_contents($fixtures_path . '/' . $filename);
    return json_decode($json);
  }
}

