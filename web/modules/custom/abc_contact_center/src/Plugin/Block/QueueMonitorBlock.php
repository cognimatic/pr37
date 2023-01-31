<?php

namespace Drupal\abc_contact_center\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use GuzzleHttp\Exception\RequestException;

/**
 * Provides a call centre queue monitor block.
 *
 * @Block(
 *   id = "abc_contact_center_queue_monitor",
 *   admin_label = @Translation("Call Centre Queue Monitor"),
 *   category = @Translation("Custom")
 * )
 */
class QueueMonitorBlock extends BlockBase {

  /**
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $settings;

  function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->settings = \Drupal::config('abc_contact_center.settings');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    $build['content'] = [
      '#attached' => [
        'library' => [
          'abc_contact_center/abc_contact_center',
        ],
      ],
    ];

    $build['content']['queue'] = ['#markup' => $this->callcentervolumes()];
    $build['content']['markup'] = [
      '#type' => 'processed_text',
      '#text' => $this->settings->get('contact_block_markup.value'),
      '#format' => $this->settings->get('contact_block_markup.format'),
    ];
    return $build;
  }

  /**
   * Is current time within configured opening hours?
   * @return bool
   */
  protected function callCenterIsOpen() {
    //get config from contact_center settings form
    $opening = $this->settings->get('opening_time');
    $closing = $this->settings->get('closing_time');

    // get the time and day and determine whether we are open or closed
    $day = date('N') ; // Day of the week; 1 = Monday, 7 = Sunday
    $time = date('H:i') ; // The time, HH:MM
    if ( $time >= $opening) { // it is after opening time
      if (( $day < 6 ) && ( $time < $closing)) {
        // it is Monday to Friday and before closing time
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Allow block to cache for 15 seconds, unless call center is closed.
   * @return int
   */
  public function getCacheMaxAge() {

    if ($this->callCenterIsOpen()) {
      return 15;
    }

    $next_transition = $this->settings->get('opening_time');
    $current_time = date('H:i'); // The time, HH:MM

    // If time has already passed, this must mean tomorrow.
    // (this is safe even if we don't actually open tomorrow)
    $day = $current_time > $next_transition
      ? 'tomorrow'
      : 'today';

    $next_transition_dt = new \Datetime($day . ' ' . $next_transition, new \DateTimeZone('Europe/London'));
    return $next_transition_dt->getTimestamp() - time();
  }

  private function callcentervolumes() {

    // check if we are open or closed and either show queue times or 'office closed' message
    if (!$this->callCenterIsOpen()) {
      $callcenter_content = "<section class='alert alert-warning'><h2>Closed</h2>Our Contact Centre is currently closed, but you can find other ways to contact us in our website.</section>" ;
    }
    else {

      $calls = $this->getCallsQueueDepth();

      $callcenter_content = '';
      if ($calls === NULL){
        $callcenter_content = "<section class='alert alert-primary'><h2>No data</h2>Sorry, cannot obtain data from the phone queue.</section>";
      }
      elseif ($calls == 0) {
        $callcenter_content = "<br>";
      }
      else if ($calls == 1) {
        $callcenter_content = "<section class='alert alert-success'><h2>Current Contact Centre Call Volumes</h2>There is currently 1 caller in the Contact Centre phone queue.</section>";
      }
      else if ($calls <= 3) {
        $callcenter_content = "<section class='alert alert-warning'><h2>Current Contact Centre Call Volumes</h2>There are currently " . $calls . " callers in the Contact Centre phone queue.</section>";
      }
      else if ($calls > 3) {
        $callcenter_content = "<section class='alert alert-danger'><h2>Current Contact Centre Call Volumes</h2>There are currently ".$calls . " callers in the Contact Centre phone queue.</section>";
      }
    }
    return $callcenter_content;
  }

  protected function getCallsQueueDepth() {
    $cid = 'abc_contact_center_queue_data';
    $data = NULL;
    if ($cache = \Drupal::cache()->get($cid)) {
      $data = $cache->data;
    }
    else {
      $url = $this->settings->get('liberty_endpoint');
      // FIXME this could do with locks to prevent simultaneous calls when cache expires(?)
      $data = $this->callToLibertyApi($url);

      // Cache for 15 seconds.
      \Drupal::cache()->set($cid, $data, time() + 15);
    }

    if (empty($data)) {
      return NULL;
    }
    else {
      return $data['callsQueuing'];
    }
  }

  /**
   * Call liberty api to get queue
   * @return array|mixed
   */
  protected function callToLibertyApi($url) {

    $data = [];
    try {
      $response = \Drupal::httpClient()->get($url);
      $data = json_decode($response->getBody(), TRUE);
    }
    catch (RequestException $e) {
      watchdog_exception('abc_contact_center', $e);
    }

    if (isset($error_msg)) {
      echo $error_msg;
    }

    return $data;
//    return json_decode('{"href": "http:\/\/argylbc-cp01\/api\/liberty\/2\/Partitions\/3\/acd\/11\/livequeuesummary", "callsQueuing":3,"longestWait":0}', TRUE);
  }

}
