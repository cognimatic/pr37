<?php

namespace Drupal\abc_contact_center\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a live chat and va availability block.
 *
 * @Block(
 *   id = "abc_contact_center_live_chat_and_va_availability",
 *   admin_label = @Translation("Live Chat and VA availability"),
 *   category = @Translation("Custom")
 * )
 */
class LiveChatAndVaAvailabilityBlock extends BlockBase {

  function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->settings = \Drupal::config('abc_contact_center.settings');
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
   * Allow block to cache until next open/closed transition.
   * @return int
   */
  public function getCacheMaxAge() {
    $next_transition = $this->callCenterIsOpen()
      ? $this->settings->get('closing_time')
      : $this->settings->get('opening_time');

    $current_time = date('H:i'); // The time, HH:MM

    // If time has already passed, this must mean tomorrow.
    // (this is safe even if we don't actually open tomorrow)
    $day = $current_time > $next_transition
      ? 'tomorrow'
      : 'today';

    $next_transition_dt = new \Datetime($day . ' ' . $next_transition, new \DateTimeZone('Europe/London'));
    return $next_transition_dt->getTimestamp() - time();
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    // Display conditions
    if ( !$this->callCenterIsOpen()) {
      $content = "<a href='http://argyllandbute.custhelp.com/app/ask'><img src='https://www.argyll-bute.gov.uk/sites/default/files/online_help_blue.gif' alt='smart assistant' /></a>";
    }
    else {
      $content = "<div id='chatbtn'><form action='' method='post'>
    <input type='image'  src='https://www.argyll-bute.gov.uk/sites/default/files/online_help_blue.gif' alt='Live chat with an adviser' value='Live chat with an adviser' />
    <input type='hidden' name='chatrequest' value='1' /><br>
   </form></div>";
    }

    $build['content'] = [
      '#markup' => $content,
      '#attached' => [
        'library' => [
          'abc_contact_center/abc_contact_center',
          'abc_contact_center/abc_contact_center_rightnow',
        ],
      ],
    ];
    return $build;
  }

}
