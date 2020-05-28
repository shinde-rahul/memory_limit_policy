<?php

namespace Drupal\memory_limit_policy_path\Plugin\MemoryLimitConstraint;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Path\PathMatcherInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\memory_limit_policy\MemoryLimitConstraintBase;
use Drupal\path_alias\AliasManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure the memory limit based on path.
 *
 * @MemoryLimitConstraint(
 *   id = "path",
 *   title = @Translation("Path"),
 *   description = @Translation("Provide a list of path where the memory limit must be overridden.")
 * )
 */
class Path extends MemoryLimitConstraintBase implements ContainerFactoryPluginInterface {

  /**
   * Current path.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPath;

  /**
   * The path matcher.
   *
   * @var \Drupal\Core\Path\PathMatcherInterface
   */
  protected $pathMatcher;

  /**
   * An alias manager to find the alias for the current system path.
   *
   * @var \Drupal\path_alias\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * Constructs constraint plugin.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Path\CurrentPathStack $current_path
   *   The current path.
   * @param \Drupal\Core\Path\PathMatcherInterface $path_matcher
   *   The path matcher service.
   * @param \Drupal\path_alias\AliasManagerInterface $alias_manager
   *   An alias manager to find the alias for the current system path.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, CurrentPathStack $current_path, PathMatcherInterface $path_matcher, AliasManagerInterface $alias_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->currentPath = $current_path;
    $this->pathMatcher = $path_matcher;
    $this->aliasManager = $alias_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('path.current'),
      $container->get('path.matcher'),
      $container->get('path_alias.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['paths'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Paths'),
      '#description' => $this->t('Enter one path per line. Use \'*\' character as wildcard to target multiple pages (e.g "/node/*/edit" for all the node edit pages)'),
      '#default_value' => $this->getConfiguration()['paths'] ?? '',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['paths'] = $form_state->getValue('paths');
  }

  /**
   * {@inheritdoc}
   */
  public function getSummary() {
    $paths = explode(PHP_EOL, $this->configuration['paths']);
    array_walk($paths, function (&$path) {
      $path = rtrim(trim($path, "\r"), '/');
    });
    $paths = implode(', ', $paths);

    return $this->t('Paths: @paths', ['@paths' => $paths]);
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    $current_path = trim($this->currentPath->getPath(), "\r");

    $page_match = $this->pathMatcher->matchPath($current_path, $this->configuration['paths']);

    if (!$page_match) {
      $alias_path = $this->aliasManager->getPathByAlias($current_path);

      if ($current_path !== $alias_path) {
        $page_match = $this->pathMatcher->matchPath($alias_path, $this->configuration['paths']);
      }
    }

    return $page_match ?? parent::evaluate();
  }

}
