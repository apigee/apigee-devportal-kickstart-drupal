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

use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\requirement\Annotation\Requirement;
use Drupal\requirement\Plugin\RequirementBase;

/**
 * @Requirement(
 *   id = "payment_gateway",
 *   group="apigee_m10n_add_credit",
 *   label = "Payment gateway",
 *   description = "Configure a payment gateway to handle prepaid balance checkouts.",
 *   action_button_label="Create payment gateway",
 *   severity="error"
 * )
 */
class PaymentGateway extends RequirementBase {

  /**
   * {@inheritdoc}
   */
  public function getActionButton(): array {
    // Return a link to the payment gateway page.
    if (($entity_type = $this->entityTypeManager->getDefinition('commerce_payment_gateway')) && ($uri = $entity_type->getLinkTemplate('add-form'))) {
      return Link::fromTextAndUrl($this->getActionButtonLabel(), Url::fromUserInput($uri, [
        'query' => \Drupal::destination()->getAsArray(),
        'attributes' => [
          'class' => [
            'button',
          ],
        ],
      ]))->toRenderable();
    }

    return parent::getActionButton();
  }

  /**
   * {@inheritdoc}
   */
  public function isApplicable(): bool {
    return $this->getModuleHandler()->moduleExists('apigee_m10n_add_credit');
  }

  /**
   * {@inheritdoc}
   */
  public function isCompleted(): bool {
    return count($this->getEntityTypeManager()->getStorage('commerce_payment_gateway')->loadMultiple());
  }

}
