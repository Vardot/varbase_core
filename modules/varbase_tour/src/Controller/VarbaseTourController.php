<?php

namespace Drupal\varbase_tour\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Varbase Tour Controller.
 */
class VarbaseTourController extends ControllerBase {

  /**
   * Content.
   */
  public function content() {

    return [
      '#theme' => 'varbase_welcome_theme',
      '#welcome_modal_content' => $this->t('Welcome modal content'),
    ];

  }

}
