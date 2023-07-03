<?php

namespace Drupal\simplesamlphp_custom_attributes\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * {@inheritdoc}
 */
class SimplesamlphpCustomAttributesController extends ControllerBase {

  /**
   * Mapping settings.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $mappingConfig;

  /**
   * Used to get field names for user entity.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityFieldManagerInterface $entity_field_manager) {
    $this->mappingConfig = $this->config('simplesamlphp_custom_attributes.mappings');
    $this->entityFieldManager = $entity_field_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_field.manager')
    );
  }

  /**
   * Creates the mapping table page.
   *
   * @return array
   *   Table.
   */
  public function ssoMappings() {
    // Load the mappings.
    $mappings = $this->mappingConfig->get('mappings');
    // Load the user fields.
    $fields = $this->entityFieldManager->getFieldDefinitions('user', 'user');

    // Set up the table.
    $table = [
      '#theme' => 'table',
      '#header' => [
        $this->t('SAML Attribute'),
        $this->t('User Field'),
        $this->t('Operations'),
      ],
      '#sticky' => TRUE,
      '#empty' => $this->t("There are no mappings. You can add one using the link above."),
    ];

    // If there are mapping, process them.
    if ($mappings) {
      foreach ($mappings as $id => $mapping) {
        // If this is a custom mapping, specify the correct label for it.
        if ($mapping['field_name'] === 'custom') {
          $user_field = $this->t('Custom');
        }
        // Otherwise get the label from the field config.
        else {
          if (isset($fields[$mapping['field_name']])) {
            $user_field = $fields[$mapping['field_name']]->getLabel();
          }
          else {
            $user_field = $this->t('Missing field: %field', [
              '%field' => $mapping['field_name'],
            ]);
          }
        }

        // Set up the operations dropbutton.
        $operations = [
          '#type' => 'dropbutton',
          '#links' => [
            'edit' => [
              'title' => $this->t('edit'),
              'url' => Url::fromRoute('simplesamlphp_custom_attributes.edit', ['mapping' => $id]),
            ],
            'delete' => [
              'title' => $this->t('delete'),
              'url' => Url::fromRoute('simplesamlphp_custom_attributes.delete', ['mapping' => $id]),
            ],
          ],
        ];

        // Add the row to the table.
        $table['#rows'][$id] = [
          'saml_attribute' => $mapping['attribute_name'],
          'user_field' => $user_field,
          'operations' => render($operations),
        ];
      }
    }

    return $table;
  }

}
