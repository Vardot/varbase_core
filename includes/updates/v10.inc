<?php

/**
 * @file
 * Contains varbase_core_update_10###(s) hook updates.
 */

/**
 * Issue #3399650: Add Config ignore for Import/Export for Dynamic Responsive Image (drimage) + Improved.
 *
 * Generated image styles.
 */
function varbase_core_update_100001() {
  if (\Drupal::moduleHandler()->moduleExists('config_ignore')) {
    // If Drimage was enabled.
    if (\Drupal::moduleHandler()->moduleExists('drimage')) {

      $config_factory = \Drupal::configFactory()->getEditable('config_ignore.settings');

      $mode = $config_factory->get('mode');

      if (!isset($mode)) {
        $config_factory->set('mode', 'simple');
      }

      $ignored_config_entities = $config_factory->get('ignored_config_entities');

      if (isset($ignored_config_entities)) {
        $ignored_config_entities[] = 'image.style.drimage_*';
        $config_factory->set('ignored_config_entities', $ignored_config_entities);
      }

      $config_factory->save(TRUE);
    }
    // If Drimage improved was enabled.
    elseif (\Drupal::moduleHandler()->moduleExists('drimage_improved')) {

      $config_factory = \Drupal::configFactory()->getEditable('config_ignore.settings');

      $mode = $config_factory->get('mode');

      if (!isset($mode)) {
        $config_factory->set('mode', 'simple');
      }

      $ignored_config_entities = $config_factory->get('ignored_config_entities');

      if (isset($ignored_config_entities)) {
        $ignored_config_entities[] = 'image.style.drimage_improved_*';
        $config_factory->set('ignored_config_entities', $ignored_config_entities);
      }

      $config_factory->save(TRUE);
    }
  }
}

/**
 * VARBASE-2380: Stop using the unsupported ECA Helper module.
 *
 * ECA Helper is unsupported (SA-CONTRIB-2026-193). Move every ECA model off
 * its "HTTP request" action onto the Varbase Core one, which sets the same
 * response token, then uninstall ECA Helper when no configuration depends on
 * it any more.
 */
function varbase_core_update_100002() {
  $config_factory = \Drupal::configFactory();
  $messages = [];

  foreach ($config_factory->listAll('eca.eca.') as $name) {
    $model = $config_factory->getEditable($name);
    $actions = $model->get('actions') ?? [];
    $replaced = [];
    foreach ($actions as $action_id => $action) {
      if (($action['plugin'] ?? '') !== 'eca_helper_http_request') {
        continue;
      }
      $actions[$action_id]['plugin'] = 'varbase_core_http_request';
      $actions[$action_id]['configuration'] = _varbase_core_http_request_configuration($action['configuration'] ?? []);
      $replaced[] = $action_id;
    }
    if (!$replaced) {
      continue;
    }
    $model->set('actions', $actions);

    // Keep the modeler's copy of the model in step, so the diagram still
    // opens with the new action. A stored hash that no longer matches its
    // data model makes the modeler discard the diagram.
    $modeler_id = $model->get('third_party_settings.modeler_api.modeler_id');
    $data = $model->get('third_party_settings.modeler_api.data');
    if (is_string($data) && $data !== '') {
      if (strlen($data) === 37 && str_starts_with($data, 'hash:')) {
        $data_model = $config_factory->getEditable('modeler_api.data_model.eca_' . $modeler_id . '_' . $model->get('id'));
        $xml = (string) $data_model->get('data');
        if ($xml !== '' && hash_equals(substr($data, 5), md5($xml))) {
          $xml = _varbase_core_http_request_bpmn($xml, $replaced);
          $data_model->set('data', $xml)->save(TRUE);
          $model->set('third_party_settings.modeler_api.data', 'hash:' . md5($xml));
        }
      }
      else {
        $model->set('third_party_settings.modeler_api.data', _varbase_core_http_request_bpmn($data, $replaced));
      }
    }

    // Swap the module dependency when nothing else in the model needs it.
    $plugins = array_merge(
      array_column($model->get('events') ?? [], 'plugin'),
      array_column($model->get('conditions') ?? [], 'plugin'),
      array_column($actions, 'plugin'),
    );
    $modules = $model->get('dependencies.module') ?? [];
    if (!preg_grep('/^eca_helper/', $plugins)) {
      $modules = array_diff($modules, ['eca_helper', 'eca_helper_workflow']);
    }
    $modules[] = 'varbase_core';
    $modules = array_values(array_unique($modules));
    sort($modules);
    $model->set('dependencies.module', $modules);
    $model->save(TRUE);

    $messages[] = t('Moved the ECA model %model to the Varbase Core HTTP request action.', ['%model' => $model->get('id')]);
  }

  // Uninstall ECA Helper when nothing depends on it.
  $extensions = $config_factory->get('core.extension')->get('module') ?? [];
  $helper_modules = array_values(array_intersect(['eca_helper_workflow', 'eca_helper'], array_keys($extensions)));
  if ($helper_modules) {
    $dependents = \Drupal::service('config.manager')->findConfigEntityDependencies('module', $helper_modules);
    if ($dependents) {
      $messages[] = t('ECA Helper is unsupported (SA-CONTRIB-2026-193) but still used by: %config. Varbase Core no longer requires it, so keep it with "composer require drupal/eca_helper" until those uses are replaced, then uninstall it.', [
        '%config' => implode(', ', array_keys($dependents)),
      ]);
    }
    elseif (\Drupal::service('extension.list.module')->exists('eca_helper')) {
      \Drupal::service('module_installer')->uninstall($helper_modules, FALSE);
      $messages[] = t('Uninstalled the unsupported ECA Helper module (SA-CONTRIB-2026-193).');
    }
    else {
      // The code went with the composer update that ran before this one, so
      // the module installer cannot see it. Nothing depends on it: drop it
      // from the extension list directly.
      $extension_config = $config_factory->getEditable('core.extension');
      foreach ($helper_modules as $module) {
        $extension_config->clear('module.' . $module);
        \Drupal::keyValue('system.schema')->delete($module);
      }
      $extension_config->save(TRUE);
      $messages[] = t('Removed the unsupported ECA Helper module (SA-CONTRIB-2026-193), whose code was already gone, from the installed modules.');
    }
  }

  return implode(' ', array_map('strval', $messages));
}

