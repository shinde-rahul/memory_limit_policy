<?php

namespace Drupal\memory_limit_policy_drush\Commands;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\memory_limit_policy\Entity\MemoryLimitPolicy;
use Drush\Commands\DrushCommands;
use Symfony\Component\Console\Event\ConsoleCommandEvent;

/**
 * Class MemoryLimitPolicyDrushCommands.
 */
class MemoryLimitPolicyCommands extends DrushCommands {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  private $entityTypeManager;

  /**
   * Constructs a new MemoryLimitPolicySubscriber object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   *   Entity type manager service.
   */
  public function __construct(EntityTypeManager $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Sets the memory limit for cli.
   *
   * This pre-command-event will set the php memory limit for drush command,
   * if there any policy configured for the same.
   *
   * @param \Symfony\Component\Console\Event\ConsoleCommandEvent $event
   *   The event.
   *
   * @hook command-event *
   * @throws \Exception
   */
  public function preCommandEvent(ConsoleCommandEvent $event) {
    $command = $event->getCommand()->getName();
    $policies = $this->entityTypeManager->getStorage('memory_limit_policy')->loadByProperties(['status' => TRUE]);

    // Sort policies by weight.
    uasort($policies, function (MemoryLimitPolicy $a, MemoryLimitPolicy $b) {
      if ($a->getWeight() == $b->getWeight()) {
        return 0;
      }
      return ($a->getWeight() < $b->getWeight()) ? -1 : 1;
    });

    /** @var \Drupal\memory_limit_policy\Entity\MemoryLimitPolicy $policy */
    foreach ($policies as $policy) {
      foreach ($policy->getConstraints() as $constraint) {
        // If the constraint is other than drush, skip to next.
        if ($constraint['id'] !== 'drush') {
          continue;
        }

        // Get the configured drush commands to validate.
        $drush_commands = explode(PHP_EOL, $constraint['drush_commands']);
        array_walk($drush_commands, function (&$drush_command) {
          $drush_command = rtrim(trim($drush_command, "\r"), '/');
        });

        // If the current command is in the policy constraint, then get the
        // configured memory and set it.
        if (in_array($command, $drush_commands)) {
          ini_set('memory_limit', $policy->getMemory());
        }
      }
    }

  }

}
