<?php

/**
 * @file
 * Theme and preprocess functions for Apigee Edge Entities.
 */

/**
 * Implements template_preprocess_app().
 */
function apigee_openbank_kickstart_preprocess_app(array &$variables) {
  /** @var \Drupal\apigee_edge\Entity\AppInterface $app */
  $app = $variables['entity'];

  // Preprocess the app credentials so that we can render individual fields.
  if ($credentials = $app->getCredentials()) {
    $credential_variables['elements']['#credential'] = reset($credentials);

    template_preprocess_app_credential($credential_variables);

    // Render the consumerKey and the consumerSecret as secret fields.
    foreach (['consumerKey', 'consumerSecret'] as $field_name) {
      $credential_variables['primary_wrapper'][$field_name]['value'] = [
        '#type' => 'secret',
        '#value' => $credential_variables['primary_wrapper'][$field_name]['value']['#value'],
      ];
    }

    $variables['content']['credentials'] = $credential_variables;
  }

  // Add local tasks on the app canonical route as additional tabs.
  $variables['additional_tabs'] = [];
  $canonical_route_name = "entity.{$app->getEntityTypeId()}.canonical_by_" . str_replace('_app', '', $app->getEntityTypeId());
  if ($tasks = Drupal::service('plugin.manager.menu.local_task')->getLocalTasks($canonical_route_name)) {
    foreach ($tasks['tabs'] as $tab) {
      $link = $tab['#link'];

      /** @var \Drupal\Core\Url $url */
      $url = $link['url'];
      if ($url->getRouteName() !== $canonical_route_name) {
        $url->setRouteParameters([
          'user' => $app->getOwnerId(),
          'app' => $app->getName(),
        ]);

        $variables['additional_tabs'][] = [
          'title' => $link['title'],
          'url' => $url,
        ];
      }
    }
  }
}
