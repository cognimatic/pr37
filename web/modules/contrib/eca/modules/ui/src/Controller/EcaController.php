<?php

namespace Drupal\eca_ui\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\MessageCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Url;
use Drupal\eca\Service\Modellers;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class Eca.
 *
 * @package Drupal\eca\Controller
 */
class EcaController extends ControllerBase {

  /**
   * Symfony request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected Request $request;

  /**
   * ECA modeller service.
   *
   * @var \Drupal\eca\Service\Modellers
   */
  protected Modellers $modellerServices;

  /**
   * Entity storage manager.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected EntityStorageInterface $storage;

  /**
   * ECA controller constructor.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The symfony request.
   * @param \Drupal\eca\Service\Modellers $modeller_services
   *   The ECA modeller service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(Request $request, Modellers $modeller_services, EntityTypeManagerInterface $entity_type_manager) {
    $this->request = $request;
    $this->modellerServices = $modeller_services;
    $this->storage = $entity_type_manager->getStorage('eca');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): EcaController {
    return new static(
      $container->get('request_stack')->getCurrentRequest(),
      $container->get('eca.service.modeller'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * Enable the given ECA entity if disabled.
   *
   * @param string $eca
   *   The ID of the ECA entity to enable.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirect response to go to the ECA collection page.
   */
  public function enable(string $eca): RedirectResponse {
    /** @var \Drupal\eca\Entity\Eca $config */
    if (($config = $this->storage->load($eca)) && !$config->status() && $modeller = $config->getModeller()) {
      $modeller->enable();
    }
    return new RedirectResponse(Url::fromRoute('entity.eca.collection')->toString());
  }

  /**
   * Disable the given ECA entity if enabled.
   *
   * @param string $eca
   *   The ID of the ECA entity to disable.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirect response to go to the ECA collection page.
   */
  public function disable(string $eca): RedirectResponse {
    /** @var \Drupal\eca\Entity\Eca $config */
    if (($config = $this->storage->load($eca)) && $config->status() && $modeller = $config->getModeller()) {
      $modeller->disable();
    }
    return new RedirectResponse(Url::fromRoute('entity.eca.collection')->toString());
  }

  /**
   * Clone the given ECA entity and save it as a new one.
   *
   * @param string $eca
   *   The ID of the ECA entity to clone.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirect response to go to the ECA collection page.
   */
  public function clone(string $eca): RedirectResponse {
    /** @var \Drupal\eca\Entity\Eca $config */
    if (($config = $this->storage->load($eca)) && $config->isEditable() && $modeller = $config->getModeller()) {
      $modeller->clone();
    }
    return new RedirectResponse(Url::fromRoute('entity.eca.collection')->toString());
  }

  /**
   * Export the model from the given ECA entity.
   *
   * @param string $eca
   *   The ID of the ECA entity to export.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   Redirect response to go to the ECA collection page.
   */
  public function export(string $eca): Response {
    /** @var \Drupal\eca\Entity\Eca $config */
    if (($config = $this->storage->load($eca)) && $config->isExportable() && $modeller = $config->getModeller()) {
      $response = $modeller->export();
      if ($response) {
        return $response;
      }
    }
    return new RedirectResponse(Url::fromRoute('entity.eca.collection')->toString());
  }

  /**
   * Edit the given ECA entity if the modeller supports that.
   *
   * @param string $eca
   *   The ID of the ECA entity to edit.
   *
   * @return array
   *   The render array for editing the ECA entity.
   */
  public function edit(string $eca): array {
    /** @var \Drupal\eca\Entity\Eca $config */
    if (($config = $this->storage->load($eca)) && $config->isEditable() && $modeller = $config->getModeller()) {
      $build = $modeller->edit();
      $build['#title'] = $this->t('%label ECA Model', ['%label' => $config->label()]);
      return $build;
    }
    return [];
  }

  /**
   * Ajax callback to save an ECA model with a given modeller.
   *
   * @param string $modeller_id
   *   The plugin ID of the modeller that's being used for the posted model.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   An Ajax response object containing the message indicating the success of
   *   the save operation and if this is a new ECA entity to be saved, also
   *   containing a redirect instruction to the edit page of that entity.
   */
  public function save(string $modeller_id): AjaxResponse {
    $response = new AjaxResponse();
    if ($modeller = $this->modellerServices->getModeller($modeller_id)) {
      try {
        if ($modeller->save($this->request->getContent())) {
          $editUrl = Url::fromRoute('entity.eca.edit_form', ['eca' => mb_strtolower($modeller->getId())], ['absolute' => TRUE])->toString();
          $response->addCommand(new RedirectCommand($editUrl));
        }
        $message = new MessageCommand('Successfully saved the model.', NULL, [
          'type' => 'status',
        ]);
      }
      catch (\Exception $ex) {
        // @todo Log details about the exception.
        $message = new MessageCommand($ex->getMessage(), NULL, [
          'type' => 'error',
        ]);
      }
    }
    else {
      $message = new MessageCommand('Invalid modeller ID.', NULL, [
        'type' => 'error',
      ]);
    }
    $response->addCommand($message);
    foreach ($this->messenger()->all() as $type => $messages) {
      foreach ($messages as $message) {
        $response->addCommand(new MessageCommand($message, NULL, ['type' => $type], FALSE));
      }
    }
    return $response;
  }

}
