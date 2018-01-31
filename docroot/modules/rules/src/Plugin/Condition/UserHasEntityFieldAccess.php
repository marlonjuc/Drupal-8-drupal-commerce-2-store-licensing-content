<?php

namespace Drupal\rules\Plugin\Condition;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\rules\Core\RulesConditionBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'User has entity field access' condition.
 *
 * @Condition(
 *   id = "rules_entity_field_access",
 *   label = @Translation("User has entity field access"),
 *   category = @Translation("User"),
 *   context = {
 *     "user" = @ContextDefinition("entity:user",
 *       label = @Translation("User")
 *     ),
 *     "entity" = @ContextDefinition("entity",
 *       label = @Translation("Entity")
 *     ),
 *     "field" = @ContextDefinition("string",
 *       label = @Translation("Field"),
 *       description = @Translation("The name of the field to check."),
 *       list_options_callback = "fieldListOptions"
 *     ),
 *     "operation" = @ContextDefinition("string",
 *       label = @Translation("Operation"),
 *       description = @Translation("The access to check for."),
 *       list_options_callback = "accessListOptions",
 *       default_value = "view",
 *     ),
 *   }
 * )
 *
 * @todo: Add access callback information from Drupal 7.
 */
class UserHasEntityFieldAccess extends RulesConditionBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity_field.manager service.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * Constructs a UserHasEntityFieldAccess object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity_type.manager service.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity_field.manager service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, EntityFieldManagerInterface $entity_field_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->entityFieldManager = $entity_field_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('entity_field.manager')
    );
  }

  /**
   * Evaluate if the user has access to the field of an entity.
   *
   * @param \Drupal\Core\Session\AccountInterface $user
   *   The user account to test access against.
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity to check access on.
   * @param string $field
   *   The name of the field to check access on.
   * @param string $operation
   *   The operation access should be checked for. Usually one of "view" or
   *   "edit".
   *
   * @return bool
   *   TRUE if the user has access to the field on the entity, FALSE otherwise.
   */
  protected function doEvaluate(AccountInterface $user, ContentEntityInterface $entity, $field, $operation) {
    if (!$entity->hasField($field)) {
      return FALSE;
    }

    $access = $this->entityTypeManager->getAccessControlHandler($entity->getEntityTypeId());
    if (!$access->access($entity, $operation, $user)) {
      return FALSE;
    }

    $definition = $entity->getFieldDefinition($field);
    $items = $entity->get($field);
    return $access->fieldAccess($operation, $definition, $user, $items);
  }

  /**
   * Returns all the available fields in the system.
   *
   * @return array
   *   An array of field names keyed on the field name.
   */
  public function fieldListOptions() {
    $options = [];

    // Load all the fields in the system.
    $fields = $this->entityFieldManager->getFieldMap();

    // Add each field to our options array.
    foreach ($fields as $entity_fields) {
      foreach ($entity_fields as $field_name => $field) {
        $options[$field_name] = $field_name;
      }
    }
    // Sort the field names for ease of locating and selecting.
    asort($options);

    return $options;
  }

  /**
   * Returns the types of field access to check for.
   *
   * @return array
   *   An array of access types.
   */
  public function accessListOptions() {
    return ['view' => $this->t('View'), 'edit' => $this->t('Edit')];
  }

}
