/**
 * @file
 * Behaviors for the vartheme bs4 theme.
 */

(function ($, _, Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.varbaseUpdateHelper = {
    attach: function (context) {
      $('[data-drupal-selector^="edit-varbase-update-helper"] input').attr("disabled", true);
    }
  };

})(window.jQuery, window._, window.Drupal, window.drupalSettings);
