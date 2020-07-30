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
      '#description' => $this->t('Enter one command per line.'),
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
    array_walk($drush_commands, function (&$path) {
      $path = rtrim(trim($path, "\r"), '/');
    });
    $drush_commands = implode(', ', $drush_commands);

    return $this->t('Drush Commands: @drush_commands', ['@drush_commands' => $drush_commands]);
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    return parent::evaluate();
  }

}
