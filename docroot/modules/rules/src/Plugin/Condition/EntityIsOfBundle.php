<?php

namespace Drupal\rules\Plugin\Condition;

use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\rules\Core\RulesConditionBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an 'Entity is of bundle' condition.
 *
 * @Condition(
 *   id = "rules_entity_is_of_bundle",
 *   label = @Translation("Entity is of bundle"),
 *   category = @Translation("Entity"),
 *   context = {
 *     "entity" = @ContextDefinition("entity",
 *       label = @Translation("Entity"),
 *       description = @Translation("Specifies the entity for which to evaluate the condition."),
 *       assignment_restriction = "selector",
 *     ),
 *     "type" = @ContextDefinition("string",
 *       label = @Translation("Type"),
 *       description = @Translation("The type of the evaluated entity."),
 *       list_options_callback = "entityTypesListOptions"
 *     ),
 *     "bundle" = @ContextDefinition("string",
 *       label = @Translation("Bundle"),
 *       description = @Translation("The bundle of the evaluated entity."),
 *       list_options_callback = "bundleListOptions"
 *     )
 *   }
 * )
 *
 * @todo: Add access callback information from Drupal 7?
 */
class EntityIsOfBundle extends RulesConditionBase implements ContainerFactoryPluginInterface {

  /**
   * The entity_type.manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs an EntityIsOfBundle object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity_type.manager service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * Check if a provided entity is of a specific type and bundle.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to check the bundle and type of.
   * @param string $type
   *   The type to check for.
   * @param string $bundle
   *   The bundle to check for.
   *
   * @return bool
   *   TRUE if the provided entity is of the provided type and bundle.
   */
  protected function doEvaluate(EntityInterface $entity, $type, $bundle) {
    $entity_type = $entity->getEntityTypeId();
    $entity_bundle = $entity->bundle();

    // Check to see whether the entity's bundle and type match the specified
    // values.
    return $entity_bundle == $bundle && $entity_type == $type;
  }

  /**
   * {@inheritdoc}
   */
  public function assertMetadata(array $selected_data) {
    // Assert the checked bundle.
    $changed_definitions = [];
    if (isset($selected_data['entity']) && $bundle = $this->getContextValue('bundle')) {
      $changed_definitions['entity'] = clone $selected_data['entity'];
      $bundles = is_array($bundle) ? $bundle : [$bundle];
      $changed_definitions['entity']->setBundles($bundles);
    }
    return $changed_definitions;
  }

  /**
   * Returns an array of entity types that exist in the system.
   *
   * @return array
   *   An array of entity types keyed on the entity type machine name.
   */
  public function entityTypesListOptions() {
    $options = [];

    $entity_types = $this->entityTypeManager->getDefinitions();

    foreach ($entity_types as $entity_type) {
      if (!$entity_type instanceof ContentEntityTypeInterface) {
        continue;
      }

      $options[$entity_type->id()] = $entity_type->getLabel();
      // If the id differs from the label add the id in brackets for clarity.
      if (strtolower(str_replace('_', ' ', $entity_type->id())) != strtolower($entity_type->getLabel())) {
        $options[$entity_type->id()] .= ' (' . $entity_type->id() . ')';
      }
    }

    return $options;
  }

  /**
   * Returns an array of entity bundles options.
   *
   * @return array
   *   An array of bundles keyed on the bundle machine name.
   */
  public function bundleListOptions() {
    $options = [];

    $entity_types = $this->entityTypeManager->getDefinitions();

    foreach ($entity_types as $entity_type) {
      if ($bundle_entity_type = $entity_type->getBundleEntityType()) {
        foreach ($this->entityTypeManager->getStorage($bundle_entity_type)->loadMultiple() as $entity) {
          $options[$entity->id()] = $entity->label();
          // If the id differs from the label add the id in brackets.
          if (strtolower(str_replace('_', ' ', $entity->id())) != strtolower($entity->label())) {
            $options[$entity->id()] .= ' (' . $entity->id() . ')';
          }
        }
      }
    }

    return $options;
  }

}
