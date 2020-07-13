<?php

namespace Drupal\varbase_core\Utility;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The Varbase core Batch Processor.
 */
class BatchProcessor implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The module installer.
   *
   * @var \Drupal\Core\Extension\ModuleInstallerInterface
   */
  protected $moduleInstaller;

  /**
   * BatchProcessor construct.
   *
   * @param \Drupal\Core\Extension\ModuleInstallerInterface $module_installer
   *   The module installer.
   */
  public function __construct(
    ModuleInstallerInterface $module_installer
  ) {
    $this->moduleInstaller = $module_installer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('module_installer')
    );
  }

  /**
   * Performs batch installation of modules.
   */
  public function installModuleBatch($module, $module_name, &$context) {
    $this->moduleInstaller->install([$module], FALSE);
    $context['results'][] = $module;
    $context['message'] = $this->t('Installed %module_name module.', ['%module_name' => $module_name]);
  }

}
