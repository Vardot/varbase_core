<?php

/**
 * @file
 * Contains varbase_tour_update_10###(s) hook updates.
 */

use Drupal\Core\Recipe\Recipe;
use Drupal\Core\Recipe\RecipeRunner;

/**
 * Issue #3499644: Remove the Tour UI module.
 *
 * As it has officially been merged into Tour ~2.0, Hide tour when empty, Change the machine
 * name id of tours to follow The machine-readable name must contain only
 * lowercase letters, numbers, and underscores.
 */
function varbase_tour_update_100001() {
  varbase_tour_update_90001();
}

/**
 * Issue #3505583: Change the default welcome to Varbase tour to work with the Navigation system.
 */
function varbase_tour_update_100002() {
  $module_path = Drupal::service('module_handler')->getModule('varbase_tour')->getPath();
  $recipe = Recipe::createFromDirectory($module_path . '/recipes/updates/varbase_tour_update_100002');
  RecipeRunner::processRecipe($recipe);
}
