<?php

/*
 * Copyright 2019 Google Inc.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 2 as published by the
 * Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 * or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public
 * License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc., 51
 * Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 */

namespace Drupal\apigee_devportal_kickstart\Plugin\Requirements\Requirements;

use Drupal\apigee_devportal_kickstart\ApigeeEdgeSdkConnectorTrait;
use Drupal\requirements\Plugin\RequirementsBase;

/**
 * @Requirements(
 *   id="apigee_edge_connection",
 *   group="apigee_m10n_add_credit",
 *   label="Apigee Edge connection",
 *   description="A working connection to Apigee Edge is required.",
 *   form="\Drupal\apigee_edge\Form\AuthenticationForm",
 *   action_button_label="Configure credentials",
 *   severity="error",
 *   weight=-100
 * )
 */
class ApigeeEdgeConnection extends RequirementsBase {

  use ApigeeEdgeSdkConnectorTrait;

  /**
   * {@inheritdoc}
   */
  public function isApplicable(): bool {
    // This is always applicable. A connection to Apigee Edge is required.
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function isCompleted(): bool {
    try {
      $this->getApigeeEdgeSdkConnector()->testConnection();
      return TRUE;
    } catch (\Exception $exception) {
      watchdog_exception('requirements', $exception);
    }

    return FALSE;
  }

}
