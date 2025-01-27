<?php

namespace Drupal\xhprof\XHProfLib;

use Drupal\xhprof\XHProfLib\Storage\FileStorage;

class Aggregator {

  /**
   * @var array
   */
  public $runs = [];

  /**
   * An instance of a class that implements XHProfRunsInterface.
   */
  protected $xhprof_runs_class;

  public function __construct() {
    $this->xhprof_runs = new FileStorage();
  }

  /**
   * @param $run_id
   * @param $namespace
   *
   * @return void
   */
  public function addRun($run_id, $namespace) {
    $this->runs[] = ['run_id' => $run_id, 'namespace' => $namespace];
  }

  /**
   * @return array
   */
  public function average() {
    $keys = [];
    foreach ($this->runs as $data) {
      $keys = $keys + array_keys($data);
    }
    $agg_run = [];
    $run_count = count($this->runs);
    foreach ($keys as $key) {
      $agg_key = [];
      // Check which runs have this parent_child function key, collect metrics if so.
      foreach ($this->runs as $data) {
        if (isset($data[$key])) {
          foreach ($data[$key] as $metric => $val) {
            $agg_key[$metric][] = $val;
          }
        }
      }

      // Average each metric for the key into the aggregated run.
      $agg_run[$key] = [];
      foreach ($agg_key as $metric => $vals) {
        $sd = self::sd($vals);
        $mean = (array_sum($vals) / count($vals));
        $good_vals = [];

        if ($sd == 0) {
          $agg_run[$key][$metric] = (array_sum($vals) / count($vals));
        }
        else {
          foreach ($vals as $v) {
            $diff = abs($mean - $v);
            if (abs($mean - $v) < ($sd * 2)) {
              $good_vals[] = $v;
            }
          }
          $agg_run[$key][$metric] = (array_sum($good_vals) / count($good_vals));
        }
      }
    }

    return $agg_run;
  }


  /**
   * @param bool $skip_bad_runs
   *  Set to TRUE to prevent this method from failing in the case of a bad run
   *  (i.e. a corrupt, unserializable file).
   *
   * @return array
   */
  public function sum($skip_bad_runs = FALSE) {
    $keys = [];
    $agg_run = [];
    foreach ($this->runs as $run) {
      try {
        $run = $this->xhprof_runs->getRun($run['run_id'], $run['namespace']);
      }
      catch (\UnexpectedValueException $e) {
        if ($skip_bad_runs) {
          continue;
        }
        else {
          throw $e;
        }
      }
      $keys = $run->getKeys();

      foreach ($keys as $key) {
        foreach ($run->getMetrics($key) as $metric => $val) {
          if (isset($agg_run[$key][$metric])) {
            $agg_run[$key][$metric] += $val;
          }
          else {
            $agg_run[$key][$metric] = $val;
          }
        }
      }
    }
    return $agg_run;
  }

  public static function sd_square($x, $mean) {
    return pow($x - $mean, 2);
  }

  /**
   * Function to calculate standard deviation (uses sd_square)
   */
  public static function sd($array) {
    // square root of sum of squares devided by N-1
    return sqrt(
      array_sum(
        array_map([
          'XHProfTools',
          'sd_square',
        ], $array, array_fill(0, count($array), (array_sum($array) / count($array))))) / (count($array) - 1)
    );
  }

}
