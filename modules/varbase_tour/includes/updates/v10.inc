<?php

/**
 * @file
 * Contains varbase_tour_update_10###(s) hook updates.
 */

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
