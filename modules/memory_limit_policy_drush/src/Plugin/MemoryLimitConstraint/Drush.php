<?php

namespace Drupal\memory_limit_policy_drush\Plugin\MemoryLimitConstraint;

use Drupal\Core\Form\FormStateInterface;
use Drupal\memory_limit_policy\MemoryLimitConstraintBase;

/**
 * Configure the memory limit based on path.
 *
 * @MemoryLimitConstraint(
 *   id = "drush",
 *   title = @Translation("Drush"),
 *   description = @Translation("Provide a list of drush commands where the memory limit must be overridden.")
 * )
 */
class Drush extends MemoryLimitConstraintBase {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['drush_commands'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Drush Commands'),
      '#description' => $this->t('Enter one drush command per line.'),
      '#default_value' => $this->getConfiguration()['drush_commands'] ?? '',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['drush_commands'] = $form_state->getValue('drush_commands');
  }

  /**
   * {@inheritdoc}
   */
  public function getSummary() {
    $drush_commands = explode(PHP_EOL, $this->configuration['drush_commands']);
    array_walk($drush_commands, function (&$drush_command) {
      $drush_command = rtrim(trim($drush_command, "\r"), '/');
    });
    $drush_commands = implode(', ', $drush_commands);

    return $this->t('Drush Commands: @drush_commands', ['@drush_commands' => $drush_commands]);
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    // For Drush constraints we are not evaluating anything here. So this
    // will always return FALSE for clients other than CLI.
    return FALSE;
  }

}
