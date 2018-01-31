<?php

namespace Drupal\rules\Plugin\Condition;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\rules\Core\RulesConditionBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Entity has field' condition.
 *
 * @Condition(
 *   id = "rules_entity_has_field",
 *   label = @Translation("Entity has field"),
 *   category = @Translation("Entity"),
 *   context = {
 *     "entity" = @ContextDefinition("entity",
 *       label = @Translation("Entity"),
 *       description = @Translation("Specifies the entity for which to evaluate the condition.")
 *     ),
 *     "field" = @ContextDefinition("string",
 *       label = @Translation("Field"),
 *       description = @Translation("The name of the field to check for."),
 *       list_options_callback = "fieldListOptions"
 *     )
 *   }
 * )
 *
 * @todo: Add access callback information from Drupal 7.
 */
class EntityHasField extends RulesConditionBase implements ContainerFactoryPluginInterface {

  /**
   * The entity_field.manager service.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * Constructs an EntityHasField object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity_field.manager service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityFieldManagerInterface $entity_field_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
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
      $container->get('entity_field.manager')
    );
  }

  /**
   * Checks if a given entity has a given field.
   *
   * @param \Drupal\Core\Entity\FieldableEntityInterface $entity
   *   The entity to check for the provided field.
   * @param string $field
   *   The field to check for on the entity.
   *
   * @return bool
   *   TRUE if the provided entity has the provided field.
   */
  protected function doEvaluate(FieldableEntityInterface $entity, $field) {
    return $entity->hasField($field);
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

}
