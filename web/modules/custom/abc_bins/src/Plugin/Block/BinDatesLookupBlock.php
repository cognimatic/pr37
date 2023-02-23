<?php

namespace Drupal\abc_bins\Plugin\Block;

use Drupal\abc_bins\WebserviceInterface;
use Drupal\Component\Utility\Html;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a bin dates lookup block.
 *
 * @Block(
 *   id = "abc_bins_bin_dates_lookup",
 *   admin_label = @Translation("Bin dates lookup"),
 *   category = @Translation("Custom")
 * )
 */
class BinDatesLookupBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\abc_bins\Webservice
   */
  protected $service;

  /**
   * @var array Values read from $_POST.
   */
  protected $values;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, WebserviceInterface $service) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->service = $service;
  }

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   *
   * @return static
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('abc_bins.webservice')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    $this->sanitizeParams();

    $mode = '';
    $calendarName = '';
    $nextCollections = [];
    $route = '';

    if (!empty($this->values['addressSelect'])) {
      $bindata = $this->service->getDatesByUprn($this->values['addressSelect']);

      if (!empty($bindata[0]->collectiondate)){
        $route = $bindata[0]->route;
        $currentCalendar = $this->service->getRoute($route);
        $calendarName = $currentCalendar[0]->calendarname;

        // Write .ics file to filesystem (whilst building a block?! - this would be better as a background job or menu callback)
        $this->writeIcal($route, $calendarName);

        // May be multiple collections on the same day for different waste types.
        $nextCollectionDate = $bindata[0]->collectiondate;
        $nextCollections = array_filter($bindata, function($item) use ($nextCollectionDate) {
          return $item->collectiondate == $nextCollectionDate;
        });
        $mode = 'uprn';
      }
      else {
        if ($this->values['postcode']) {
          $collections = $this->service->getProperties($this->values['postcode']);
          $route = $collections[0]->routename;
          $calendarName = $this->service->getRoute($route);
          $mode = "postcode";
        }
      }
    }


    $build['content']['form'] = [
      'form' => [
        '#type' => 'inline_template',
        '#template' => '{{ form|raw }}',
        '#context' => [
          'form' => $this->build_bin_form(),
        ]
      ]
    ];

    if ($mode) {
      $build['content']['details'] = [
        '#theme' => 'bin_lookup_details',
        '#mode' => $mode,
        '#calendarname' => $calendarName,
        '#route' => $route,
        '#nextCollections' => $nextCollections,
        '#bindays' => $bindata,
      ];
    }

    $build['content']['#attached'] = [
      'library' => [
        'abc_bins/abc_bins',
      ],
    ];

    return $build;
  }

  function label() {
    return 'Find your bin collection calendar by entering your postcode';
  }

  function writeIcal($routeName, $calendarName) {
    //format dates into icalander
    $allRouteDates = $this->service->getDatesByRoute($routeName);
    //print_r($allRouteDates);
    $icalString = "BEGIN:VCALENDAR\r
VERSION:2.0\r
PRODID:-//Argyll and Bute Council//Bin Collection dates for route " . $routeName . " //EN\r
NAME:Bin Collection days\r
X-WR-CALNAME:Bin Collection days\r
DESCRIPTION:Bin Collection dates for route " . $routeName . "\r
X-WR-CALDESC:Bin Collection dates for route " . $routeName . "\r
CALSCALE:GREGORIAN\r
METHOD:PUBLISH\r";

    foreach ($allRouteDates as $icalEntry) {
      $today = date('Y-m-d');
      $todayTime = time();
      $ical_bin_date = date('Ymd', strtotime($icalEntry->collectiondate));
      $ical_bin_date .= "T060000";
      $ical_bin_end_date = date('Ymd', strtotime($icalEntry->collectiondate));
      $ical_bin_end_date .= "T230000";
      $binTime = strtotime($icalEntry->collectiondate);
      if ($binTime >= $todayTime) {
        $icalString .= "BEGIN:VEVENT\rUID:" . $ical_bin_date . "-" . rand() . "@argyll-bute.gov.uk\rDTSTAMP:" . $ical_bin_date . "\rSUMMARY:" . $icalEntry->wastetype . " bin collection today. \rDTSTART:" . $ical_bin_date . "\rDTEND:" . $ical_bin_end_date . "\r";
        //echo "<tr class='".$icalEntry->wastetype."''><td>". $icalEntry->wastetype . "</td><td>" . $ical_bin_date . "</td></tr>";};
        $icalString .= "END:VEVENT\r";
      }
    }
    $icalString .= "END:VCALENDAR";
    //echo $icalanderString;

    $handle = fopen("public://binroutes/2019/" . $calendarName . ".ics","w") or die ("Unable to open file!");
    fwrite($handle, $icalString);
    fclose($handle);
  }

  protected function sanitizeParams() {

    $this->values = [
      'postcode' => NULL,
      'addressSelect' => NULL,
    ];

    if (!empty($_POST['postcode'])) {
      $postcode = trim($_POST['postcode']);
      if (substr($postcode, -4, 1) != " ") {
        $postcode = substr($postcode, 0, strlen($postcode) - 3) . " " . substr($postcode, -3);
      }
      $this->values['postcode'] = $postcode;
    }

    if (!empty($_POST['addressSelect'])) {
      $this->values['addressSelect'] = filter_var($_POST['addressSelect'], FILTER_SANITIZE_NUMBER_INT);
    }
  }

  function selected($uprnValue) {
    if (isset($this->values['addressSelect'])) {
      if ($this->values['addressSelect'] == $uprnValue) {
        $selectedVal = "selected";
      } else {
        $selectedVal = "";
      };
      return $selectedVal;
    }
  }

  /**
   * Return HTML soup for interactive part of bin form.
   * @return string
   */
  function build_bin_form() {

    $postcode = '';
    if (!empty($this->values['postcode'])) {
      $postcode = Html::escape($this->values['postcode']);
      $pcdata = $this->service->getProperties($this->values['postcode']);
    }

    $bin_content = '';
    $bin_content = '<form name="form" method="POST" onsubmit="ga(\'send\', \'event\', { eventCategory: \'bin submit\', eventAction: \'click\', eventLabel: \'bin lookup\'});">
                      <div class="form-group">
                        <div class="input-group">
                          <div class="input-group-prepend">
                            <span class="input-group-text"><span class="fa fa-search" aria-hidden="true"></span></span>
                          </div>
                          <input type="text" name="postcode" id="postcode" placeholder="Search for your postcode" class="form-control"  title="Enter your postcode" value="' . $postcode . '">
                        </div>
                      </div>';

    if ($postcode && empty($pcdata)) {
      $bin_content .= "<div class='alert alert-warning'>Unable to get information for this postcode. Please check you have entered it correctly.</div>";
    }
    elseif ($postcode && $pcdata) {
      usort($pcdata, function ($a, $b) {
        return strnatcmp($a->fulladdress, $b->fulladdress);
      });
      $bin_content .= "<div class='form-group'><select name='addressSelect' id='addressSelect' class='form-control'>";
      foreach ($pcdata as $property) {
        $bin_content .= "<option value='" . $property->caguprn . "' " . $this->selected($property->caguprn) . ">" . $property->fulladdress . "</option>";
      };
      $bin_content .= "</select></div>";
    }

    $bin_content .= '<button class="btn-submit" type="submit" value="submit">Search for my bin collection details</button></form>';

    return $bin_content;
  }
}

