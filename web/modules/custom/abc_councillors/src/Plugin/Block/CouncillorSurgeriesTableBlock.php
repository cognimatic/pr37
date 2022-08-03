<?php

namespace Drupal\abc_councillors\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use GuzzleHttp\Exception\RequestException;

/**
 * Provides a councillor surgeries table block.
 *
 * @Block(
 *   id = "abc_councillors_councillor_surgeries_table",
 *   admin_label = @Translation("Councillor Surgeries Table"),
 *   category = @Translation("Custom")
 * )
 */
class CouncillorSurgeriesTableBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $surgeryData = $this->callToApi("https://argyllandbute.custhelp.com/cc/AjaxCustom/getCouncillorSurgeries");
    $content = '';

    if (empty($surgeryData)){
      $content = "<div class='alert alert-danger'>
                    Sorry, no data found.
                    </div>";
    }
    else {
      $areas = [];

      foreach ($surgeryData as $item) {
        if (!isset($areas[$item->area]))
        {$areas[$item->area] = [];}
        $areas[$item->area][] = $item;
      }

      foreach ($areas as $key => $area_id) {
        $content .= "<h1>".$key."</h1>";
        $content .= "<table class='table table-bordered cllr-table'><thead><tr><th class='col-2'>Councillors</th><th class='col-2'>Address</th><th class='col-2'>Date</th><th class='col-4'>Details</th><th class='col-2'>Ward</th>
                    </tr></thead><tbody>";
        usort($area_id, function($a, $b) {return strnatcmp($a->ward, $b->ward);});
        foreach ($area_id as $surgeryDetail) {
          $convertedDateStart = date("j M Y", strtotime($surgeryDetail->start));
          $time_start = date("H:i", strtotime($surgeryDetail->start));
          $time_end = date("H:i", strtotime($surgeryDetail->end));
          $expirydate = date("d-m-y H:i", strtotime("+30 days"));

          $date = '<div class="date">' . $convertedDateStart . '</div>' . $time_start . " - " . $time_end;

          $tableData = "<tr><td class='col-2'><ul>";
          if ($surgeryDetail->memberOne != null){
            $tableData .= "<li>".$surgeryDetail->memberOne."</li>";
          }
          if ($surgeryDetail->memberTwo != null){
            $tableData .= "<li>".$surgeryDetail->memberTwo."</li>";
          }
          if ($surgeryDetail->memberThree != null){
            $tableData .= "<li>".$surgeryDetail->memberThree."</li>";
          }
          if ($surgeryDetail->memberFour != null){
            $tableData .= "<li>".$surgeryDetail->memberFour."</li>";
          }
          $tableData .= "</ul></td><td class='col-2'>".$surgeryDetail->address."</td>
                                          <td class='col-2'>".$date."</td>
                                          <td class='col-4'>".$surgeryDetail->details."</td>
                                          <td class='col-2'>".$surgeryDetail->ward."</td>

                                          </tr>";

          $content .= $this->detectEmail($tableData);

        }
        $content .= "</tbody></table>";
      }
    }

    $build['content'] = [
      '#markup' => $content,
      '#attached' => [
        'library' => [
          'abc_councillors/abc_councillors',
        ],
      ]
    ];
    return $build;
  }

  function callToApi($url) {
    try {
      $response = \Drupal::httpClient()->get($url);
      $data = json_decode($response->getBody());
    }
    catch (RequestException $e) {
      watchdog_exception('abc_councillors', $e);
    }

    return $data;
  }

  function detectEmail($str) {
    //Detect and create email
    $mail_pattern = "/([A-z0-9\._-]+\@[A-z0-9_-]+\.)([A-z0-9\_\-\.]{1,}[A-z])/";
    $str = preg_replace($mail_pattern, '<a href="mailto:$1$2">$1$2</a>', $str);

    return $str;
  }

}
