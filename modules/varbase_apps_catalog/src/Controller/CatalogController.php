<?php
namespace Drupal\varbase_apps_catalog\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\Yaml\Yaml;
use \Drupal\Core\Url;
/**
 * Provides route responses for the Example module.
 */
class CatalogController extends ControllerBase {
  const YAML_FILE_NAME = 'catalog.yml';
  const YAML_FILE_BASE_URL = '';

  public function handleUrls($app){
    if(!$this::YAML_FILE_BASE_URL) return $app;
    if(isset($app["#icon"])){
      if (!preg_match('#^(https?://)#i', $app["#icon"])) {
        $app["#icon"] = $this::YAML_FILE_BASE_URL . "/" . $app["#icon"];
      }
    }

    if(isset($app["images"])){
      foreach ($app["images"] as $key => $url) {
        if (!preg_match('#^(https?://)#i', $url)) {
          $app["images"][$key] = $this::YAML_FILE_BASE_URL . "/" . $url;
        }
      }
    }

    return $app;
  }

  public function appInfoPage($machineName) {
    $module_handler = \Drupal::service('module_handler');
    $module_path = $module_handler->getModule('varbase_apps_catalog')->getPath();
    $yamlUrlBase = $this::YAML_FILE_BASE_URL;
    if (!$yamlUrlBase) {
      $yamlUrlBase = $module_path . "/";
    }

    $yml = file_get_contents($yamlUrlBase . $this::YAML_FILE_NAME);
    $yml = Yaml::parse($yml);
    $currentApp = NULL;

    $notFound = array(
      '#theme' => 'app_page',
      '#title' => "Application info",
      '#subtitle' => "Application not found",
      '#attached' => array(
        'library' =>  array(
          'varbase_apps_catalog/varbase_apps_catalog'
        ),
      ),
    );

    if (isset($yml["#apps"])) {
      foreach ($yml["#apps"] as $key => $app) {
        if ($app['#machine_name'] == $machineName) {
          $currentApp = $app;
          if (isset($currentApp['#machine_name']) && $module_handler->moduleExists($currentApp['#machine_name'])) {
            $currentApp["#status"] = "enabled";
          } else {
            $currentApp["#status"] = "disabled";
          }
          $currentApp = $this->handleUrls($currentApp);
          break;
        }
      }
    } else {
      return $notFound;
    }

    
    if ($currentApp) {
      return array(
        '#theme' => 'app_page',
        '#title' => $currentApp['#title'],
        '#subtitle' => $currentApp['#subtitle'],
        "#status" => $currentApp['#status'],
        '#icon' => $currentApp['#icon'],
        '#images' => $currentApp['#images'],
        '#description' => $currentApp['#description'],
        '#attached' => array(
          'library' =>  array(
            'varbase_apps_catalog/varbase_apps_catalog'
          ),
        ),
      );
    } else {
      return $notFound;
    }


    return $notFound;
  }

  public function catalogPage() {
    $module_handler = \Drupal::service('module_handler');
    $module_path = $module_handler->getModule('varbase_apps_catalog')->getPath();
    $yamlUrlBase = $this::YAML_FILE_BASE_URL;
    if (!$yamlUrlBase) {
      $yamlUrlBase = $module_path . "/";
    }

    $yml = file_get_contents($yamlUrlBase . $this::YAML_FILE_NAME);
    $yml = Yaml::parse($yml);
    
    if (isset($yml["#apps"])) {
      foreach ($yml["#apps"] as $key => $app) {
        if (isset($app['#machine_name']) && $module_handler->moduleExists($app['#machine_name'])) {
          $yml["#apps"][$key]["#status"] = "enabled";
        } else{ 
          $yml["#apps"][$key]["#status"] = "disabled";
        }

        $yml["#apps"][$key] = $this->handleUrls($yml["#apps"][$key]);
        $yml["#apps"][$key]["#url"] = Url::fromRoute('varbase_apps_catalog.app_info', ['machineName' => $yml["#apps"][$key]["#machine_name"]]);
      }
    }
    $element = array(
      '#theme' => 'catalog_page',
      '#title' => $yml['#title'],
      '#subtitle' => $yml['#subtitle'],
      '#featured' => $yml['#featured'],
      '#apps' => $yml['#apps'],
      '#attached' => array(
				'library' =>  array(
					'varbase_apps_catalog/varbase_apps_catalog'
				),
			),
    );
    return $element;
  }

}