<?php

namespace Drupal\varbase_admin\Hook;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;

/**
 * Object-oriented hook implementations for Varbase Admin.
 */
class VarbaseAdminHooks {

  /**
   * Implements hook_toolbar_alter().
   */
  #[Hook('toolbar_alter')]
  public function toolbarAlter(array &$items): void {
    // Remove the [Search] menu link and box in the toolbar as we use Coffee.
    if (isset($items['administration_search'])) {
      unset($items['administration_search']);
    }
  }

  /**
   * Implements hook_form_BASE_FORM_ID_alter() for node_type_add_form.
   */
  #[Hook('form_node_type_add_form_alter')]
  public function formNodeTypeAddFormAlter(array &$form, FormStateInterface $form_state): void {
    if (isset($form['workflow']['options']['#default_value']['promote'])) {
      unset($form['workflow']['options']['#default_value']['promote']);
    }
    if (isset($form['rabbit_hole']['rh_override']['#default_value'])) {
      $form['rabbit_hole']['rh_override']['#default_value'] = 0;
    }
    if (isset($form['menu']['menu_options']['#default_value'])) {
      $form['menu']['menu_options']['#default_value'] = [];
    }
    if (isset($form['display']['display_submitted']['#default_value'])) {
      $form['display']['display_submitted']['#default_value'] = 0;
    }
  }

}
