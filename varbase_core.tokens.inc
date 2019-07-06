<?php

/**
 * @file
 * Contains varbase_core.tokens.inc.
 */

/**
 * To have all varbase core general and global tokens.
 */

use Drupal\Core\Render\BubbleableMetadata;

/**
 * Implements hook_token_info().
 */
function varbase_core_token_info() {
  // Default theme token.
  $info['types']['default-active-theme'] = [
    'name' => t('Default theme'),
    'description' => t('Tokens related to the Default theme.'),
  ];
  $info['tokens']['default-active-theme']['path'] = [
    'name' => t('Path'),
    'description' => t('The path of the Default theme.'),
  ];

  return $info;
}

/**
 * Implements hook_tokens().
 */
function varbase_core_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  $replacements = [];

  if ($type == 'default-active-theme') {
    foreach ($tokens as $name => $original) {
      switch ($name) {
        case 'path':
          $replacements[$original] = \Drupal::theme()->getActiveTheme()->getPath();
          break;
      }
    }
  }
  return $replacements;
}
