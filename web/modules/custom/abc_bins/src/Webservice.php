<?php

namespace Drupal\abc_bins;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;

/**
 * Webservice service.
 */
class Webservice implements WebserviceInterface {

  /**
   * The HTTP client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * Constructs a Webservice object.
   *
   * @param \GuzzleHttp\ClientInterface $http_client
   *   The HTTP client.
   */
  public function __construct(ClientInterface $http_client) {
    $this->httpClient = $http_client;
  }

  /**
   * @param $routeName
   */
  public function getDatesByRoute($routeName) {
    return $this->callToBinapp("/routes/" . $routeName . "/dates");
  }

  public function getDatesByUprn($uprn) {
    return $this->callToBinapp("/properties/uprn/" . $uprn . "/dates");
  }

  public function getRoute($routeName) {
    return $this->callToBinapp("/routes?routename=". $routeName);
  }

  public function getProperties($postcode) {
    return $this->callToBinapp("/properties/postcode/" . rawurlencode($postcode));
  }

  protected function callToBinapp($path) {
    $config = \Drupal::config('abc_bins.settings');
    $data = '';

    $url = $config->get('bins_api_endpoint') . $path;
    try {
      $response = $this->httpClient->get($url);
      $data = json_decode($response->getBody());
    }
    catch (RequestException $e) {
      watchdog_exception('abc_bins', $e);
    }

    return $data;
  }

}
