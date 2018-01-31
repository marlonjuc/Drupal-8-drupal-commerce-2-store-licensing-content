<?php

namespace Drupal\Tests\rules\Functional;

/**
 * Tests that a rule can be configured and triggered when a node is edited.
 *
 * @group RulesUi
 * @group legacy
 * @todo Remove the 'legacy' tag when Rules no longer uses deprecated code.
 * @see https://www.drupal.org/project/rules/issues/2922757
 */
class ConfigureAndExecuteTest extends RulesBrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['node', 'rules', 'comment', 'ban'];

  /**
   * We use the minimal profile because we want to test local action links.
   *
   * @var string
   */
  protected $profile = 'minimal';

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // Create an article content type that we will use for testing.
    $type = $this->container->get('entity_type.manager')->getStorage('node_type')
      ->create([
        'type' => 'article',
        'name' => 'Article',
      ]);
    $type->save();
    $this->container->get('router.builder')->rebuild();

    $this->account = $this->drupalCreateUser([
      'create article content',
      'administer rules',
      'administer site configuration',
    ]);
    $this->drupalLogin($this->account);
  }

  /**
   * Helper function to create a reaction rule.
   *
   * @param string $label
   *   The label for the new rule.
   * @param string $machine_name
   *   The internal machine-readable name.
   * @param string $event
   *   The name of the event to react on.
   * @param string $description
   *   Optional description for the reaction rule.
   */
  private function createRule($label, $machine_name, $event, $description = '') {
    $this->drupalGet('admin/config/workflow/rules');
    $this->clickLink('Add reaction rule');
    $this->fillField('Label', $label);
    $this->fillField('Machine-readable name', $machine_name);
    $this->fillField('React on event', $event);
    $this->fillField('Description', $description);
    $this->pressButton('Save');
  }

  /**
   * Tests creation of a rule and then triggering its execution.
   */
  public function testConfigureAndExecute() {

    // Set up a rule that will show a system message if the title of a node
    // matches "Test title".
    $this->createRule('Test rule', 'test_rule', 'rules_entity_presave:node');

    $this->clickLink('Add condition');
    $this->fillField('Condition', 'rules_data_comparison');
    $this->pressButton('Continue');

    // @todo this should not be necessary once the data context is set to
    // selector by default anyway.
    $this->pressButton('Switch to data selection');
    $this->fillField('context[data][setting]', 'node.title.0.value');

    $this->fillField('context[value][setting]', 'Test title');
    $this->pressButton('Save');

    $this->clickLink('Add action');
    $this->fillField('Action', 'rules_system_message');
    $this->pressButton('Continue');

    $this->fillField('context[message][setting]', 'Title matched "Test title"!');
    $this->fillField('context[type][setting]', 'status');
    $this->pressButton('Save');

    // One more save to permanently store the rule.
    $this->pressButton('Save');

    // Add a node now and check if our rule triggers.
    $this->drupalGet('node/add/article');
    $this->fillField('Title', 'Test title');
    $this->pressButton('Save');

    $this->assertSession()->pageTextContains('Title matched "Test title"!');

    // Edit the rule and negate the condition.
    $this->drupalGet('admin/config/workflow/rules/reactions/edit/test_rule');
    $this->clickLink('Edit', 0);
    $this->getSession()->getPage()->checkField('negate');
    $this->pressButton('Save');
    // One more save to permanently store the rule.
    $this->pressButton('Save');

    // Need to clear cache so that the edited version will be used.
    drupal_flush_all_caches();
    // Create node with same title and check that the message is not shown.
    $this->drupalGet('node/add/article');
    $this->fillField('Title', 'Test title');
    $this->pressButton('Save');
    $this->assertSession()->pageTextNotContains('Title matched "Test title"!');
  }

  /**
   * Test to add each condition provided by Rules.
   *
   * @param string $id
   *   The id of the condition.
   * @param string $label
   *   The label of the condition.
   * @param array $required
   *   Array of fields to fill. The key is form field name and the value is the
   *   text to fill into the field. This should hold just the fields which are
   *   required and do not have any default value. Use an empty array if there
   *   are no fields or all fields have a default.
   * @param array $defaults
   *   Array of defaults which should be stored without having to select the
   *   value in the form.
   *
   * @dataProvider dataAddConditions()
   */
  public function testAddConditions($id, $label, array $required = [], array $defaults = []) {
    $this->createRule('Add condition ' . $id, 'test_rule', 'rules_entity_presave:node', "Add condition $label\nid=$id");
    $this->clickLink('Add condition');
    $this->fillField('Condition', $id);
    $this->pressButton('Continue');

    // Save the form. If $required is not empty then we should get error(s) so
    // verify this, then fill in the specified fields and try to save again.
    $this->pressButton('Save');
    if (!empty($required)) {
      // Check that an error message is shown.
      $this->assertSession()->pageTextContains('Error message');
      // Fill in the required fields.
      foreach ($required as $field => $value) {
        $this->fillField($field, $value);
      }
      $this->pressButton('Save');
    }
    // Assert that the rule has saved correctly with no error message.
    $this->assertSession()->pageTextNotContains('Error message');
    $this->assertSession()->pageTextContains('Edit reaction rule "Add condition ' . $id . '"');
    $this->assertSession()->pageTextContains('Condition: ' . $label);
    // @todo - check that all values ($required and $defaults) have been stored.
  }

  /**
   * Provides data for testAddConditions().
   *
   * @return array
   *   The test data.
   */
  public function dataAddConditions() {
    return [
      // Data.
      'Data Comparison' => [
        'rules_data_comparison',
        'Data Comparison',
        ['context[data][setting]' => 'node.status.value', 'context[value][setting]' => TRUE],
        ['operation' => '=='],
      ],
      'Data Is Empty' => [
        'rules_data_is_empty',
        'Data value is empty',
        ['context[data][setting]' => 'node.uid.entity.name.value'],
      ],
      /*
       // The two 'list' conditions do not work yet.
      'List contains' => [
        'rules_list_contains',
        'List contains item',
        ['context[list][setting]' => 'list_a', 'context[item][setting]' => '1'],
      ],
      'List count' => [
        'rules_list_count_is',
        'List Count Comparison',
        ['context[list][setting]' => 'list_b', 'context[value][setting]' => 3],
        ['operator' => '=='],
      ],
       */
      // Entity.
      'Entity has field' => [
        'rules_entity_has_field',
        'Entity has field',
        ['context[entity][setting]' => 'node', 'context[field][setting]' => 'mail'],
      ],
      'Entity is new' => [
        'rules_entity_is_new',
        'Entity is new',
        ['context[entity][setting]' => 'node'],
      ],
      'Entity is Bundle' => [
        'rules_entity_is_of_bundle',
        'Entity is of bundle', [
          'context[entity][setting]' => 'node',
          'context[type][setting]' => 'node',
          'context[bundle][setting]' => 'article',
        ],
      ],
      'Entity is Type' => [
        'rules_entity_is_of_type',
        'Entity is of TYPE',
        ['context[entity][setting]' => 'something' , 'context[type][setting]' => 'user'],
      ],
      // Node.
      'Node is promoted' => [
        'rules_node_is_promoted',
        'Node is promoted',
        ['context[node][setting]' => 'node'],
      ],
      'Node is published' => [
        'rules_node_is_published',
        'Node is published',
        ['context[node][setting]' => 'node'],
      ],
      'Node is sticky' => [
        'rules_node_is_sticky',
        'Node is sticky',
        ['context[node][setting]' => 'anything'],
      ],
      'Node is Type' => [
        'rules_node_is_of_type',
        'Node is of type',
        ['context[node][setting]' => 'something', 'edit-context-types-setting' => 'article'],
      ],
      // Path.
      'Path alias exists' => [
        'rules_path_alias_exists',
        'Path alias exists',
        ['context[alias][setting]' => 'something'],
        ['context[language][setting]' => 'something'],
      ],
      'Path has alias' => [
        'rules_path_has_alias',
        'Path has alias',
        ['context[path][setting]' => 'something'],
        ['context[language][setting]' => 'something'],
      ],
      // User.
      'User has entity field access' => [
        'rules_entity_field_access',
        'User has entity field access', [
          'context[entity][setting]' => 'something',
          'context[field][setting]' => 'mail',
          'context[user][setting]' => 'someone',
        ],
        ['context[operation][setting]' => 'view'],
      ],
      'User has role' => [
        'rules_user_has_role',
        'User has role',
        ['context[user][setting]' => 'someone', 'context[roles][setting][]' => 'authenticated'],
        ['operation' => 'AND'],
      ],
      'User is blocked' => [
        'rules_user_is_blocked',
        'User is blocked',
        ['context[user][setting]' => 'someone'],
      ],
    ];
  }

  /**
   * Test to add each action provided by Rules.
   *
   * @param string $id
   *   The id of the action.
   * @param string $label
   *   The label of the acition.
   * @param array $required
   *   Array of fields to fill. The key is form field name and the value is the
   *   text to fill into the field. This should hold just the fields which are
   *   required and do not have any default value. Use an empty array if there
   *   are no fields or all fields have a default.
   * @param array $defaults
   *   Array of defaults which should be stored without having to select the
   *   value in the form.
   *
   * @dataProvider dataAddActions()
   */
  public function testAddActions($id, $label, array $required = [], array $defaults = []) {
    $agr = print_r($this->account->getRoles(), TRUE);
    $urn = print_r(user_role_names(TRUE), TRUE);
    $this->createRule('Add action ' . $id, 'test_rule', 'rules_entity_presave:node', "Add condition $label id=$id\naccount->getRoles() = " . $agr . "\nuser_role_names = " . $urn);
    $this->clickLink('Add action');
    $this->fillField('Action', $id);
    $this->pressButton('Continue');

    // Save the form. If $required is not empty then we should get error(s) so
    // verify this, then fill in the specified fileds and try to save again.
    $this->pressButton('Save');
    if (!empty($required)) {
      // Check that an error message is shown.
      $this->assertSession()->pageTextContains('Error message');
      // Fill in the required fields.
      foreach ($required as $field => $value) {
        $this->fillField($field, $value);
      }
      $this->pressButton('Save');
    }

    // Assert that the rule has saved correctly with no error message.
    $this->assertSession()->pageTextNotContains('Error message');
    $this->assertSession()->pageTextContains('Edit reaction rule "Add action ' . $id . '"');
    $this->assertSession()->pageTextContains('Action: ' . $label);
    // @todo - check that all values ($required and $defaults) have been stored.
  }

  /**
   * Provides data for testAddActions().
   *
   * @return array
   *   The test data.
   */
  public function dataAddActions() {
    return [

    /* rules_ban_ip fails interactively and in tests with same error:
       You have requested a non-existent service "request".
       @see https://www.drupal.org/project/rules/issues/2922804
       The test data can be uncommented when this issue has been fixed.
     */
    /*
      'Ban ip address' => [
        'rules_ban_ip',
        'Ban an IP address',
        [],
        ['context[ip][setting]' => ''],
      ],
     */

    /* rules_entity_create:comment fails interactively and in tests with error:
       PluginNotFoundException: The "entity:comment:x" plugin does not exist.
       All values fails.
      'Add Comment' => [
        'rules_entity_create:comment',
        'Create Comment',
        ['context[comment_type][setting]' => 'x',
         'context[entity_id][setting]' => '5']
      ],
     */
      // Content.
      'Add node' => [
        'rules_entity_create:node',
        'Create a new content',
        ['context[type][setting]' => 'article', 'context[title][setting]' => 'Cakes'],
      ],
      // System.
      'Display system message' => [
        'rules_system_message',
        'Show a message on the site',
        ['context[message][setting]' => 'Here is the news', 'context[type][setting]' => 'status'],
        ['context[repeat][setting]' => 'no'],
      ],
      // User.
      'User - Add Role' => [
        'rules_user_role_add',
        'Add user role',
        ['context[user][setting]' => 'someone', 'context[roles][setting][]' => 'authenticated'],
      ],
      'User - Block' => [
        'rules_user_block',
        'Block a user',
        ['context[user][setting]' => 'someone'],
      ],
      'User - Remove Role' => [
        'rules_user_role_remove',
        'Remove user role',
        ['context[user][setting]' => 'someone', 'context[roles][setting][]' => 'authenticated'],
      ],

    ];
  }

}
