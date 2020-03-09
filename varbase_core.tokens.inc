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
  $info['tokens']['site']['origin-url'] = [
    'name' => t("Origin URL"),
    'description' => t("The Origin URL (scheme and HTTP host) of the site. No language prefix."),
  ];

  return $info;
}

/**
 * Implements hook_tokens().
 */
function varbase_core_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  $replacements = [];

  if ($type == 'site') {
    foreach ($tokens as $name => $original) {
      switch ($name) {
        case 'origin-url':
          // Until #1088112: Introduce a token to get site's base URL is
          // committed,
          // https://www.drupal.org/project/drupal/issues/1088112
          // let's use a custom token. Let's call it: [site:origin-url].
          // No language prefix in the url.
          // https://www.drupal.org/project/varbase_core/issues/3106793
          // -----------------------------------------------------------------
          /** @var \Symfony\Component\HttpFoundation\Request $origin_url */
          $origin_url = \Drupal::request()->getSchemeAndHttpHost() . \Drupal::request()->getBaseUrl();
          $bubbleable_metadata->addCacheContexts(['url.site']);
          $replacements[$original] = $origin_url;
          break;
      }
    }
  }
  elseif ($type == 'default-active-theme') {
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
