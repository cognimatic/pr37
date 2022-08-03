<?php

namespace Drupal\eca_misc\Plugin\Action;

use Drupal\Core\Access\AccessibleInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\eca\Plugin\Action\ConfigurableActionBase;
use Drupal\eca_misc\Plugin\RouteInterface;
use Drupal\eca_misc\Plugin\RouteTrait;

/**
 * Load an entity into the token environment.
 *
 * @Action(
 *   id = "eca_token_load_route_param",
 *   label = @Translation("Token: load route parameter")
 * )
 */
class TokenLoadRouteParameter extends ConfigurableActionBase {

  use RouteTrait;

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    $allowed = FALSE;
    if ($parameter = $this->getRouteMatch()->getParameter($this->configuration['parameter_name'])) {
      $allowed = TRUE;
      if ($parameter instanceof AccessibleInterface) {
        $allowed = $parameter->access('view', $account);
      }
    }
    $result = AccessResult::allowedIf($allowed);
    return $return_as_object ? $result : $result->isAllowed();
  }

  /**
   * {@inheritdoc}
   */
  public function execute(): void {
    if ($parameter = $this->getRouteMatch()->getParameter($this->configuration['parameter_name'])) {
      $tokenName = empty($this->configuration['token_name']) ? $this->tokenServices->getTokenType($parameter) : $this->configuration['token_name'];
      if ($tokenName) {
        $this->tokenServices->addTokenData($tokenName, $parameter);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'token_name' => '',
      'request' => RouteInterface::ROUTE_CURRENT,
      'parameter_name' => '',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildConfigurationForm($form, $form_state);
    $this->requestFormField($form);
    $form['parameter_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name of route parameter'),
      '#default_value' => $this->configuration['parameter_name'],
      '#weight' => -20,
    ];
    $form['token_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name of token'),
      '#default_value' => $this->configuration['token_name'],
      '#weight' => -10,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    $this->configuration['token_name'] = $form_state->getValue('token_name');
    $this->configuration['request'] = $form_state->getValue('request');
    $this->configuration['parameter_name'] = $form_state->getValue('parameter_name');
    parent::submitConfigurationForm($form, $form_state);
  }

}
