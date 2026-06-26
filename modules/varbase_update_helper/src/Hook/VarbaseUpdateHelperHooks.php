<?php

namespace Drupal\varbase_update_helper\Hook;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Render\Element;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\update_helper_checklist\Entity\Update;

/**
 * Object-oriented hook implementations for Varbase Update Helper.
 */
class VarbaseUpdateHelperHooks {

  use StringTranslationTrait;

  /**
   * Implements hook_form_alter().
   */
  #[Hook('form_alter')]
  public function formAlter(array &$form, FormStateInterface $form_state, $form_id): void {
    if ($form_id == "checklistapi_checklist_form") {
      if ($form['#checklist']->id == 'update_helper_checklist') {
        if (!isset($form['actions']['save']['#submit'])) {
          $form['actions']['save']['#submit'] = [];
        }

        // The #submit callback stays a procedural function (referenced by name).
        $form['actions']['save']['#submit'][] = 'varbase_update_helper_checklistapi_form_submit';
        $checklist = $form['#checklist'];
        $groups = $checklist->items;

        // Prevent the user from Clearing checklist progress.
        unset($form['actions']['clear']);

        foreach (Element::children($groups) as $group_key) {
          $group = &$groups[$group_key];

          foreach (Element::children($group) as $item_key) {
            $update_key = str_replace('.', '_', $item_key);
            $entity = Update::load($update_key);

            $entityStatus = ($entity && $entity->wasSuccessfulByHook()) ? TRUE : FALSE;
            if ($entityStatus) {
              $form[$group_key][$item_key]['#disabled'] = TRUE;
            }
          }
        }
      }
    }
  }

  /**
   * Implements hook_help().
   */
  #[Hook('help')]
  public function help($route_name, RouteMatchInterface $route_match): string {
    switch ($route_name) {
      case 'help.page.varbase_update_helper':
        $output = '';
        $output .= '<h3>' . $this->t('About') . '</h3>';
        $output .= '<p>' . $this->t('Varbase update helper') . '</p>';
        return $output;

      default:
        return '';
    }
  }

  /**
   * Implements hook_checklistapi_checklist_info_alter().
   */
  #[Hook('checklistapi_checklist_info_alter')]
  public function checklistapiChecklistInfoAlter(array &$definitions): void {
    if (isset($definitions['update_helper_checklist']['#title'])) {
      $definitions['update_helper_checklist']['#title'] = $this->t('Varbase update instructions');
    }
  }

  /**
   * Implements hook_form_FORM_ID_alter() for checklistapi_checklist_form.
   */
  #[Hook('form_checklistapi_checklist_form_alter')]
  public function formChecklistapiChecklistFormAlter(array &$form, FormStateInterface $form_state): void {
    if ($form['#checklist']->id == 'update_helper_checklist') {
      $form['#attached']['library'][] = 'varbase_update_helper/varbase_update_helper';
    }
  }

}
