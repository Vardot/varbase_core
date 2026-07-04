<?php

declare(strict_types=1);

namespace Drupal\varbase_update_helper\Hook;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\update_helper_checklist\Entity\Update;

/**
 * Object-oriented hook implementations for Varbase Update Helper.
 */
class VarbaseUpdateHelperHooks {

  use StringTranslationTrait;
  use DependencySerializationTrait;

  /**
   * Constructs a VarbaseUpdateHelperHooks object.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler.
   */
  public function __construct(
    protected MessengerInterface $messenger,
    protected ModuleHandlerInterface $moduleHandler,
  ) {}

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

        $form['actions']['save']['#submit'][] = [$this, 'checklistapiFormSubmit'];
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
   * Form submit callback for the checklistapi checklist form.
   */
  public function checklistapiFormSubmit(array $form, FormStateInterface $form_state): void {
    if ($form['#checklist']->id == 'update_helper_checklist') {
      $messenger = $this->messenger;

      $checklistapi = $form_state->getValue('checklistapi');
      foreach ($checklistapi as $key => $updateset) {
        if ($key == "checklistapi__active_tab") {
          continue;
        }

        if (is_array($updateset) && !empty($updateset)) {
          foreach ($updateset as $update => $status) {
            $update_key = str_replace('.', '_', $update);
            $entity = Update::load($update_key);

            $entityStatus = ($entity && $entity->wasSuccessfulByHook()) ? TRUE : FALSE;
            if ($entityStatus) {
              continue;
            }
            if ($status) {
              $update_data = explode(":", $update);
              $this->moduleHandler->loadInclude($update_data[0], 'install');
              if (function_exists($update_data[1])) {
                call_user_func($update_data[1], FALSE);
              }
              else {
                $checklistapi[$key][$update] = 0;
                $messenger->addWarning($this->t("Couldn't find an update hook: %update_hook. Please verify the update hook name.", ["%update_hook" => $update_data[1]]));
              }
            }
          }
        }
      }

      $checklist = $form['#checklist'];
      $checklist->saveProgress($checklistapi);
      $form_state->setRedirect($checklist->getRouteName(), [], [
        'fragment' => $checklistapi['checklistapi__active_tab'],
      ]);
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
