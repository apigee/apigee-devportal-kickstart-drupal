<?php

/**
 * @file
 * Theme and preprocess functions for fields.
 */

/**
 * Implements template_preprocess_field().
 */
function apigee_kickstart_preprocess_field(&$variables) {
  if ($variables['field_type'] === 'apigee_purchase') {
    /** @var \Drupal\apigee_m10n\Entity\RatePlanInterface $rate_plan */
    if ($rate_plan = $variables['element']['#object']) {
      // Add a url to the rate plan to the template.
      $variables['rate_plan_url'] = $rate_plan->toUrl();
    }
  }
}