/**
 * Maps an eca_helper_http_request configuration to the Varbase Core action.
 *
 * Cookies, multipart uploads and free-form request options are not carried
 * over: the Varbase Core action does not offer them.
 *
 * @param array $configuration
 *   The eca_helper_http_request configuration.
 *
 * @return array
 *   The varbase_core_http_request configuration.
 */
function _varbase_core_http_request_configuration(array $configuration): array {
  $keys = [
    'method',
    'url',
    'data',
    'data_serialization',
    'query_parameters',
    'headers',
    'token_name',
  ];
  $configuration = array_intersect_key($configuration, array_flip($keys)) + [
    'method' => 'get',
    'url' => '',
    'data' => '',
    'data_serialization' => 'raw',
    'query_parameters' => '',
    'headers' => '',
    'token_name' => '',
  ];
  if ($configuration['data_serialization'] === 'multipart') {
    $configuration['data_serialization'] = 'raw';
  }
  $configuration['timeout'] = '5';
  return $configuration;
}

/**
 * Moves the given BPMN tasks to the Varbase Core HTTP request action.
 *
 * @param string $xml
 *   The BPMN model.
 * @param string[] $task_ids
 *   The ids of the tasks to change.
 *
 * @return string
 *   The changed BPMN model.
 */
function _varbase_core_http_request_bpmn(string $xml, array $task_ids): string {
  foreach ($task_ids as $task_id) {
    $xml = preg_replace_callback(
      '#<bpmn2:task id="' . preg_quote($task_id, '#') . '".*?</bpmn2:task>#s',
      static function (array $match): string {
        $task = str_replace(
          [
            'org.drupal.action.eca_helper_http_request"',
            'name="pluginid" value="eca_helper_http_request"',
          ],
          [
            'org.drupal.action.varbase_core_http_request"',
            'name="pluginid" value="varbase_core_http_request"',
          ],
          $match[0]
        );
        $task = preg_replace('#\s*<camunda:field name="(data_yaml|cookies|request_options)">\s*<camunda:string>[^<]*</camunda:string>\s*</camunda:field>#', '', $task);
        $task = preg_replace('#(\n([ ]*)<camunda:field name="token_name">)#', "\n$2<camunda:field name=\"timeout\">\n$2  <camunda:string>5</camunda:string>\n$2</camunda:field>$1", $task, 1);
        return $task;
      },
      $xml
    );
  }
  return $xml;
}
