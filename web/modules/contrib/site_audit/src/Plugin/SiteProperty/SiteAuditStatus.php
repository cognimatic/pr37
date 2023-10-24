<?php

namespace Drupal\site_audit\Plugin\SiteProperty;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Url;
use Drupal\site\Entity\SiteDefinition;
use Drupal\site\SitePropertyPluginBase;
use Drupal\site_audit\Plugin\SiteAuditCheckBase;
use Drupal\site_audit\Plugin\SiteAuditChecklistBase;

/**
 * Plugin implementation of the site_property.
 *
 * @SiteProperty(
 *   id = "site_audit_status",
 *   name = "site_audit_status",
 *   hidden = true,
 *   label = @Translation("Site Audit Status"),
 *   description = @Translation("The state of Site Audit Report.")
 * )
 */
class SiteAuditStatus extends SitePropertyPluginBase {

  protected $site_audit_state_map = [
    SiteAuditCheckBase::AUDIT_CHECK_SCORE_PASS => SiteDefinition::SITE_OK,
    SiteAuditCheckBase::AUDIT_CHECK_SCORE_INFO => SiteDefinition::SITE_INFO,
    SiteAuditCheckBase::AUDIT_CHECK_SCORE_WARN => SiteDefinition::SITE_WARN,
    SiteAuditCheckBase::AUDIT_CHECK_SCORE_FAIL => SiteDefinition::SITE_ERROR,
  ];


  public function state(SiteDefinition $site) {
    if (!in_array('site_audit', $site->get('state_factors'))) {
      return;
    }
    $audit_manager = \Drupal::service('plugin.manager.site_audit_checklist');
    $requirements_options = $site->get('third_party_settings')['site_audit']['site_audit_required'] ?? [];

    $reasons[] = $site->get('reason');
    $worst_state = SiteAuditCheckBase::AUDIT_CHECK_SCORE_INFO;
    foreach (array_filter($requirements_options) as $required_report) {
      /**
       * @var SiteAuditChecklistBase
       */
      $report = $audit_manager->createInstance($required_report);

      if ($report->severity < SiteAuditCheckBase::AUDIT_CHECK_SCORE_PASS) {

        $reason_build = [
          '#type' => 'details',
          '#title' => t('Site Audit Report ":requirement" returned a :warning.', [
            ':warning' => SiteDefinition::getStateName($this->site_audit_state_map[$report->severity]),
            ':requirement' => $report->getLabel(),
          ]),
          '#description' => t('See <a href=":url">Site Audit: :type</a>', [
            ':url' => Url::fromRoute('site_audit.report')->setAbsolute()->setOptions([
              'fragment' => $required_report,
            ])->toString(),
            ':type' => $report->getLabel(),
          ]),
          '#description_display' => 'after',
        ];

        foreach ($report->getCheckObjects() as $check) {
          $checkBuild = [];
          if ($check->getScore() < SiteAuditCheckBase::AUDIT_CHECK_SCORE_PASS)
          if (is_array($check->getResult())) {
            $checkBuild['result'] = $check->getResult();
            $checkBuild['result']['#attributes']['class'] = 'well result';
          }
          else {
            $checkBuild['detail'] = [
              '#type' => 'html_tag',
              '#tag' => 'p',
              '#value' => $check->getResult(),
              '#attributes' => [
                'class' => 'well result',
              ],
            ];
          }

          // Action.
          if ($action = $check->renderAction()) {
            $checkBuild['action'] = [
              '#type' => 'html_tag',
              '#tag' => 'div',
              '#attributes' => [
                'class' => 'well action',
              ],
            ];
            if (!is_array($action)) {
              $checkBuild['action']['text'] = [
                '#type' => 'html_tag',
                '#tag' => 'p',
                '#value' => $action,
              ];
            }
            else {
              $checkBuild['action']['rendered'] = $action;
            }
          }
          $reason_build[$check->getPluginId()] = $checkBuild;
        }

        // @TODO: Dig through the checks to print out the problem and solution.
        $reasons[] = $reason_build;
      }

      if ($report->severity < $worst_state) {
        $worst_state = $report->severity;
      }
    }

    $site->addReason($reasons);
    return $this->site_audit_state_map[$worst_state];
  }
}
