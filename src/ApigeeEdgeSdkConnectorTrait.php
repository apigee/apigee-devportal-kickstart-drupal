<?php

namespace Drupal\apigee_devportal_kickstart;

/**
 * Provides a trait with utility method to fetch Apigee's Edge sdk connector.
 */
trait ApigeeEdgeSdkConnectorTrait {

  /**
   * The Apigee Edge SDK connector.
   *
   * @var \Drupal\apigee_edge\SDKConnectorInterface
   */
  protected $apigeeEdgeSdkConnector;

  /**
   * Gets the Apigee Edge SDK connector.
   *
   * @return \Drupal\apigee_edge\SDKConnectorInterface|mixed
   *   The Apigee Edge SDK connector.
   */
  public function getApigeeEdgeSdkConnector() {
    if (!$this->apigeeEdgeSdkConnector) {
      $this->apigeeEdgeSdkConnector = \Drupal::service('apigee_edge.sdk_connector');
    }

    return $this->apigeeEdgeSdkConnector;
  }

}
