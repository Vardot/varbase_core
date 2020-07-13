<?php

namespace Drupal\varbase_core\Utility;

class BatchProcessor {

  /**
   * Implements callback_batch_operation().
   *
   * Performs batch installation of modules.
   */
  public function install_module_batch($module, $module_name, &$context) {
    \Drupal::service('module_installer')->install([$module], FALSE);
    $context['results'][] = $module;
    $context['message'] = t('Installed %module module.', ['%module' => $module_name]);
  }

}