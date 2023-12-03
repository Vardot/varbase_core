<?php

/**
 * @file
 * Contains varbase_internationalization_update_8###(s) hook updates.
 */

/**
 * Issue #3032661: Changed [Varbase Internationalization] not to use.
 *
 * The [Localization client] module by disabling it for old sites and not to
 * install it in new Varbase installations.
 */
function varbase_internationalization_update_86001() {

  if (\Drupal::moduleHandler()->moduleExists('l10n_client_ui')) {
    \Drupal::service('module_installer')->uninstall(['l10n_client_ui'], FALSE);
  }
}
