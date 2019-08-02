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

namespace Drupal\apigee_devportal_kickstart\Plugin\Requirement\Requirement;

use Drupal\apigee_edge\Job\Job;
use Drupal\requirement\Annotation\Requirement;
use Drupal\requirement\Plugin\RequirementBase;

/**
 * @Requirement(
 *   id="apigee_edge_developer_sync",
 *   group="apigee_edge",
 *   label="Developer Sync",
 *   description="Sync Drupal users with Apigee Edge developers.",
 *   severity="error",
 *   form="\Drupal\apigee_edge\Form\DeveloperSyncForm",
 *   action_button_label="Sync developers",
 *   dependencies={
 *      "apigee_edge_connection",
 *   }
 * )
 */
class ApigeeEdgeDeveloperSync extends RequirementBase {

  /**
   * {@inheritdoc}
   */
  public function isApplicable(): bool {
    return $this->getModuleHandler()->moduleExists('apigee_edge');
  }

  /**
   * {@inheritdoc}
   */
  public function isCompleted(): bool {
    // This requirement is met if there's a developer sync job running or
    // completed.
    // Find all developer sync jobs that are not FAILED.
    $jobs = \Drupal::database()->select('apigee_edge_job', 'j')
      ->fields('j', ['id', 'status'])
      ->condition('tag', 'developer_sync%', 'LIKE')
      ->condition('status', Job::FAILED, '!=')
      ->countQuery()
      ->execute()
      ->fetchField();

    return (int)($jobs) > 0;
  }

}
