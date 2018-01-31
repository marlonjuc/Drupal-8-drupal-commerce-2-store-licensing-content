<?php

namespace Drupal\rules\Plugin\RulesAction;

use Drupal\Component\Utility\SafeMarkup;
use Drupal\rules\Core\RulesActionBase;

/**
 * Provides a 'Show a message on the site' action.
 *
 * @RulesAction(
 *   id = "rules_system_message",
 *   label = @Translation("Show a message on the site"),
 *   category = @Translation("System"),
 *   context = {
 *     "message" = @ContextDefinition("string",
 *       label = @Translation("Message")
 *     ),
 *     "type" = @ContextDefinition("string",
 *       label = @Translation("Message type"),
 *       list_options_callback = "messageTypeListOptions"
 *     ),
 *     "repeat" = @ContextDefinition("boolean",
 *       label = @Translation("Repeat message"),
 *       description = @Translation("If disabled and the message has been already shown, then the message won't be repeated."),
 *       default_value = NULL,
 *       required = FALSE,
 *       list_options_callback = "repeatListOptions"
 *     )
 *   }
 * )
 *
 * @todo: Add access callback information from Drupal 7.
 */
class SystemMessage extends RulesActionBase {

  /**
   * Set a system message.
   *
   * @param string $message
   *   Message string that should be set.
   * @param string $type
   *   Type of the message.
   * @param bool $repeat
   *   (optional) TRUE if the message should be repeated.
   */
  protected function doExecute($message, $type, $repeat) {
    // @todo Should we do the sanitization somewhere else? D7 had the sanitize
    // flag in the context definition.
    $message = SafeMarkup::checkPlain($message);
    $repeat = (bool) $repeat;
    drupal_set_message($message, $type, $repeat);
  }

  /**
   * Returns an array of statuses that we can set for the drupal_set_message().
   *
   * @return array
   *   An array of status options keyed on the status name.
   */
  public function messageTypeListOptions() {
    return [
      'info' => $this->t('Info'),
      'status' => $this->t('Status'),
      'warning' => $this->t('Warning'),
      'error' => $this->t('Error'),
    ];
  }

  /**
   * Returns a YES/NO option set for selecting whether to repeat the message.
   *
   * @return array
   *   A YES/NO options array.
   */
  public function repeatListOptions() {
    return [
      0 => $this->t('No'),
      1 => $this->t('Yes'),
    ];
  }

}
