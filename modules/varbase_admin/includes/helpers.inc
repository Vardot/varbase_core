<?php

/**
 * @file
 * Contains List of all varbase_admin helpers.
 *
 * Add custom needed helper functions.
 */

use Drupal\shortcut\Entity\Shortcut;

/**
 * Add default shortcuts.
 */
function varbase_admin__add_default_shortcuts() {
  // Populate the default shortcut set.
  $shortcut = Shortcut::create([
    'shortcut_set' => 'default',
    'title' => t('Add content'),
    'weight' => 0,
    'link' => ['uri' => 'internal:/node/add'],
  ]);
  $shortcut->save();

  $shortcut = Shortcut::create([
    'shortcut_set' => 'default',
    'title' => t('All content'),
    'weight' => 1,
    'link' => ['uri' => 'internal:/admin/content'],
  ]);
  $shortcut->save();

  $shortcut = Shortcut::create([
    'shortcut_set' => 'default',
    'title' => t('All media'),
    'weight' => 2,
    'link' => ['uri' => 'internal:/admin/content/media'],
  ]);
  $shortcut->save();

  $shortcut = Shortcut::create([
    'shortcut_set' => 'default',
    'title' => t('Taxonomy'),
    'weight' => 3,
    'link' => ['uri' => 'internal:/admin/structure/taxonomy'],
  ]);
  $shortcut->save();

  $shortcut = Shortcut::create([
    'shortcut_set' => 'default',
    'title' => t('Permissions'),
    'weight' => 4,
    'link' => ['uri' => 'internal:/admin/people/permissions'],
  ]);
  $shortcut->save();
}
