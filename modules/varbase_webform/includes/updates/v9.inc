<?php

/**
 * @file
 * Contains varbase_webform_update_9###(s) hook updates.
 */

use Vardot\Installer\ModuleInstallerFactory;

/**
 * Issue #3270834: Switch to use the Module Installer Factory.
 *
 * In the Varbase Webform module.
 */
function varbase_webform_update_90001() {
  // Set the weight of the module after installation of list of modules.
  // To make sure that any hook or event subscriber worker
  // after all used modules.
  ModuleInstallerFactory::setModuleWeightAfterInstallation('varbase_webform');
}
