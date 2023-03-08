<?php

namespace Drupal\abc_status_1\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Provides a block to render a summery of key service statuses
 *
 * @Block(
 *   id = "service_status_1_lights",
 *   admin_label = @Translation("Service Status traffic lights"),
 *   category = @Translation("Service Status"),
 * )
 */
class ServiceStatus1 extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $list = "<div class='service-status-tl'>";
       
    $list .= "<ul>";
    
    
    
    /** 
     * Find all Services that should show status on home page
     * then find highest current (published + shown on landing page) status
     */
    
    $db = \Drupal::database();
    $services = $db->query("SELECT entity_id, title FROM node__field_show_alert_status_on_home_, node_field_data WHERE field_show_alert_status_on_home__value = 1 AND status = 1 AND entity_id = nid ORDER BY title");    
    foreach ($services AS $service) {
      $service_id = $service->entity_id;
      $service_name = $service->title;
      $service_status = "<i class='status-normal fa-sharp fa-solid fa-circle-check'></i>";
      $service_link = Link::fromTextAndUrl($service_name, Url::fromRoute('entity.node.canonical', ['node' => $service_id]))->toString();
      
      //Find most extreme related, valid status for current service
      $status_query = $db->select('node__localgov_service_status' , 's');
      $status_query->join('node__localgov_services_parent', 'p', 'p.entity_id = s.entity_id');
      $status_query->join('node_field_data', 'n', 'p.entity_id = n.nid');
      $status_query
          ->fields('s', array('localgov_service_status_value'))
          ->condition('p.localgov_services_parent_target_id', $service_id)
          ->condition('n.status', 1)
          ->orderBy('s.localgov_service_status_value', 'ASC')
          ->range(0,1);
      //Take first value of results
      $status_result = $status_query->execute()->fetchField(0);
      
      if(isset($status_result)){
        if($status_result == '0-severe-impact'){
          $service_status = "<i class='status-severe fa-sharp fa-solid fa-triangle-exclamation'>Severe</i>";
        } elseif ($status_result == '1-has-issues'){
          $service_status = "<i class='status-issues fa-solid fa-circle-info'>Issues</i>";
        }
      }
      
      $list .= "<li>" . $service_status . " " . $service_link . "</li>";
    }
   
    $list .= "</ul>";
    
    $list .= "<p><a href='/service-status' title='More detail about status updates across Argyll and Bute'>Detailed status updates</a></p>";
    $list .= "</div>";
    return [
      '#type' => 'markup',
      '#markup' => $list,
    ];
  }

}
